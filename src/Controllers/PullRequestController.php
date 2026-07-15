<?php

// Controlador para manejar todo lo relacionado con los Pull Requests, incluyendo la creación de nuevos PRs, visualización de detalles de PRs,
// gestión de comentarios en PRs, y acciones como mergear o cerrar PRs, utilizando los modelos PullRequest y PrComment para interactuar con la base de datos
// y el servicio GitService para realizar operaciones relacionadas con las ramas y merges en los repositorios
namespace OfficeHub\Controllers;

use OfficeHub\Core\Controller;
use OfficeHub\Core\Session;
use OfficeHub\Core\Response;
use OfficeHub\Models\Repository;
use OfficeHub\Models\PullRequest;
use OfficeHub\Models\PrComment;
use OfficeHub\Models\ActivityLog;
use OfficeHub\Models\RepoPermission;
use OfficeHub\Services\GitService;

class PullRequestController extends Controller
{
    // GET /repos/{name}/pulls
    public function index(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo   = $this->getRepoOr404($params['name']);
        $user   = $this->currentUser();
        $status = $this->request->query('status', 'open');

        $this->assertCanRead($repo);

        $this->view('pull_requests/index', [
            'title'       => "Pull Requests — {$repo['name']}",
            'repo'        => $repo,
            'status'      => $status,
            'prs'         => PullRequest::forRepo($repo['id'], $status),
            'canCreatePr' => RepoPermission::canWrite($repo['id'], $user['id'], $user['role']),
        ]);
    }

    // GET /repos/{name}/pulls/create
    public function create(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name']);
        $git  = GitService::fromRepo($repo);

        $this->assertCanWrite($repo);

        $this->view('pull_requests/create', [
            'title'    => "Nuevo Pull Request — {$repo['name']}",
            'repo'     => $repo,
            'branches' => $git->branches(),
            'error'    => Session::getFlash('error'),
        ]);
    }

    // POST /repos/{name}/pulls
    public function store(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name']);
        $user = $this->currentUser();

        $this->assertCanWrite($repo);

        $source = $this->request->input('source_branch', '');
        $target = $this->request->input('target_branch', '');
        $title  = trim($this->request->input('title', ''));
        $desc   = $this->request->input('description', '');

        if (empty($title) || empty($source) || empty($target)) {
            Session::flash('error', 'Completá todos los campos obligatorios.');
            $this->redirect("/repos/{$repo['name']}/pulls/create");
        }

        if ($source === $target) {
            Session::flash('error', 'La rama origen y destino no pueden ser la misma.');
            $this->redirect("/repos/{$repo['name']}/pulls/create");
        }

        $prId = PullRequest::create([
            'repo_id'       => $repo['id'],
            'title'         => $title,
            'description'   => $desc,
            'author_id'     => $user['id'],
            'source_branch' => $source,
            'target_branch' => $target,
        ]);

        ActivityLog::log($user['id'], 'open_pr', $repo['id'], [
            'pr_id' => $prId,
            'title' => $title,
        ]);

        $this->redirect("/repos/{$repo['name']}/pulls/{$prId}");
    }

    // GET /repos/{name}/pulls/{id}
    public function show(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name']);
        $user = $this->currentUser();
        $pr   = PullRequest::findById((int)$params['id']);

        $this->assertCanRead($repo);

        if (!$pr || $pr['repo_id'] !== $repo['id']) {
            http_response_code(404);
            require BASE_PATH . '/src/Views/errors/404.php';
            exit;
        }

        $git     = GitService::fromRepo($repo);
        $rawDiff = $git->diff($pr['target_branch'], $pr['source_branch']);

        $this->view('pull_requests/show', [
            'title'        => "PR #{$pr['id']}: {$pr['title']}",
            'repo'         => $repo,
            'pr'           => $pr,
            'diff'         => $git->parseDiff($rawDiff),
            'comments'     => PrComment::generalForPr($pr['id']),
            'inline'       => PrComment::inlineForPr($pr['id']),
            'canManagePr'  => RepoPermission::canAdmin($repo['id'], $user['id'], $user['role']),
            'canCommentPr' => RepoPermission::canWrite($repo['id'], $user['id'], $user['role']),
        ]);
    }

    // POST /repos/{name}/pulls/{id}/comment
    public function comment(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name']);
        $user = $this->currentUser();
        $pr   = PullRequest::findById((int)$params['id']);

        $this->assertCanWrite($repo);

        if (!$pr || $pr['repo_id'] !== $repo['id']) {
            http_response_code(404);
            require BASE_PATH . '/src/Views/errors/404.php';
            exit;
        }

        $body     = trim($this->request->input('body', ''));
        $filePath = $this->request->input('file_path') ?: null;
        $lineNum  = $this->request->input('line_number') ?: null;

        if (!empty($body)) {
            PrComment::create([
                'pr_id'       => $pr['id'],
                'author_id'   => $user['id'],
                'body'        => $body,
                'file_path'   => $filePath,
                'line_number' => $lineNum ? (int)$lineNum : null,
            ]);
        }

        $this->redirect("/repos/{$repo['name']}/pulls/{$pr['id']}");
    }

    // POST /repos/{name}/pulls/{id}/merge
    public function merge(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name']);
        $user = $this->currentUser();
        $pr   = PullRequest::findById((int)$params['id']);

        $this->assertCanAdmin($repo);

        if (!$pr || $pr['repo_id'] !== $repo['id']) {
            http_response_code(404);
            require BASE_PATH . '/src/Views/errors/404.php';
            exit;
        }

        if ($pr['status'] !== 'open') {
            Session::flash('error', 'Este Pull Request ya no está abierto.');
            $this->redirect("/repos/{$repo['name']}/pulls/{$pr['id']}");
        }

        $git    = GitService::fromRepo($repo);
        $result = $git->merge(
            $pr['target_branch'],
            $pr['source_branch'],
            "Merge PR #{$pr['id']}: {$pr['title']}"
        );

        if ($result['success']) {
            PullRequest::setStatus($pr['id'], 'merged');
            ActivityLog::log($user['id'], 'merge_pr', $repo['id'], ['pr_id' => $pr['id']]);
            Session::flash('success', 'Pull Request mergeado correctamente.');
        } else {
            Session::flash('error', 'El merge falló. Puede haber conflictos que resolver manualmente.');
        }

        $this->redirect("/repos/{$repo['name']}/pulls/{$pr['id']}");
    }

    // POST /repos/{name}/pulls/{id}/close
    public function close(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name']);
        $user = $this->currentUser();
        $pr   = PullRequest::findById((int)$params['id']);

        $this->assertCanAdmin($repo);

        if (!$pr || $pr['repo_id'] !== $repo['id']) {
            http_response_code(404);
            require BASE_PATH . '/src/Views/errors/404.php';
            exit;
        }

        PullRequest::setStatus($pr['id'], 'closed');
        ActivityLog::log($user['id'], 'close_pr', $repo['id'], ['pr_id' => $pr['id']]);

        Session::flash('success', 'Pull Request cerrado.');
        $this->redirect("/repos/{$repo['name']}/pulls/{$pr['id']}");
    }

    // -------------------------------------------------------
    private function getRepoOr404(string $name): array
    {
        $repo = Repository::findByName($name);
        $user = $this->currentUser();

        if (
            !$repo
            || !$user
            || !RepoPermission::canRead(
                (int)$repo['id'],
                (int)$user['id'],
                (string)$user['role'],
                (string)$repo['visibility']
            )
        ) {
            http_response_code(404);
            require BASE_PATH . '/src/Views/errors/404.php';
            exit;
        }
        return $repo;
    }

    private function assertCanRead(array $repo): void
    {
        $user = $this->currentUser();

        if (!RepoPermission::canRead($repo['id'], $user['id'], $user['role'], $repo['visibility'])) {
            Response::abort(403, 'No tenés permisos para ver este repositorio.');
        }
    }

    private function assertCanWrite(array $repo): void
    {
        $user = $this->currentUser();

        if (!RepoPermission::canWrite($repo['id'], $user['id'], $user['role'])) {
            Response::abort(403, 'No tenés permisos para crear o comentar Pull Requests en este repositorio.');
        }
    }

    private function assertCanAdmin(array $repo): void
    {
        $user = $this->currentUser();

        if (!RepoPermission::canAdmin($repo['id'], $user['id'], $user['role'])) {
            Response::abort(403, 'No tenés permisos para administrar Pull Requests en este repositorio.');
        }
    }
}

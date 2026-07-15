<?php

// Controlador para manejar la navegación por el contenido de los repositorios, incluyendo la visualización de árboles de archivos, blobs,
// historial de commits y detalles de commits específicos, utilizando el servicio GitService para interactuar con los repositorios y
// obtener la información necesaria para las vistas correspondientes
namespace OfficeHub\Controllers;

use OfficeHub\Core\Controller;
use OfficeHub\Models\Repository;
use OfficeHub\Models\RepoPermission;
use OfficeHub\Services\GitService;

class BrowseController extends Controller
{
    // GET /repos/{name}/tree
    public function tree(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo   = $this->getRepoOr404($params['name']);
        $git    = GitService::fromRepo($repo);

        // Soporta tanto /tree/{branch} como /tree?branch=...
        $branch = $params['branch'] ?? $this->request->query('branch', $repo['default_branch']);
        $path   = $params['path']   ?? $this->request->query('path', '');

        $this->view('browse/tree', [
            'title'    => "{$repo['name']} / {$branch}",
            'repo'     => $repo,
            'branch'   => $branch,
            'branches' => $git->branches(),
            'path'     => $path,
            'tree'     => $git->listTree($branch, $path),
        ]);
    }

    // GET /repos/{name}/blob
    public function blob(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo     = $this->getRepoOr404($params['name']);
        $git      = GitService::fromRepo($repo);

        // Soporta tanto /blob/{branch}/{path} como /blob?branch=...&path=...
        $branch   = $params['branch'] ?? $this->request->query('branch', $repo['default_branch']);
        $filePath = $params['path']   ?? $this->request->query('path', '');

        $content = $git->fileContent($branch, $filePath);

        if (empty(trim($content))) {
            $content = $git->fileContent('HEAD', $filePath);
        }

        $this->view('browse/blob', [
            'title'    => basename($filePath) . " — {$repo['name']}",
            'repo'     => $repo,
            'branch'   => $branch,
            'filePath' => $filePath,
            'content'  => $content,
        ]);
    }

    // GET /repos/{name}/commits/{branch}
    public function commits(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name']);
        $git  = GitService::fromRepo($repo);

        $branch = $params['branch'] ?? $this->request->query('branch', 'main');

        $this->view('browse/commits', [
            'title'   => "Commits — {$repo['name']}",
            'repo'    => $repo,
            'branch'  => $branch,
            'commits' => $git->log($branch, 50),
        ]);
    }

    // GET /repos/{name}/commit/{hash}
    public function commit(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo   = $this->getRepoOr404($params['name']);
        $git    = GitService::fromRepo($repo);
        $commit = $git->show($params['hash']);

        $this->view('browse/commit', [
            'title'  => shortHash($params['hash']) . " — {$repo['name']}",
            'repo'   => $repo,
            'commit' => $commit,
            'parsed' => $git->parseDiff($commit['diff']),
        ]);
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
}

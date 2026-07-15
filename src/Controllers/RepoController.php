<?php

namespace OfficeHub\Controllers;

use OfficeHub\Core\Controller;
use OfficeHub\Core\Response;
use OfficeHub\Core\Session;
use OfficeHub\Models\Repository;
use OfficeHub\Models\ActivityLog;
use OfficeHub\Models\RepoPermission;
use OfficeHub\Models\User;
use OfficeHub\Services\GitService;


class RepoController extends Controller
{
    // GET /
    public function dashboard(array $params = []): void
    {
        $this->requireRepoAccess();
        $user     = $this->currentUser();
        $areaId   = \OfficeHub\Core\Session::get('active_area_id');
        $repos    = Repository::visibleFor($user['id'], $user['role'], $areaId);
        $activity = ActivityLog::recent(15, $areaId, (int)$user['id']);

        usort($repos, static function (array $a, array $b): int {
            $createdA = (string)($a['created_at'] ?? '');
            $createdB = (string)($b['created_at'] ?? '');

            if ($createdA !== '' && $createdB !== '' && $createdA !== $createdB) {
                return strcmp($createdA, $createdB);
            }

            return (int)($a['id'] ?? 0) <=> (int)($b['id'] ?? 0);
        });

        $this->view('repos/dashboard', [
            'title'    => 'Dashboard — OfficeHub',
            'repos'    => $repos,
            'activity' => $activity,
        ]);
    }

    // GET /repos
    public function index(array $params = []): void
    {
        $this->requireRepoAccess();
        $user   = $this->currentUser();
        $areaId = \OfficeHub\Core\Session::get('active_area_id');
        $repos  = Repository::visibleFor($user['id'], $user['role'], $areaId);

        $this->view('repos/index', [
            'title' => 'Repositorios',
            'repos' => $repos,
        ]);
    }

    // GET /repos/create
    public function create(array $params = []): void
    {
        $this->requireRepoAccess();
        $user = $this->currentUser();

 if ($user['role'] === 'viewer') {
    Session::flash('error', 'No tenés permisos para crear repositorios.');
    $this->redirect('/repos');
}


        $this->view('repos/create', [
            'title' => 'Nuevo repositorio',
            'error' => Session::getFlash('error'),
        ]);
    }

    // POST /repos
    public function store(array $params = []): void
    {
        $this->requireRepoAccess();
        $user = $this->currentUser();

        $name        = preg_replace('/[^a-zA-Z0-9\-_]/', '', $this->request->input('name', ''));
        $description = $this->request->input('description', '');
        $visibility  = $this->request->input('visibility', 'internal');
        $areaId      = \OfficeHub\Core\Session::get('active_area_id');

        if (empty($name)) {
            Session::flash('error', 'El nombre es obligatorio y solo puede contener letras, números, guiones y guiones bajos.');
            $this->redirect('/repos/create');
        }

        if (Repository::findByName($name)) {
            Session::flash('error', "Ya existe un repositorio con el nombre \"{$name}\".");
            $this->redirect('/repos/create');
        }

        $config  = require BASE_PATH . '/config/app.php';
        $repoDir = rtrim($config['repos_path'], '/\\') . DIRECTORY_SEPARATOR . $name . '.git';

        if (!GitService::initBare($repoDir)) {
            Session::flash('error', 'No se pudo inicializar el repositorio.');
            $this->redirect('/repos/create');
        }

        $repoId = Repository::create([
            'name'         => $name,
            'description'  => $description,
            'owner_id'     => $user['id'],
            'visibility'   => $visibility,
            'path_on_disk' => $repoDir,
            'area_id'      => $areaId,
        ]);

        ActivityLog::log($user['id'], 'create_repo', $repoId, ['name' => $name]);
        Session::flash('success', "Repositorio \"{$name}\" creado correctamente.");
        $this->redirect("/repos/{$name}");
    }

    // POST /repos/{name}/website
    public function updateWebsite(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name']);
        $this->assertCanAdmin($repo);

        $url = trim($this->request->input('website_url', ''));

        if ($url && !filter_var($url, FILTER_VALIDATE_URL)) {
            Session::flash('error', 'La URL ingresada no es válida. Asegurate de incluir http:// o https://');
            $this->redirect("/repos/{$repo['name']}/settings");
        }

        Repository::updateWebsiteUrl($repo['id'], $url ?: null);
        Session::flash('success', $url ? 'URL de producción actualizada.' : 'URL de producción eliminada.');
        $this->redirect("/repos/{$repo['name']}/settings");
    }

    public function updateDescription(array $params): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name'] ?? '');
        $this->assertCanAdmin($repo);

        $description = trim($this->request->input('description', ''));
        Repository::updateDescription($repo['name'], $description);

        Session::flash('success', 'Descripcion actualizada.');
        $this->redirect('/repos/' . $repo['name'] . '/settings');
    }

    // GET /repos/{name}
    public function show(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo    = $this->getRepoOr404($params['name']);
        $user    = $this->currentUser();
        $git     = GitService::fromRepo($repo);
        $branch  = $this->request->query('branch', $repo['default_branch']);
        $isEmpty = $git->isEmpty();

        $this->view('repos/show', [
            'title'    => $repo['name'],
            'repo'     => $repo,
            'branch'   => $branch,
            'branches' => $isEmpty ? [] : $git->branches(),
            'tree'     => $isEmpty ? [] : $git->listTree($branch),
            'commits'  => $isEmpty ? [] : $git->log($branch, 5),
            'isEmpty'  => $isEmpty,
            'canAdmin'  => RepoPermission::canAdmin(
                (int)$repo['id'],
                (int)$user['id'],
                (string)$user['role']
            ),
        ]);
    }

    // GET /repos/{name}/settings
    public function settings(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name']);
        $this->assertCanAdmin($repo);

        $this->view('repos/settings', [
            'title' => "Configuración — {$repo['name']}",
            'repo'  => $repo,
            'collaborators' => RepoPermission::forRepo((int)$repo['id']),
        ]);
    }

    // POST /repos/{name}/delete
    public function delete(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name']);
        $user = $this->currentUser();

        $canAdmin = RepoPermission::canAdmin(
            (int)$repo['id'],
            (int)$user['id'],
            (string)$user['role']
        );
        $isInternalDeveloperOtherOwner =
            $repo['visibility'] === 'internal'
            && $user['role'] === 'developer'
            && (int)$repo['owner_id'] !== (int)$user['id'];

        if (!$canAdmin || $isInternalDeveloperOtherOwner) {
            $this->redirect("/repos/{$repo['name']}");
        }

        GitService::deleteRepo($repo['path_on_disk']);
        Repository::delete($repo['id']);
        ActivityLog::log($user['id'], 'delete_repo', null, ['name' => $repo['name']]);

        Session::flash('success', "Repositorio \"{$repo['name']}\" eliminado.");
        $this->redirect('/repos');
    }

    // POST /area/switch
    public function switchArea(array $params = []): void
    {
        $this->requireRepoAccess();
        $areaId = $this->request->input('area_id');

        if ($areaId === 'all') {
            \OfficeHub\Core\Session::set('active_area_id', null);
        } else {
            \OfficeHub\Core\Session::set('active_area_id', (int)$areaId);
        }

        $this->redirect('/');
    }

    // POST /repos/{name}/move-area
    public function moveArea(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name']);
        $this->assertCanAdmin($repo);
        $user = $this->currentUser();

        if (($user['role'] ?? '') !== 'admin') {
            Session::flash('error', 'No tenés permisos para mover repositorios entre áreas.');
            $this->redirect("/repos/{$repo['name']}/settings");
        }

        $areaId = $this->request->input('area_id');
        Repository::updateArea($repo['id'], $areaId ? (int)$areaId : null);
        Session::flash('success', 'Repositorio movido correctamente.');
        $this->redirect("/repos/{$repo['name']}/settings");
    }

    // POST /repos/{name}/permissions/add
    public function addPermission(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name']);
        $this->assertCanAdmin($repo);

        $username = trim($this->request->input('username', ''));
        $permission = $this->request->input('permission', 'read');

        if (!in_array($permission, ['read', 'write', 'admin'], true)) {
            Session::flash('error', 'El permiso seleccionado no es valido.');
            $this->redirect("/repos/{$repo['name']}/settings");
        }

        $targetUser = User::findByUsername($username);

        if (!$targetUser) {
            Session::flash('error', 'No se encontro un usuario activo con ese nombre.');
            $this->redirect("/repos/{$repo['name']}/settings");
        }

        if ((int)$targetUser['id'] === (int)$repo['owner_id']) {
            Session::flash('error', 'El propietario ya tiene acceso total al repositorio.');
            $this->redirect("/repos/{$repo['name']}/settings");
        }

        RepoPermission::set((int)$repo['id'], (int)$targetUser['id'], $permission);
        Session::flash('success', "Permiso {$permission} asignado a {$targetUser['username']}.");
        $this->redirect("/repos/{$repo['name']}/settings");
    }

    // POST /repos/{name}/permissions/remove
    public function removePermission(array $params = []): void
    {
        $this->requireRepoAccess();
        $repo = $this->getRepoOr404($params['name']);
        $this->assertCanAdmin($repo);

        $userId = (int)$this->request->input('user_id', 0);

        if ($userId > 0 && $userId !== (int)$repo['owner_id']) {
            RepoPermission::remove((int)$repo['id'], $userId);
            Session::flash('success', 'Colaborador eliminado del repositorio.');
        }

        $this->redirect("/repos/{$repo['name']}/settings");
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

    private function assertCanAdmin(array $repo): void
    {
        $user = $this->currentUser();

        if (
            !$user
            || !RepoPermission::canAdmin(
                (int)$repo['id'],
                (int)$user['id'],
                (string)$user['role']
            )
        ) {
            Response::abort(403, 'No tenes permisos para administrar este repositorio.');
        }
    }
}

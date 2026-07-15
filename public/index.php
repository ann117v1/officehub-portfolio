<?php
// Punto de entrada de la aplicación

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__));
define('APP_BASE', '');

// Autoloader PSR-4
spl_autoload_register(function (string $class): void {
    $prefix = 'OfficeHub\\';
    $base   = BASE_PATH . '/src/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file     = $base . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Helpers globales
require BASE_PATH . '/src/helpers.php';

// Configuración general
$appConfig = require BASE_PATH . '/config/app.php';
date_default_timezone_set($appConfig['timezone']);

// Iniciar sesión
use OfficeHub\Core\Session;

Session::start();

// Rutas
use OfficeHub\Core\Router;
use OfficeHub\Core\Request;
use OfficeHub\Core\Response;
use OfficeHub\Controllers\AuthController;
use OfficeHub\Controllers\RepoController;
use OfficeHub\Controllers\BrowseController;
use OfficeHub\Controllers\PullRequestController;
use OfficeHub\Controllers\AdminController;
use OfficeHub\Controllers\SupportController;
use OfficeHub\Controllers\BoardController;
use OfficeHub\Controllers\NotificationController;

$router = new Router();

// Auth
$router->get('/login',  [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/olvide-mi-contrasena', [AuthController::class, 'showForgotPassword']);
$router->post('/olvide-mi-contrasena', [AuthController::class, 'requestPasswordReset']);
$router->get('/restablecer-contrasena', [AuthController::class, 'showResetPassword']);
$router->post('/restablecer-contrasena', [AuthController::class, 'resetPassword']);
$router->get('/logout', [AuthController::class, 'logout']);

// Dashboard
$router->get('/', [RepoController::class, 'dashboard']);

// Soporte
$router->get('/soporte',                           [SupportController::class, 'index']);
$router->get('/soporte/categoria/{slug}',          [SupportController::class, 'category']);
$router->get('/soporte/articulo/{slug}',           [SupportController::class, 'show']);
$router->get('/soporte/nuevo',                     [SupportController::class, 'create']);
$router->post('/soporte',                          [SupportController::class, 'store']);
$router->get('/soporte/articulo/{slug}/editar',    [SupportController::class, 'edit']);
$router->post('/soporte/articulo/{slug}/editar',   [SupportController::class, 'update']);
$router->post('/soporte/articulo/{slug}/eliminar', [SupportController::class, 'delete']);
$router->get('/soporte/adjunto/{id}',              [SupportController::class, 'download']);

// Tablero local
$router->get('/tablero', [BoardController::class, 'index']);
$router->get('/trello', [BoardController::class, 'index']);
$router->get('/tablero/historial', [BoardController::class, 'history']);
$router->get('/tablero/version', [BoardController::class, 'version']);
$router->post('/tablero/lists', [BoardController::class, 'storeList']);
$router->post('/tablero/cards', [BoardController::class, 'storeCard']);
$router->post('/tablero/cards/{id}/update', [BoardController::class, 'updateCard']);
$router->post('/tablero/cards/move', [BoardController::class, 'moveCard']);
$router->post('/tablero/cards/complete', [BoardController::class, 'completeCard']);
$router->post('/tablero/cards/progress', [BoardController::class, 'progressCard']);
$router->get('/tablero/cards/{id}/trace', [BoardController::class, 'traceCard']);
$router->post('/tablero/cards/{id}/comments', [BoardController::class, 'addComment']);
$router->post('/tablero/cards/{id}/attachments', [BoardController::class, 'addAttachment']);
$router->get('/tablero/cards/{id}/attachments/{attachmentId}', [BoardController::class, 'downloadAttachment']);
$router->post('/tablero/cards/{id}/attachments/{attachmentId}/delete', [BoardController::class, 'deleteAttachment']);
$router->post('/tablero/cards/{id}/archive', [BoardController::class, 'archiveCard']);
$router->post('/tablero/cards/{id}/delete', [BoardController::class, 'deleteCard']);

// Notificaciones
$router->get('/notificaciones', [NotificationController::class, 'poll']);
$router->post('/notificaciones/{id}/leer', [NotificationController::class, 'markRead']);
$router->post('/notificaciones/leer-todas', [NotificationController::class, 'markAllRead']);

// Repositorios
$router->get('/repos',                 [RepoController::class, 'index']);
$router->get('/repos/create',          [RepoController::class, 'create']);
$router->post('/repos',                [RepoController::class, 'store']);
$router->get('/repos/{name}',          [RepoController::class, 'show']);
$router->get('/repos/{name}/settings', [RepoController::class, 'settings']);
$router->post('/repos/{name}/delete',  [RepoController::class, 'delete']);
$router->post('/repos/{name}/website',      [RepoController::class, 'updateWebsite']);
$router->post('/repos/{name}/update-description', [RepoController::class, 'updateDescription']);
$router->post('/repos/{name}/permissions/add', [RepoController::class, 'addPermission']);
$router->post('/repos/{name}/permissions/remove', [RepoController::class, 'removePermission']);

// Browsing — con segmentos de URL
$router->get('/repos/{name}/tree/{branch}',        [BrowseController::class, 'tree']);
$router->get('/repos/{name}/blob/{branch}/{path}', [BrowseController::class, 'blob']);
$router->get('/repos/{name}/commits/{branch}',     [BrowseController::class, 'commits']);
$router->get('/repos/{name}/commit/{hash}',        [BrowseController::class, 'commit']);

// Browsing — con query string (para compatibilidad con las vistas)
$router->get('/repos/{name}/tree',                 [BrowseController::class, 'tree']);
$router->get('/repos/{name}/blob',                 [BrowseController::class, 'blob']);
$router->get('/repos/{name}/commits',              [BrowseController::class, 'commits']);

// Pull Requests
$router->get('/repos/{name}/pulls',                [PullRequestController::class, 'index']);
$router->get('/repos/{name}/pulls/create',         [PullRequestController::class, 'create']);
$router->post('/repos/{name}/pulls',               [PullRequestController::class, 'store']);
$router->get('/repos/{name}/pulls/{id}',           [PullRequestController::class, 'show']);
$router->post('/repos/{name}/pulls/{id}/merge',    [PullRequestController::class, 'merge']);
$router->post('/repos/{name}/pulls/{id}/close',    [PullRequestController::class, 'close']);
$router->post('/repos/{name}/pulls/{id}/comment',  [PullRequestController::class, 'comment']);

// Browsing
$router->get('/repos/{name}/tree',               [BrowseController::class, 'tree']);
$router->get('/repos/{name}/blob',               [BrowseController::class, 'blob']);
$router->get('/repos/{name}/commits',            [BrowseController::class, 'commits']);

$router->get('/repos/{name}/tree/{branch}',      [BrowseController::class, 'tree']);
$router->get('/repos/{name}/blob/{branch}/{path}', [BrowseController::class, 'blob']);
$router->get('/repos/{name}/commits/{branch}',   [BrowseController::class, 'commits']);
$router->get('/repos/{name}/commit/{hash}',      [BrowseController::class, 'commit']);

// Admin
$router->get('/admin/users',               [AdminController::class, 'users']);
$router->post('/admin/users',              [AdminController::class, 'createUser']);
$router->post('/admin/users/{id}/toggle',  [AdminController::class, 'toggleUser']);
$router->post('/admin/users/{id}/access',  [AdminController::class, 'changeAccess']);
$router->post('/admin/users/{id}/delete',  [AdminController::class, 'deleteUser']);

$router->post('/area/switch',               [RepoController::class, 'switchArea']);
$router->post('/repos/{name}/move-area',    [RepoController::class, 'moveArea']);

$router->get('/admin/areas',              [AdminController::class, 'areas']);
$router->post('/admin/areas',             [AdminController::class, 'storeArea']);
$router->post('/admin/areas/{id}/update', [AdminController::class, 'updateArea']);
$router->post('/admin/areas/{id}/delete', [AdminController::class, 'deleteArea']);

$router->post('/admin/users/{id}/role', [AdminController::class, 'changeRole']);

$router->get('/admin/support',                         [AdminController::class, 'support']);
$router->post('/admin/support/categories',             [AdminController::class, 'storeSupportCategory']);
$router->post('/admin/support/categories/{id}/update', [AdminController::class, 'updateSupportCategory']);
$router->post('/admin/support/categories/{id}/delete', [AdminController::class, 'deleteSupportCategory']);

// Despachar
$request = new Request();

if ($request->isPost()) {
    $csrfToken = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');

    if (!Session::verifyCsrfToken(is_string($csrfToken) ? $csrfToken : null)) {
        $acceptsJson = str_contains((string)($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json');
        $isAjax = strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest';

        if ($acceptsJson || $isAjax) {
            Response::json([
                'ok' => false,
                'message' => 'La sesion expiro o el formulario no es valido. Recarga la pagina e intenta de nuevo.',
            ], 403);
        }

        $redirectTo = '/';
        $referer = (string)($_SERVER['HTTP_REFERER'] ?? '');

        if ($referer !== '') {
            $refererHost = parse_url($referer, PHP_URL_HOST);
            $currentHost = (string)($_SERVER['HTTP_HOST'] ?? '');

            if ($refererHost === null || strcasecmp($refererHost, $currentHost) === 0) {
                $path = parse_url($referer, PHP_URL_PATH) ?: '/';
                $query = parse_url($referer, PHP_URL_QUERY);
                $redirectTo = $path . ($query ? '?' . $query : '');
            }
        }

        Session::flash('error', 'La sesion expiro o el formulario no es valido. Recarga la pagina e intenta de nuevo.');
        Response::redirect($redirectTo);
    }
}

$router->dispatch($request);

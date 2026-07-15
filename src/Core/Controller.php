<?php

namespace OfficeHub\Core;

use OfficeHub\Models\User;

abstract class Controller
{
    protected Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    protected function view(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    protected function redirect(string $url): never
    {
        Response::redirect($url);
    }

    protected function json(mixed $data, int $status = 200): never
    {
        Response::json($data, $status);
    }

    protected function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            Session::flash('error', 'Debes iniciar sesion para acceder a esta pagina.');
            Response::redirect('/login');
        }

        $sessionUser = Session::user();

        if (!$sessionUser || empty($sessionUser['id'])) {
            Session::logout();
            Session::flash('error', 'Debes iniciar sesion para continuar.');
            Response::redirect('/login');
        }

        $freshUser = User::findById((int)$sessionUser['id']);

        if (!$freshUser || !(int)($freshUser['is_active'] ?? 0)) {
            Session::logout();
            Session::flash('error', 'Tu cuenta esta inactiva o ya no existe.');
            Response::redirect('/login');
        }

        if (
            array_key_exists('session_version', $freshUser)
            && (
                !array_key_exists('session_version', $sessionUser)
                || (int)$freshUser['session_version'] !== (int)$sessionUser['session_version']
            )
        ) {
            Session::logout();
            Session::flash('error', 'Tu sesion vencio por seguridad. Inicia sesion nuevamente.');
            Response::redirect('/login');
        }

        unset($freshUser['password_hash']);
        Session::setUser($freshUser);
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();

        $user = Session::user();

        if (($user['role'] ?? '') !== 'admin') {
            Response::abort(403, 'Acceso denegado.');
        }
    }

    protected function requireRepoAccess(): void
    {
        $this->requireAuth();

        $user = Session::user();

        if (($user['role'] ?? '') === 'admin' || (int)($user['repo_access'] ?? 1) === 1) {
            return;
        }

        if (in_array(($user['support_role'] ?? 'none'), ['support_admin', 'support_viewer'], true)) {
            Response::redirect('/soporte');
        }

        Response::abort(403, 'No tenes permisos para acceder a repositorios.');
    }

    protected function requireSupportAccess(): void
    {
        $this->requireAuth();

        $user = Session::user();

        if (($user['role'] ?? '') === 'admin' || in_array(($user['support_role'] ?? 'none'), ['support_admin', 'support_viewer'], true)) {
            return;
        }

        Response::abort(403, 'No tenes permisos para acceder al portal de soporte.');
    }

    protected function requireSupportAdmin(): void
    {
        $this->requireSupportAccess();

        $user = Session::user();

        if (($user['role'] ?? '') !== 'admin' && ($user['support_role'] ?? 'none') !== 'support_admin') {
            Response::abort(403, 'No tenes permisos para administrar soporte.');
        }
    }

    protected function currentUser(): ?array
    {
        return Session::user();
    }
}

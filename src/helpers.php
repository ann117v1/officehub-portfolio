<?php

// Son funciones globales de utilidad que se pueden usar en cualquier parte del proyecto, especialmente dentro de las vistas. 
// Son atajos para cosas que se repiten mucho.
// La más importante es e() — es la función que hay que usar siempre al mostrar datos del usuario en el HTML para evitar ataques XSS.

use OfficeHub\Core\View;
use OfficeHub\Core\Session;

function e(mixed $value): string
{
    return View::escape($value);
}

function url(string $path = ''): string
{
    $config = require BASE_PATH . '/config/app.php';
    return rtrim($config['url'], '/') . '/' . ltrim($path, '/');
}

function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function currentUser(): ?array
{
    return Session::user();
}

function csrf_token(): string
{
    return Session::csrfToken();
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function hasRole(string $role): bool
{
    $user = currentUser();
    return $user && $user['role'] === $role;
}

function canUseRepos(): bool
{
    $user = currentUser();
    return $user && (($user['role'] ?? '') === 'admin' || (int)($user['repo_access'] ?? 1) === 1);
}

function supportRole(): string
{
    $user = currentUser();
    return (string)($user['support_role'] ?? 'none');
}

function canUseSupport(): bool
{
    $user = currentUser();
    return $user && (($user['role'] ?? '') === 'admin' || in_array(supportRole(), ['support_admin', 'support_viewer'], true));
}

function canUseTrello(): bool
{
    return (bool) currentUser();
}

function canUseBoard(): bool
{
    return (bool) currentUser();
}

function canUseNotifications(): bool
{
    $user = currentUser();
    return $user && (int)($user['notifications_enabled'] ?? 1) === 1;
}

function canManageSupport(): bool
{
    $user = currentUser();
    return $user && (($user['role'] ?? '') === 'admin' || supportRole() === 'support_admin');
}

function dateFormat(string $date, string $format = 'd/m/Y H:i'): string
{
    return date($format, strtotime($date));
}

function shortHash(string $hash, int $length = 7): string
{
    return substr($hash, 0, $length);
}

function base(string $path = ''): string
{
    $b = defined('APP_BASE') ? APP_BASE : '';
    return $b . '/' . ltrim($path, '/');
}

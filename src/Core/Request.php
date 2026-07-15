<?php
// representa la petición HTTP que hace el navegador. En vez de usar directamente $_GET, $_POST, $_SERVER (que son variables globales de PHP),
// esta clase los envuelve en métodos más limpios y seguros

namespace OfficeHub\Core;

class Request
{
    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $uri  = strtok($uri, '?');
        $base = defined('APP_BASE') ? APP_BASE : '';

        // Quitar el prefijo del subdirectorio
        if ($base && str_starts_with($uri, $base)) {
            $uri = substr($uri, strlen($base));
        }

        $uri = '/' . trim($uri, '/');
        return $uri === '' ? '/' : $uri;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function all(): array
    {
        return array_map(fn($v) => is_string($v) ? trim($v) : $v, $_POST);
    }

    public function isAjax(): bool
    {
        return ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';
    }

    public function isPost(): bool
    {
        return $this->method() === 'POST';
    }
}

<?php
//se encarga de enviar respuestas HTTP al navegador, como redirecciones, respuestas JSON o páginas de error.
// Tiene métodos estáticos para facilitar su uso desde cualquier parte del código sin necesidad de instanciar la clase.


namespace OfficeHub\Core;

class Response
{
    public static function redirect(string $url): never
    {
        $base = defined('APP_BASE') ? APP_BASE : '';
        if (!str_starts_with($url, 'http')) {
            $url = $base . $url;
        }
        header('Location: ' . $url);
        exit;
    }

    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function abort(int $code, string $message = ''): never
    {
        http_response_code($code);
        echo htmlspecialchars($message);
        exit;
    }
}

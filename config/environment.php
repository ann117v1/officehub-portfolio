<?php

if (!function_exists('officehub_is_local')) {
    function officehub_is_local(): bool
    {
        $env = strtolower((string)getenv('OFFICEHUB_ENV'));

        if (in_array($env, ['local', 'dev', 'development'], true)) {
            return true;
        }

        $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
        $host = explode(':', $host)[0];

        return in_array($host, ['officehub.local', 'localhost', '127.0.0.1'], true);
    }
}

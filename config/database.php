<?php

require_once __DIR__ . '/environment.php';

if (officehub_is_local() && file_exists(__DIR__ . '/database.local.php')) {
    return require __DIR__ . '/database.local.php';
}

return [
    'host' => getenv('DB_HOST') ?: '127.0.0.1',
    'port' => (int)(getenv('DB_PORT') ?: 3306),
    'dbname' => getenv('DB_DATABASE') ?: 'officehub_portfolio',
    'username' => getenv('DB_USERNAME') ?: 'root',
    'password' => getenv('DB_PASSWORD') ?: '',
    'charset' => getenv('DB_CHARSET') ?: 'utf8mb4',
];

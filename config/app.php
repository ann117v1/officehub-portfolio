<?php

require_once __DIR__ . '/environment.php';

if (officehub_is_local() && file_exists(__DIR__ . '/app.local.php')) {
    return require __DIR__ . '/app.local.php';
}

$storageBase = dirname(__DIR__) . '/storage';

return [
    'name' => getenv('APP_NAME') ?: 'OfficeHub Portfolio',
    'repos_path' => getenv('OFFICEHUB_REPOS_PATH') ?: $storageBase . '/repos',
    'support_uploads_path' => getenv('OFFICEHUB_SUPPORT_UPLOADS_PATH') ?: $storageBase . '/support-uploads',
    'support_uploads_url' => '/soporte/adjunto/',
    'kanban_uploads_path' => getenv('OFFICEHUB_KANBAN_UPLOADS_PATH') ?: $storageBase . '/kanban-uploads',
    'office_preview_enabled' => filter_var(getenv('OFFICE_PREVIEW_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    'soffice_path' => getenv('SOFFICE_PATH') ?: '',
    'url' => getenv('APP_URL') ?: 'http://officehub.local',
    'timezone' => getenv('APP_TIMEZONE') ?: 'America/Argentina/Buenos_Aires',
    'debug' => filter_var(getenv('APP_DEBUG') ?: 'true', FILTER_VALIDATE_BOOLEAN),
];

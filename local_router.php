<?php
// local_router.php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Si le fichier existe physiquement (images, css, js, etc.), on le sert directement
if (file_exists(__DIR__ . $uri) && is_file(__DIR__ . $uri)) {
    return false;
}

// Sinon, on redirige tout vers index.php (comme le fait .htaccess)
require_once __DIR__ . '/index.php';

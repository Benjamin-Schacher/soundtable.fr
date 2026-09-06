<?php

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (file_exists(__DIR__ . $uri) && is_file(__DIR__ . $uri)) {
    return false;
}

require_once __DIR__ . '/index.php';

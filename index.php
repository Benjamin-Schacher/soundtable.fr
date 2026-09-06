<?php
require_once 'route.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

route($uri);
?>
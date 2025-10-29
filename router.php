<?php
// Router untuk TVRI-WEB
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Jika root path atau index.php, include index.php
if ($uri === '/' || $uri === '/index.php') {
    include __DIR__ . '/index.php';
    return;
}

// Jika file ada, serve file tersebut
if (file_exists(__DIR__ . $uri)) {
    return false;
}

// Default: include index.php
include __DIR__ . '/index.php';
?>
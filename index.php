<?php

$uri = $_SERVER['REQUEST_URI'];

if (strpos($uri, '/api/') === 0) {
    require_once __DIR__ . '/routes/api.php';
    route();
    return;
}

if (preg_match('/\.(css|js|woff2?|eot|ttf|svg|png|jpg|jpeg|gif)$/', $uri)) {
    $filePath = __DIR__ . '/dist' . $uri;

    if (file_exists($filePath)) {

        echo file_get_contents($filePath);
        exit;
    }
}

$indexFile = __DIR__ . '/dist/index.html';
if (file_exists($indexFile)) {
    echo file_get_contents($indexFile);
} else {
    http_response_code(404);
    echo "404 Not Found";
}

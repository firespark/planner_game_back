<?php

$uri = $_SERVER['REQUEST_URI'];

if (strpos($uri, '/api/') === 0) {
    require_once __DIR__ . '/routes/api.php';
    route();
    return;
}

if (preg_match('/\.(css|js|woff2?|eot|ttf|svg|png|jpg|jpeg|gif)$/', $uri)) {
    $filePath = __DIR__ . '/public' . $uri;

    if (file_exists($filePath)) {
        $fileInfo = pathinfo($filePath);
        $mimeType = '';

        switch (strtolower($fileInfo['extension'])) {
            case 'css':
                $mimeType = 'text/css';
                break;
            case 'js':
                $mimeType = 'application/javascript';
                break;
            case 'woff':
            case 'woff2':
                $mimeType = 'font/woff2';
                break;
            case 'eot':
                $mimeType = 'application/vnd.ms-fontobject';
                break;
            case 'ttf':
                $mimeType = 'font/ttf';
                break;
            case 'svg':
                $mimeType = 'image/svg+xml';
                break;
            case 'png':
                $mimeType = 'image/png';
                break;
            case 'jpg':
            case 'jpeg':
                $mimeType = 'image/jpeg';
                break;
            case 'gif':
                $mimeType = 'image/gif';
                break;
            default:
                $mimeType = 'application/octet-stream';
                break;
        }

        header("Content-Type: $mimeType");
        header("Cache-Control: public, max-age=3600");

        echo file_get_contents($filePath);
        exit;
    }
}

$indexFile = __DIR__ . '/public/index.html';
if (file_exists($indexFile)) {
    echo file_get_contents($indexFile);
} else {
    http_response_code(404);
    echo "404 Not Found";
}
?>

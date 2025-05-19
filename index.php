<?php
require_once __DIR__ . '/helpers/fileHelper.php';

$uri = $_SERVER['REQUEST_URI'];

if (strpos($uri, '/api/') === 0) {
    require_once __DIR__ . '/routes/api.php';
    route();
    return;
}

$cleanUri = parse_url($uri, PHP_URL_PATH);

if (isStaticFile( $cleanUri)) {
    $filePath = __DIR__ . '/public' . $cleanUri;

    if (file_exists($filePath)) {

        header("Content-Type: " . getMimeType($filePath));
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
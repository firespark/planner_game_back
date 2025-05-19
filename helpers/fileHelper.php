<?php
function getMimeType($filePath)
{
    $fileInfo = pathinfo($filePath);
    $ext = strtolower($fileInfo['extension'] ?? '');

    switch ($ext) {
        case 'css':
            return 'text/css';
        case 'js':
            return 'application/javascript';
        case 'woff':
        case 'woff2':
            return 'font/woff2';
        case 'eot':
            return 'application/vnd.ms-fontobject';
        case 'ttf':
            return 'font/ttf';
        case 'svg':
            return 'image/svg+xml';
        case 'png':
            return 'image/png';
        case 'jpg':
        case 'jpeg':
            return 'image/jpeg';
        case 'gif':
            return 'image/gif';
        case 'ico':
            return 'image/x-icon';
        default:
            return 'application/octet-stream';
    }
}

function isStaticFile($uri)
{
    return preg_match('/\.(css|js|woff2?|eot|ttf|svg|png|jpg|jpeg|gif|ico)$/', $uri) === 1;
}

function serveStaticFile($filePath)
{
    if (!file_exists($filePath)) {
        return false;
    }

    $mimeType = getMimeType($filePath);

    header("Content-Type: $mimeType");
    header("Cache-Control: public, max-age=3600");
    echo file_get_contents($filePath);
    exit;
}
?>
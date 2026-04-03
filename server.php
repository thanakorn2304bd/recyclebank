<?php

$requestPath = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$publicPath = realpath(__DIR__.'/public');
$staticPath = $requestPath === '/' ? false : realpath(__DIR__.'/public'.$requestPath);

if (
    $publicPath !== false
    && $staticPath !== false
    && is_file($staticPath)
    && str_starts_with($staticPath, $publicPath.DIRECTORY_SEPARATOR)
) {
    $mimeType = mime_content_type($staticPath) ?: 'application/octet-stream';

    header('Content-Type: '.$mimeType);
    readfile($staticPath);

    return true;
}

require __DIR__.'/public/index.php';

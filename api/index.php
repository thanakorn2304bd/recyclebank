<?php

$projectRoot = dirname(__DIR__);
$requestPath = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');
$publicPath = realpath($projectRoot.'/public');
$staticPath = $requestPath === '/' ? false : realpath($projectRoot.'/public'.$requestPath);

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

require $projectRoot.'/public/index.php';

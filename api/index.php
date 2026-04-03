<?php

function detectStaticMimeType(string $path): string
{
    return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
        'css' => 'text/css; charset=UTF-8',
        'js', 'mjs' => 'application/javascript; charset=UTF-8',
        'json', 'map' => 'application/json; charset=UTF-8',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'avif' => 'image/avif',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'otf' => 'font/otf',
        'eot' => 'application/vnd.ms-fontobject',
        'txt' => 'text/plain; charset=UTF-8',
        default => mime_content_type($path) ?: 'application/octet-stream',
    };
}

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
    $mimeType = detectStaticMimeType($staticPath);

    header('Content-Type: '.$mimeType);
    header('Content-Length: '.filesize($staticPath));
    header('Cache-Control: public, max-age=31536000, immutable');

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        readfile($staticPath);
    }

    return true;
}

require $projectRoot.'/public/index.php';

<?php
// Simple router dla PHP CLI server

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Ścieżka do pliku w public
$file = __DIR__ . '/public' . $uri;

// Jeśli plik istnieje → serwuj go bezpośrednio
if ($uri !== '/' && file_exists($file) && is_file($file)) {
    $ext = pathinfo($file, PATHINFO_EXTENSION);

    $types = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'ico' => 'image/x-icon',
        'svg' => 'image/svg+xml',
        'mp4' => 'video/mp4'
    ];

    header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
    header('Accept-Ranges: bytes');

    $filesize = filesize($file);

    // Range requests (video streaming)
    if (isset($_SERVER['HTTP_RANGE'])) {
        if (preg_match('/bytes=(\d+)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
            $start = intval($matches[1]);
            $end = $matches[2] !== '' ? intval($matches[2]) : $filesize - 1;

            header('HTTP/1.1 206 Partial Content');
            header("Content-Range: bytes $start-$end/$filesize");
            header('Content-Length: ' . ($end - $start + 1));

            $fp = fopen($file, 'rb');
            fseek($fp, $start);
            echo fread($fp, $end - $start + 1);
            fclose($fp);
            exit;
        }
    }

    header('Content-Length: ' . $filesize);
    readfile($file);
    exit;
}

// Wszystko inne → aplikacja
require __DIR__ . '/public/index.php';
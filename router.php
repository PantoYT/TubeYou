<?php
// Simple router dla PHP CLI server

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Jeśli to request do static file (css, uploads, js)
if (strpos($uri, '/css/') === 0 || strpos($uri, '/uploads/') === 0 || strpos($uri, '/js/') === 0) {
    $file = __DIR__ . '/public' . $uri;
    
    if (file_exists($file) && is_file($file)) {
        // Serwuj plik z Range Request support
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $types = [
            'css' => 'text/css',
            'js' => 'application/javascript', 
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'mp4' => 'video/mp4'
        ];
        
        header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
        header('Accept-Ranges: bytes');
        
        $filesize = filesize($file);
        
        // Handle Range requests dla video streaming
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
                return true;
            }
        }
        
        header('Content-Length: ' . $filesize);
        readfile($file);
        return true;
    }
}

// Wszystko inne routuje przez index.php
require __DIR__ . '/index.php';




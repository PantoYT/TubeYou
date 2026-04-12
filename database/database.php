<?php

$db = new PDO(
    'mysql:host=localhost',
    'root',
    ''
);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    $schema = file_get_contents(__DIR__ . '/schema.sql');
    foreach (explode(';', $schema) as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $db->exec($query);
        }
    }
    echo "Schema wczytana\n";

    $seed = file_get_contents(__DIR__ . '/seed.sql');
    foreach (explode(';', $seed) as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $db->exec($query);
        }
    }
    echo "Seed wczytany\n";

    echo "Czyszczenie osieroconych plików...\n";

    $uploadsDir = __DIR__ . '/../public/uploads/videos/';
    $thumbsDir  = __DIR__ . '/../public/uploads/thumbnails/';
    $avatarsDir = __DIR__ . '/../public/uploads/avatars/';
    $bannersDir = __DIR__ . '/../public/uploads/banners/';

    $deleted = 0;

    // Pobierz wszystkie src z bazy
    $validBases = [];
    foreach ($db->query("SELECT src FROM tubeyou.videos")->fetchAll(PDO::FETCH_COLUMN) as $src) {
        $base = pathinfo($src, PATHINFO_FILENAME);
        $validBases[$base] = true;
    }

    // Pobierz wszystkie thumbnail paths
    $validThumbs = [];
    foreach ($db->query("SELECT thumbnail FROM tubeyou.videos")->fetchAll(PDO::FETCH_COLUMN) as $t) {
        $validThumbs[$t] = true;
    }

    // Pobierz wszystkie user ids
    $validUserIds = [];
    foreach ($db->query("SELECT id FROM tubeyou.users")->fetchAll(PDO::FETCH_COLUMN) as $id) {
        $validUserIds[(string)$id] = true;
    }

    // Wyczyść video pliki
    if (is_dir($uploadsDir)) {
        foreach (glob($uploadsDir . '*/') as $userDir) {
            foreach (glob($userDir . '*.{mp4,jpg}', GLOB_BRACE) as $file) {
                $base      = pathinfo($file, PATHINFO_FILENAME);
                $cleanBase = preg_replace('/_(1080p|720p|480p|360p|thumb)$/', '', $base);
                if (!isset($validBases[$cleanBase]) && !isset($validBases[$base])) {
                    unlink($file);
                    echo "Usunięto: $file\n";
                    $deleted++;
                }
            }
        }
    }

    // Wyczyść thumbnails
    if (is_dir($thumbsDir)) {
        foreach (glob($thumbsDir . '*/*.jpg') as $file) {
            $rel = '/uploads/thumbnails/' . basename(dirname($file)) . '/' . basename($file);
            if (!isset($validThumbs[$rel])) {
                unlink($file);
                echo "Usunięto thumbnail: $file\n";
                $deleted++;
            }
        }
    }

    // Wyczyść avatary osieroconych userów
    foreach ([$avatarsDir, $bannersDir] as $dir) {
        if (!is_dir($dir)) continue;
        foreach (glob($dir . '*/') as $userDir) {
            $uid = basename($userDir);
            if (!isset($validUserIds[$uid])) {
                array_map('unlink', glob($userDir . '*'));
                rmdir($userDir);
                echo "Usunięto folder: $userDir\n";
                $deleted++;
            }
        }
    }

    echo "Usunięto $deleted osieroconych plików.\n";
    echo "Baza zresetowana!\n";

} catch (Exception $e) {
    echo "Błąd: " . $e->getMessage() . "\n";
}
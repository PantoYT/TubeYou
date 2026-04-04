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
    echo "Baza zresetowana!\n";
    
} catch (Exception $e) {
    echo "Błąd: " . $e->getMessage() . "\n";
}

<?php
// Reset database - usuwa stare dane i wstawia nowe

$db = new PDO(
    'mysql:host=localhost',
    'root',
    ''
);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // Usuń starą bazę
    $db->exec("DROP DATABASE IF EXISTS tubeyou");
    echo "Baza tubeyou usunięta\n";
    
    // Wykonaj schema
    $schema = file_get_contents(__DIR__ . '/database/schema.sql');
    foreach (explode(';', $schema) as $query) {
        $query = trim($query);
        if (!empty($query)) {
            $db->exec($query);
        }
    }
    echo "Schema wczytana\n";
    
    // Wykonaj seed
    $seed = file_get_contents(__DIR__ . '/database/seed.sql');
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

<?php

class Database
{
    private static ?Database $instance = null;
    private PDO $conn;

    private function __construct()
    {
        $tempConn = new PDO(
            'mysql:host=localhost',
            'root',
            ''
        );
        $tempConn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $schema = file_get_contents(__DIR__ . '/schema.sql');
        foreach (explode(';', $schema) as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $tempConn->exec($query);
            }
        }

        $checkStmt = $tempConn->prepare("SELECT COUNT(*) FROM tubeyou.users");
        $checkStmt->execute();
        if ($checkStmt->fetchColumn() == 0) {
            $seed = file_get_contents(__DIR__ . '/seed.sql');
            foreach (explode(';', $seed) as $query) {
                $query = trim($query);
                if (!empty($query)) {
                    $tempConn->exec($query);
                }
            }
        }

        $this->conn = new PDO(
            'mysql:host=localhost;dbname=tubeyou',
            'root',
            ''
        );

        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->conn;
    }
}
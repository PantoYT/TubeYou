<?php

class Database
{
    private static ?Database $instance = null;
    private PDO $conn;

    private function __construct()
    {
        $host   = $_ENV['DB_HOST']     ?? 'localhost';
        $port   = $_ENV['DB_PORT']     ?? '3306';
        $dbname = $_ENV['DB_NAME']     ?? 'tubeyou';
        $user   = $_ENV['DB_USER']     ?? 'root';
        $pass   = $_ENV['DB_PASSWORD'] ?? '';

        $attempts = 3;
        while ($attempts--) {
            try {
                $this->conn = new PDO(
                    "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4",
                    $user, $pass,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_TIMEOUT            => 5,
                        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                    ]
                );
                return;
            } catch (PDOException $e) {
                if ($attempts === 0) throw $e;
                sleep(1);
            }
        }
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
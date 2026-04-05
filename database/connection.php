<?php

class Database
{
    private static ?Database $instance = null;
    private PDO $conn;

    private function __construct()
    {
        $this->conn = new PDO(
            'mysql:host=localhost;dbname=tubeyou;charset=utf8mb4',
            'root',
            ''
        );
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
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
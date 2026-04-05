<?php

class UserRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function save(string $email, string $name, string $password): void
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $this->db->prepare(
            "INSERT INTO users (email, displayName, password) VALUES (?, ?, ?)"
        );

        $stmt->execute([$email, $name, $hash]);
    }
    
    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateProfile(int $id, string $displayName, string $bio): void
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET displayName = ?, bio = ? WHERE id = ?"
        );
        $stmt->execute([$displayName, $bio, $id]);
    }

    public function updateAvatar(int $id, string $path): void
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET avatar = ? WHERE id = ?"
        );
        $stmt->execute([$path, $id]);
    }

    public function saveUnverified(string $email, string $name, string $password, string $token): void
    {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare(
            "INSERT INTO users (email, displayName, password, verifyToken, emailVerified) 
            VALUES (?, ?, ?, ?, 0)"
        );
        $stmt->execute([$email, $name, $hash, $token]);
    }

    public function verifyEmail(string $token): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE users SET emailVerified = 1, verifyToken = NULL 
            WHERE verifyToken = ? AND emailVerified = 0"
        );
        $stmt->execute([$token]);
        return $stmt->rowCount() > 0;
    }

    public function findByVerifyToken(string $token): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE verifyToken = ?"
        );
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
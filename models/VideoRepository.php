<?php

class VideoRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function findAll(): array
    {
        $stmt = $this->db->query(
            "SELECT v.*, u.displayName as creatorName, u.avatar as creatorAvatar 
            FROM videos v 
            LEFT JOIN users u ON v.userId = u.id 
            ORDER BY v.createdAt DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.displayName as creatorName, u.avatar as creatorAvatar  FROM videos v 
            LEFT JOIN users u ON v.userId = u.id 
            WHERE v.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function save(int $userId, string $title, string $description, string $src, string $thumbnail, int $duration = 0): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO videos (userId, title, description, src, thumbnail, duration) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $title, $description, $src, $thumbnail, $duration]);
    }

    public function incrementViews(int $id): void
    {
        $stmt = $this->db->prepare("UPDATE videos SET views = views + 1 WHERE id = ?");
        $stmt->execute([$id]);
    }

    public function search(string $query): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.displayName as creatorName, u.avatar as creatorAvatar
            FROM videos v
            JOIN users u ON v.userId = u.id
            WHERE v.title LIKE ?
                OR v.description LIKE ?
            ORDER BY v.createdAt DESC"
        );
        $term = '%' . $query . '%';
        $stmt->execute([$term, $term]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
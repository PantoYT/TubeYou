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
        $query = trim($query);

        if (strlen($query) < 3) {
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
        } else {
            $stmt = $this->db->prepare(
                "SELECT v.*, u.displayName as creatorName, u.avatar as creatorAvatar,
                        MATCH(v.title, v.description) AGAINST (? IN NATURAL LANGUAGE MODE) as score
                FROM videos v
                JOIN users u ON v.userId = u.id
                WHERE MATCH(v.title, v.description) AGAINST (? IN NATURAL LANGUAGE MODE)
                ORDER BY score DESC"
            );

            $stmt->execute([$query, $query]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function findAllPaginated(int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare(
            "SELECT v.*, u.displayName as creatorName, u.avatar as creatorAvatar,
                    (v.views * 0.3 + 
                    (SELECT COUNT(*) FROM likes WHERE videoId = v.id AND type = 1) * 0.5 +
                    (100 / (TIMESTAMPDIFF(HOUR, v.createdAt, NOW()) + 1))) as score
            FROM videos v
            LEFT JOIN users u ON v.userId = u.id
            ORDER BY score DESC
            LIMIT ? OFFSET ?"
        );

        $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countAll(): int
    {
        return (int)$this->db->query("SELECT COUNT(*) FROM videos")->fetchColumn();
    }

    public function findSuggested(int $videoId, int $userId, int $limit = 10): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.displayName as creatorName, u.avatar as creatorAvatar,
                    (v.views * 0.3 +
                    (SELECT COUNT(*) FROM likes WHERE videoId = v.id AND type = 1) * 0.5 +
                    (100 / (TIMESTAMPDIFF(HOUR, v.createdAt, NOW()) + 1))) as score
            FROM videos v
            LEFT JOIN users u ON v.userId = u.id
            WHERE v.id != ?
            ORDER BY score DESC
            LIMIT ?"
        );
        $stmt->bindValue(1, $videoId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
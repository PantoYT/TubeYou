<?php

class LikeRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function toggleLike(int $userId, int $videoId): bool
    {
        $existing = $this->getLike($userId, $videoId);

        if (!$existing) {
            $this->addLike($userId, $videoId, 1);
            return true;
        }

        if ((int)$existing['type'] === 1) {
            $this->removeLike($userId, $videoId);
            return false;
        }

        // był dislike → zmień na like
        $this->updateLike($userId, $videoId, 1);
        return true;
    }

    public function toggleDislike(int $userId, int $videoId): bool
    {
        $existing = $this->getLike($userId, $videoId);

        if (!$existing) {
            $this->addLike($userId, $videoId, 2);
            return true;
        }

        if ((int)$existing['type'] === 2) {
            $this->removeLike($userId, $videoId);
            return false;
        }

        // był like → zmień na dislike
        $this->updateLike($userId, $videoId, 2);
        return true;
    }

    public function addLike(int $userId, int $videoId, int $type): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO likes (userId, videoId, type) VALUES (?, ?, ?)"
        );
        $stmt->execute([$userId, $videoId, $type]);
    }

    public function updateLike(int $userId, int $videoId, int $type): void
    {
        $stmt = $this->db->prepare(
            "UPDATE likes SET type = ? WHERE userId = ? AND videoId = ?"
        );
        $stmt->execute([$type, $userId, $videoId]);
    }

    public function removeLike(int $userId, int $videoId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM likes WHERE userId = ? AND videoId = ?"
        );
        $stmt->execute([$userId, $videoId]);
    }

    public function getLike(int $userId, int $videoId)
    {
        $stmt = $this->db->prepare(
            "SELECT type FROM likes WHERE userId = ? AND videoId = ?"
        );
        $stmt->execute([$userId, $videoId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function isLiked(int $userId, int $videoId): bool
    {
        $like = $this->getLike($userId, $videoId);
        return $like && (int)$like['type'] === 1;
    }

    public function isDisliked(int $userId, int $videoId): bool
    {
        $like = $this->getLike($userId, $videoId);
        return $like && (int)$like['type'] === 2;
    }

    public function countLikes(int $videoId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM likes WHERE videoId = ? AND type = 1"
        );
        $stmt->execute([$videoId]);
        return (int)$stmt->fetchColumn();
    }

    public function countDislikes(int $videoId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM likes WHERE videoId = ? AND type = 2"
        );
        $stmt->execute([$videoId]);
        return (int)$stmt->fetchColumn();
    }
}
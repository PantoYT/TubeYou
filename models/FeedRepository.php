<?php

class FeedRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function getSubscriptionFeed(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.displayName as creatorName, u.avatar as creatorAvatar
             FROM videos v
             JOIN users u ON v.userId = u.id
             JOIN subscribes s ON s.subscribedToId = v.userId
             WHERE s.subscriberId = ?
             ORDER BY v.createdAt DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getHistory(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.displayName as creatorName, u.avatar as creatorAvatar,
                    h.watchedAt
             FROM videos v
             JOIN users u ON v.userId = u.id
             JOIN history h ON h.videoId = v.id
             WHERE h.userId = ?
             ORDER BY h.watchedAt DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLiked(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.displayName as creatorName, u.avatar as creatorAvatar
             FROM videos v
             JOIN users u ON v.userId = u.id
             JOIN likes l ON l.videoId = v.id
             WHERE l.userId = ? AND l.type = 1
             ORDER BY v.createdAt DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getShorts(int $page = 1, int $perPage = 12): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare(
            "SELECT v.*, u.displayName as creatorName, u.avatar as creatorAvatar
             FROM videos v
             JOIN users u ON v.userId = u.id
             WHERE v.isShort = 1
             ORDER BY v.createdAt DESC
             LIMIT ? OFFSET ?"
        );
        $stmt->bindValue(1, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(2, $offset,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countShorts(): int
    {
        return (int)$this->db->query(
            "SELECT COUNT(*) FROM videos WHERE isShort = 1"
        )->fetchColumn();
    }

    public function recordHistory(int $userId, int $videoId): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO history (userId, videoId) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE watchedAt = CURRENT_TIMESTAMP"
        );
        $stmt->execute([$userId, $videoId]);
    }

    public function clearHistory(int $userId): void
    {
        $stmt = $this->db->prepare("DELETE FROM history WHERE userId = ?");
        $stmt->execute([$userId]);
    }

    public function toggleWatchLater(int $userId, int $videoId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM watchLater WHERE userId = ? AND videoId = ?"
        );
        $stmt->execute([$userId, $videoId]);

        if ($stmt->fetch()) {
            $this->db->prepare(
                "DELETE FROM watchLater WHERE userId = ? AND videoId = ?"
            )->execute([$userId, $videoId]);
            return false;
        }

        $this->db->prepare(
            "INSERT INTO watchLater (userId, videoId) VALUES (?, ?)"
        )->execute([$userId, $videoId]);
        return true;
    }

    public function isInWatchLater(int $userId, int $videoId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM watchLater WHERE userId = ? AND videoId = ?"
        );
        $stmt->execute([$userId, $videoId]);
        return (bool)$stmt->fetch();
    }

    public function getWatchLater(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.displayName as creatorName, u.avatar as creatorAvatar
            FROM videos v
            JOIN users u ON v.userId = u.id
            JOIN watchLater wl ON wl.videoId = v.id
            WHERE wl.userId = ?
            ORDER BY wl.addedAt DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
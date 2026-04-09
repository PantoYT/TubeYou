<?php

class NotificationRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function create(int $userId, int $fromUserId, string $type, ?int $videoId = null, ?int $commentId = null): void
    {
        if ($userId === $fromUserId) return;

        $stmt = $this->db->prepare(
            "INSERT INTO notifications (userId, fromUserId, type, videoId, commentId)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $fromUserId, $type, $videoId, $commentId]);
    }

    public function getForUser(int $userId, int $limit = 20): array
    {
        $stmt = $this->db->prepare(
            "SELECT n.*, u.displayName as fromName, u.avatar as fromAvatar,
                    v.title as videoTitle
             FROM notifications n
             JOIN users u ON u.id = n.fromUserId
             LEFT JOIN videos v ON v.id = n.videoId
             WHERE n.userId = ?
             ORDER BY n.createdAt DESC
             LIMIT ?"
        );
        $stmt->bindValue(1, $userId, PDO::PARAM_INT);
        $stmt->bindValue(2, $limit,  PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countUnread(int $userId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM notifications WHERE userId = ? AND isRead = 0"
        );
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function markAllRead(int $userId): void
    {
        $stmt = $this->db->prepare(
            "UPDATE notifications SET isRead = 1 WHERE userId = ?"
        );
        $stmt->execute([$userId]);
    }
}
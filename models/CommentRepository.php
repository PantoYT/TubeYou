<?php

class CommentRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function getForVideo(int $videoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.displayName, u.avatar,
                    SUM(CASE WHEN cl.type = 1 THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN cl.type = -1 THEN 1 ELSE 0 END) as dislikes
             FROM comments c
             JOIN users u ON c.userId = u.id
             LEFT JOIN commentLikes cl ON cl.commentId = c.id
             WHERE c.videoId = ? AND c.parentId IS NULL
             GROUP BY c.id
             ORDER BY c.pinned DESC, c.createdAt DESC"
        );
        $stmt->execute([$videoId]);
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($comments as &$comment) {
            $comment['replies'] = $this->getReplies($comment['id']);
        }

        return $comments;
    }

    public function getReplies(int $parentId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, u.displayName, u.avatar,
                    SUM(CASE WHEN cl.type = 1 THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN cl.type = -1 THEN 1 ELSE 0 END) as dislikes
             FROM comments c
             JOIN users u ON c.userId = u.id
             LEFT JOIN commentLikes cl ON cl.commentId = c.id
             WHERE c.parentId = ?
             GROUP BY c.id
             ORDER BY c.createdAt ASC"
        );
        $stmt->execute([$parentId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create(int $userId, int $videoId, string $content, ?int $parentId = null): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO comments (userId, videoId, content, parentId) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $videoId, $content, $parentId]);
    }

    public function delete(int $commentId, int $userId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM comments WHERE id = ? AND userId = ?"
        );
        $stmt->execute([$commentId, $userId]);
    }

    public function pin(int $commentId, int $videoId, int $ownerId): void
    {
        $this->db->prepare(
            "UPDATE comments SET pinned = 0 WHERE videoId = ?"
        )->execute([$videoId]);

        $stmt = $this->db->prepare(
            "UPDATE comments c
             JOIN videos v ON v.id = c.videoId
             SET c.pinned = 1
             WHERE c.id = ? AND v.userId = ?"
        );
        $stmt->execute([$commentId, $ownerId]);
    }

    public function toggleLike(int $userId, int $commentId, int $type): void
    {
        $stmt = $this->db->prepare(
            "SELECT type FROM commentLikes WHERE userId = ? AND commentId = ?"
        );
        $stmt->execute([$userId, $commentId]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existing) {
            $this->db->prepare(
                "INSERT INTO commentLikes (userId, commentId, type) VALUES (?, ?, ?)"
            )->execute([$userId, $commentId, $type]);
        } elseif ((int)$existing['type'] === $type) {
            $this->db->prepare(
                "DELETE FROM commentLikes WHERE userId = ? AND commentId = ?"
            )->execute([$userId, $commentId]);
        } else {
            $this->db->prepare(
                "UPDATE commentLikes SET type = ? WHERE userId = ? AND commentId = ?"
            )->execute([$type, $userId, $commentId]);
        }
    }

    public function count(int $videoId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM comments WHERE videoId = ?"
        );
        $stmt->execute([$videoId]);
        return (int)$stmt->fetchColumn();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM comments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getForVideoPaginated(int $videoId, int $page = 1, int $perPage = 10): array
    {
        $offset = ($page - 1) * $perPage;
        $stmt = $this->db->prepare(
            "SELECT c.*, u.displayName, u.avatar,
                    SUM(CASE WHEN cl.type = 1 THEN 1 ELSE 0 END) as likes,
                    SUM(CASE WHEN cl.type = -1 THEN 1 ELSE 0 END) as dislikes
            FROM comments c
            JOIN users u ON c.userId = u.id
            LEFT JOIN commentLikes cl ON cl.commentId = c.id
            WHERE c.videoId = ? AND c.parentId IS NULL
            GROUP BY c.id
            ORDER BY c.pinned DESC, c.createdAt DESC
            LIMIT ? OFFSET ?"
        );
        $stmt->bindValue(1, $videoId, PDO::PARAM_INT);
        $stmt->bindValue(2, $perPage, PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, PDO::PARAM_INT);
        $stmt->execute();
        $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($comments as &$comment) {
            $comment['replies'] = $this->getReplies($comment['id']);
        }
        return $comments;
    }

    public function update(int $commentId, int $userId, string $content): void
    {
        $stmt = $this->db->prepare(
            "UPDATE comments SET content = ? WHERE id = ? AND userId = ?"
        );
        $stmt->execute([$content, $commentId, $userId]);
    }
}
<?php

class TagRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function syncTags(int $videoId, string $rawTags): void
    {
        $this->db->prepare(
            "DELETE FROM videoTags WHERE videoId = ?"
        )->execute([$videoId]);

        $tags = array_unique(array_filter(
            array_map('trim', explode(',', $rawTags))
        ));

        foreach ($tags as $tag) {
            $tag = strtolower($tag);
            if (!$tag || strlen($tag) > 50) continue;

            $this->db->prepare(
                "INSERT IGNORE INTO tags (name) VALUES (?)"
            )->execute([$tag]);

            $tagId = $this->db->lastInsertId() ?: $this->getTagId($tag);

            $this->db->prepare(
                "INSERT IGNORE INTO videoTags (videoId, tagId) VALUES (?, ?)"
            )->execute([$videoId, $tagId]);
        }
    }

    public function getTagId(string $name): int
    {
        $stmt = $this->db->prepare("SELECT id FROM tags WHERE name = ?");
        $stmt->execute([$name]);
        return (int)$stmt->fetchColumn();
    }

    public function getForVideo(int $videoId): array
    {
        $stmt = $this->db->prepare(
            "SELECT t.name FROM tags t
             JOIN videoTags vt ON vt.tagId = t.id
             WHERE vt.videoId = ?"
        );
        $stmt->execute([$videoId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getVideosByTag(string $tag): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.displayName as creatorName, u.avatar as creatorAvatar
             FROM videos v
             JOIN users u ON v.userId = u.id
             JOIN videoTags vt ON vt.videoId = v.id
             JOIN tags t ON t.id = vt.tagId
             WHERE t.name = ?
             ORDER BY v.createdAt DESC"
        );
        $stmt->execute([$tag]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
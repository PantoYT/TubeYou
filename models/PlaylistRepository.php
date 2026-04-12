<?php

class PlaylistRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function create(int $userId, string $title, string $description = '', int $isPublic = 1): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO playlists (userId, title, description, isPublic) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$userId, $title, $description, $isPublic]);
        return (int)$this->db->lastInsertId();
    }

    public function addVideo(int $playlistId, int $videoId): void
    {
        $pos  = $this->count($playlistId);
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO playlistVideos (playlistId, videoId, position) VALUES (?, ?, ?)"
        );
        $stmt->execute([$playlistId, $videoId, $pos]);
    }

    public function removeVideo(int $playlistId, int $videoId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM playlistVideos WHERE playlistId = ? AND videoId = ?"
        );
        $stmt->execute([$playlistId, $videoId]);
    }

    public function getForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, COUNT(pv.videoId) as videoCount
             FROM playlists p
             LEFT JOIN playlistVideos pv ON pv.playlistId = p.id
             WHERE p.userId = ?
             GROUP BY p.id
             ORDER BY p.createdAt DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById(int $id): array|false
    {
        $stmt = $this->db->prepare("SELECT * FROM playlists WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getVideos(int $playlistId): array
    {
        $stmt = $this->db->prepare(
            "SELECT v.*, u.displayName as creatorName, u.avatar as creatorAvatar
             FROM videos v
             JOIN playlistVideos pv ON pv.videoId = v.id
             JOIN users u ON u.id = v.userId
             WHERE pv.playlistId = ?
             ORDER BY pv.position ASC"
        );
        $stmt->execute([$playlistId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function delete(int $playlistId, int $userId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM playlists WHERE id = ? AND userId = ?"
        );
        $stmt->execute([$playlistId, $userId]);
    }

    public function count(int $playlistId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM playlistVideos WHERE playlistId = ?"
        );
        $stmt->execute([$playlistId]);
        return (int)$stmt->fetchColumn();
    }

    public function isOwner(int $playlistId, int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM playlists WHERE id = ? AND userId = ?"
        );
        $stmt->execute([$playlistId, $userId]);
        return (bool)$stmt->fetch();
    }
}
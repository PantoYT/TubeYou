<?php

class SubRepository
{
    private PDO $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    public function toggleSub(int $subscriberId, int $subscribedToId): bool
    {
        if ($this->isSubbed($subscriberId, $subscribedToId)) {
            $this->removeSub($subscriberId, $subscribedToId);
            return false;
        } else {
            $this->addSub($subscriberId, $subscribedToId);
            return true;
        }
    }

    public function addSub(int $subscriberId, int $subscribedToId): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO subscribes (subscriberId, subscribedToId) VALUES (?, ?)"
        );
        $stmt->execute([$subscriberId, $subscribedToId]);
    }

    public function removeSub(int $subscriberId, int $subscribedToId): void
    {
        $stmt = $this->db->prepare(
            "DELETE FROM subscribes WHERE subscriberId = ? AND subscribedToId = ?"
        );
        $stmt->execute([$subscriberId, $subscribedToId]);
    }

    public function isSubbed(int $subscriberId, int $subscribedToId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT 1 FROM subscribes WHERE subscriberId = ? AND subscribedToId = ?"
        );
        $stmt->execute([$subscriberId, $subscribedToId]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function countSubs(int $subscribedToId): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as subCount FROM subscribes WHERE subscribedToId = ?"
        );
        $stmt->execute([$subscribedToId]);
        return (int)$stmt->fetch(PDO::FETCH_ASSOC)['subCount'];
    }
}
<?php

class Notification {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($user_id, $title, $message) {
        $query = "INSERT INTO notifications (user_id, title, message) VALUES (:user_id, :title, :message)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':title' => $title,
            ':message' => $message
        ]);
    }

    public function getForUser($user_id, $limit = 10) {
        $query = "SELECT id, title, message, is_read, created_at
                  FROM notifications
                  WHERE user_id = :user_id
                  ORDER BY created_at DESC
                  LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadCount($user_id) {
        $query = "SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return (int)$stmt->fetchColumn();
    }

    public function markAllRead($user_id) {
        $query = "UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':user_id' => $user_id]);
    }
}

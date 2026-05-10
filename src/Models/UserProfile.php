<?php
class UserProfile {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getUserById($user_id) {
        $query = "SELECT id, first_name, last_name, email, phone_number, anchor_photo_path, bio FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateBio($user_id, $bio) {
        $query = "UPDATE users SET bio = :bio WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':bio' => $bio, ':id' => $user_id]);
    }

    public function getUserSkillIds($user_id) {
        $query = "SELECT skill_id FROM user_skills WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'skill_id');
    }

    public function getUserSkills($user_id) {
        $query = "SELECT s.id, s.name
                  FROM skills s
                  JOIN user_skills us ON us.skill_id = s.id
                  WHERE us.user_id = :user_id
                  ORDER BY s.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function clearUserSkills($user_id) {
        $query = "DELETE FROM user_skills WHERE user_id = :user_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':user_id' => $user_id]);
    }

    public function saveUserSkills($user_id, array $skill_ids) {
        $this->clearUserSkills($user_id);
        if (empty($skill_ids)) {
            return true;
        }

        $query = "INSERT INTO user_skills (user_id, skill_id) VALUES (:user_id, :skill_id)";
        $stmt = $this->db->prepare($query);

        foreach ($skill_ids as $skill_id) {
            $stmt->execute([':user_id' => $user_id, ':skill_id' => $skill_id]);
        }

        return true;
    }
}

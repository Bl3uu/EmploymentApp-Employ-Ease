<?php
class Skill {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAllSkills() {
        $query = "SELECT id, name FROM skills ORDER BY name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByName($name) {
        $query = "SELECT id, name FROM skills WHERE name = :name LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':name' => trim($name)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createSkill($name) {
        $name = trim($name);
        if (empty($name)) {
            return null;
        }

        $existing = $this->findByName($name);
        if ($existing) {
            return (int)$existing['id'];
        }

        $query = "INSERT IGNORE INTO skills (name) VALUES (:name)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':name' => $name]);

        $id = (int)$this->db->lastInsertId();
        if ($id > 0) {
            return $id;
        }

        $existing = $this->findByName($name);
        return $existing ? (int)$existing['id'] : null;
    }

    public function deleteSkill($id) {
        $query = "DELETE FROM skills WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => (int)$id]);
    }

    public function updateSkillName($id, $name) {
        $query = "UPDATE skills SET name = :name WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':name' => trim($name), ':id' => (int)$id]);
    }
}

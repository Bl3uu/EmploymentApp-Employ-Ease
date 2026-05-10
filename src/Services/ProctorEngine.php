<?php
// src/Services/ProctorEngine.php

class ProctorEngine {
    private $db;
    private $maxViolations = 3;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Records a security violation in the database.
     */
    public function logViolation($application_id, $user_id, $type, $details) {
    $query = "INSERT INTO audit_logs (application_id, user_id, action, description, ip_address) 
              VALUES (:app_id, :u_id, :action, :desc, :ip)";
    $stmt = $this->db->prepare($query);
    return $stmt->execute([
        ':app_id' => $application_id,
        ':u_id'   => $user_id,
        ':action' => 'EXAM_VIOLATION',
        ':desc'   => "Type: $type | $details",
        ':ip'     => $_SERVER['REMOTE_ADDR'] ?? null
    ]);
}

    /**
     * Checks if the candidate has exceeded the allowed number of warnings.
     */
    public function isCandidateFlagged($application_id) {
        $query = "SELECT COUNT(*) as total FROM audit_logs WHERE application_id = :app_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':app_id' => $application_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return ($row['total'] >= $this->maxViolations);
    }

    /**
     * Fetches all violations for a specific application (for Recruiter review).
     */
    public function getViolations($application_id) {
        $query = "SELECT * FROM audit_logs WHERE application_id = :app_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':app_id' => $application_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
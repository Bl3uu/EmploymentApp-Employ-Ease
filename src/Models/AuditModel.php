<?php

class AuditModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function logAction($user_id, $action, $description, $application_id = null, $ip_address = null) {
        $query = "INSERT INTO audit_logs (user_id, application_id, action, description, ip_address)
                  VALUES (:user_id, :application_id, :action, :description, :ip_address)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':application_id' => $application_id,
            ':action' => $action,
            ':description' => $description,
            ':ip_address' => $ip_address
        ]);
    }

    public function logStatusChange($user_id, $application_id, $status) {
        return $this->logAction(
            $user_id,
            'Status Update',
            "Changed application status to {$status}",
            $application_id,
            $_SERVER['REMOTE_ADDR'] ?? null
        );
    }

    public function logProctorViolation($application_id, $user_id, $type, $details) {
        return $this->logAction(
            $user_id,
            'EXAM_VIOLATION',
            "Type: {$type} | {$details}",
            $application_id,
            $_SERVER['REMOTE_ADDR'] ?? null
        );
    }

    public function getLogsByApplication($application_id) {
        $query = "SELECT * FROM audit_logs
                  WHERE application_id = :application_id
                  ORDER BY created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':application_id' => $application_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasExamViolations($application_id) {
        $query = "SELECT COUNT(*) as count FROM audit_logs
                  WHERE application_id = :application_id AND action = 'EXAM_VIOLATION'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':application_id' => $application_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function getNotificationsForCandidate($user_id, $limit = 6) {
        $query = "SELECT al.*, j.title AS job_title, a.status AS app_status
                  FROM audit_logs al
                  JOIN applications a ON al.application_id = a.id
                  JOIN jobs j ON a.job_id = j.id
                  WHERE a.user_id = :user_id
                  ORDER BY al.created_at DESC
                  LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLatestInterviewConfirmation($application_id) {
        $query = "SELECT * FROM audit_logs
                  WHERE application_id = :application_id
                    AND action = 'Interview Confirmed'
                  ORDER BY created_at DESC
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':application_id' => $application_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}


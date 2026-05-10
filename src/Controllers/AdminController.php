<?php
require_once __DIR__ . '/../Models/Skill.php';

class AdminController {
    private $db;
    private $appModel;
    private $skillModel;
    private $auditModel;
    private $examModel;

    public function __construct($db, $appModel, $auditModel, $examModel) {
        $this->db = $db;
        $this->appModel = $appModel;
        $this->skillModel = new Skill($db);
        $this->auditModel = $auditModel;
        $this->examModel = $examModel;
    }

    /**
     * Verify admin access
     */
    private function requireAdmin() {
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
            header("Location: /");
            exit;
        }
    }

    /**
     * Validate CSRF token
     */
    private function validateCsrfToken() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? null;
            if (!$token || $token !== ($_SESSION['csrf_token'] ?? '')) {
                http_response_code(403);
                die('CSRF token validation failed');
            }
        }
    }

    /**
     * Display admin dashboard with analytics
     */
    public function dashboard() {
        $this->requireAdmin();

        // Get overview stats
        $stats = [
            'total_applicants' => $this->getTotalApplicants(),
            'avg_ai_score' => $this->getAverageAIScore(),
            'flagged_count' => $this->getFlaggedApplications(),
            'integrity_rate' => $this->getIntegrityRate(),
        ];

        // Get recent applications
        $recentApplications = $this->getRecentApplications();

        // Get recent violations
        $recentViolations = $this->getRecentViolations();

        // Get audit log summary
        $auditSummary = $this->getAuditSummary();

        include __DIR__ . '/../../templates/admin/dashboard.php';
    }

    public function skillManagement() {
        $this->requireAdmin();
        $skills = $this->skillModel->getAllSkills();
        include __DIR__ . '/../../templates/admin/skill_tags.php';
    }

    public function handleSkillAction() {
        $this->requireAdmin();
        $this->validateCsrfToken();

        $action = $_POST['action'] ?? '';
        $msg = 'skill_action_failed';

        if ($action === 'add') {
            $name = trim($_POST['skill_name'] ?? '');
            if ($name !== '') {
                $this->skillModel->createSkill($name);
                $msg = 'skill_added';
            }
        } elseif ($action === 'delete') {
            $skillId = (int)($_POST['skill_id'] ?? 0);
            if ($skillId > 0) {
                $this->skillModel->deleteSkill($skillId);
                $msg = 'skill_deleted';
            }
        }

        header("Location: admin-skill-tags?msg={$msg}");
        exit;
    }

    /**
     * Get total number of applicants
     */
    private function getTotalApplicants() {
        $query = "SELECT COUNT(DISTINCT user_id) as total FROM applications";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    /**
     * Get average AI score across all applications
     */
    private function getAverageAIScore() {
        $query = "SELECT AVG(ai_score) as avg_score FROM applications WHERE ai_score IS NOT NULL";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $avg = $result['avg_score'] ?? 0;
        return round($avg, 2);
    }

    /**
     * Get count of flagged applications (those with violations)
     */
    private function getFlaggedApplications() {
        $query = "SELECT COUNT(DISTINCT application_id) as flagged 
                  FROM audit_logs 
                  WHERE action LIKE '%violation%' OR description LIKE '%violation%'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['flagged'] ?? 0;
    }

    /**
     * Get integrity rate (percentage of passed exams without violations)
     */
    private function getIntegrityRate() {
        $query = "SELECT 
                    COUNT(DISTINCT CASE WHEN status = 'Passed' THEN id END) as passed_clean,
                    COUNT(DISTINCT CASE WHEN status = 'Passed' THEN id END) as total_passed
                  FROM applications
                  WHERE status = 'Passed'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total_passed'] == 0) {
            return 'N/A';
        }
        
        $rate = ($result['passed_clean'] / $result['total_passed']) * 100;
        return round($rate, 2) . '%';
    }

    /**
     * Get recent applications with candidate and position info
     */
    private function getRecentApplications($limit = 10) {
        $query = "SELECT 
                    a.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    j.title as position,
                    a.ai_score,
                    a.status,
                    a.applied_at
                  FROM applications a
                  JOIN users u ON a.user_id = u.id
                  JOIN jobs j ON a.job_id = j.id
                  ORDER BY a.applied_at DESC
                  LIMIT ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent proctoring violations from audit logs
     */
    private function getRecentViolations($limit = 5) {
        $query = "SELECT 
                    al.id,
                    al.action,
                    al.description,
                    u.first_name,
                    u.last_name,
                    al.created_at
                  FROM audit_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  WHERE al.description LIKE '%violation%' OR al.description LIKE '%tab%' OR al.description LIKE '%blur%'
                  ORDER BY al.created_at DESC
                  LIMIT ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get audit log summary
     */
    private function getAuditSummary($limit = 10) {
        $query = "SELECT 
                    al.id,
                    al.action,
                    al.description,
                    u.first_name,
                    u.last_name,
                    al.created_at
                  FROM audit_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  ORDER BY al.created_at DESC
                  LIMIT ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * View detailed application profile
     */
    public function viewApplicationProfile() {
        $this->requireAdmin();

        $appId = $_GET['id'] ?? null;

        if (!$appId) {
            header("Location: admin-dashboard");
            exit;
        }

        // Get application details
        $query = "SELECT 
                    a.*,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone_number,
                    j.title,
                    j.company
                  FROM applications a
                  JOIN users u ON a.user_id = u.id
                  JOIN jobs j ON a.job_id = j.id
                  WHERE a.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$appId]);
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$application) {
            header("Location: admin-dashboard");
            exit;
        }

        // Get violations for this application
        $violationsQuery = "SELECT * FROM audit_logs 
                            WHERE application_id = ? 
                            ORDER BY created_at DESC";
        $violationsStmt = $this->db->prepare($violationsQuery);
        $violationsStmt->execute([$appId]);
        $violations = $violationsStmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../../templates/admin/application_profile.php';
    }

    public function userManagement() {
        $this->requireAdmin();

        $query = "SELECT u.id, u.first_name, u.last_name, u.email, u.role_id, u.created_at, r.role_name
                  FROM users u
                  LEFT JOIN roles r ON u.role_id = r.id
                  ORDER BY u.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $rolesQuery = "SELECT id, role_name FROM roles ORDER BY id";
        $rolesStmt = $this->db->prepare($rolesQuery);
        $rolesStmt->execute();
        $roles = $rolesStmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../../templates/admin/user_management.php';
    }

    public function changeUserRole() {
        $this->requireAdmin();
        $this->validateCsrfToken();

        $userId = (int)($_POST['user_id'] ?? 0);
        $roleId = (int)($_POST['role_id'] ?? 0);

        if (!$userId || !$roleId) {
            header("Location: admin-manage-users?msg=invalid_selection");
            exit;
        }

        if ($userId === $_SESSION['user_id']) {
            header("Location: admin-manage-users?msg=cannot_change_own_role");
            exit;
        }

        $updateQuery = "UPDATE users SET role_id = :role_id WHERE id = :id";
        $updateStmt = $this->db->prepare($updateQuery);
        $updateStmt->execute([':role_id' => $roleId, ':id' => $userId]);

        header("Location: admin-manage-users?msg=role_updated");
        exit;
    }

    public function auditFeed() {
        $this->requireAdmin();

        $query = "SELECT al.id, al.action, al.description, al.created_at, u.first_name, u.last_name, a.job_id, j.title as job_title
                  FROM audit_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  LEFT JOIN applications a ON al.application_id = a.id
                  LEFT JOIN jobs j ON a.job_id = j.id
                  ORDER BY al.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../../templates/admin/audit_feed.php';
    }

    /**
     * Generate audit report (PDF)
     */
    public function generateAuditReport() {
        $this->requireAdmin();

        // Get all audit logs
        $query = "SELECT 
                    al.*,
                    u.first_name,
                    u.last_name,
                    a.id as app_id
                  FROM audit_logs al
                  LEFT JOIN users u ON al.user_id = u.id
                  LEFT JOIN applications a ON al.application_id = a.id
                  ORDER BY al.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Set headers for PDF download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="audit_report_' . date('Y-m-d') . '.pdf"');

        // Generate simple PDF (can be replaced with a library like TCPDF)
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->Cell(0, 10, 'System Audit Report - ' . date('Y-m-d H:i:s'), 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 10);
        $pdf->Ln(5);

        foreach ($logs as $log) {
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 5, $log['action'], 0, 1);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 4, 'User: ' . ($log['first_name'] . ' ' . $log['last_name']), 0, 1);
            $pdf->Cell(0, 4, 'Time: ' . $log['created_at'], 0, 1);
            $pdf->Cell(0, 4, 'Description: ' . $log['description'], 0, 1);
            $pdf->Ln(3);
        }

        $pdf->Output('audit_report_' . date('Y-m-d') . '.pdf', 'D');
        exit;
    }

    /**
     * Manage job postings (admin view)
     */
    public function manageJobs() {
        $this->requireAdmin();

        // Get all jobs
        $query = "SELECT j.*, u.first_name, u.last_name 
                  FROM jobs j
                  JOIN users u ON j.recruiter_id = u.id
                  ORDER BY j.created_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../../templates/admin/manage_jobs.php';
    }

    /**
     * Manage exam questions (admin view)
     */
    public function manageExams() {
        $this->requireAdmin();

        // Get all exams
        $query = "SELECT e.*, j.title, j.company 
                  FROM exams e
                  JOIN jobs j ON e.job_id = j.id
                  ORDER BY e.id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../../templates/admin/manage_exams.php';
    }

    /**
     * View system settings
     */
    public function settings() {
        $this->requireAdmin();
        include __DIR__ . '/../../templates/admin/settings.php';
    }
}
?>

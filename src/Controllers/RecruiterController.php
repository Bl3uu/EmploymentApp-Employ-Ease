<?php

require_once __DIR__ . '/../Models/Job.php';
require_once __DIR__ . '/../Models/Application.php';
require_once __DIR__ . '/../Models/ExamModel.php';
require_once __DIR__ . '/../Models/AuditModel.php';
require_once __DIR__ . '/../Models/Notification.php';
require_once __DIR__ . '/../Services/NotificationService.php';

class RecruiterController {
    private $db;
    private $jobModel;
    private $appModel;
    private $examModel;
    private $auditModel;
    private $notificationModel;
    private $notificationService;

    // --- CSRF Validation Helper ---
    private function validateCsrfToken() {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            error_log("CSRF validation failed for user: " . ($_SESSION['user_id'] ?? 'unknown'));
            header("Location: dashboard?error=csrf_invalid");
            exit;
        }
        return true;
    }

    public function __construct($db) {
        $this->db = $db;
        $this->jobModel = new Job($db);
        $this->appModel = new Application($db);
        $this->examModel = new ExamModel($db);
        $this->auditModel = new AuditModel($db);
        $this->notificationModel = new Notification($db);
        $this->notificationService = new NotificationService($db);
    }

    public function processJob() {
        if (isset($_GET['action']) && $_GET['action'] === 'close') {
            $jobId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
            if ($jobId) {
                $this->jobModel->closeJob($jobId);
            }
            header("Location: dashboard?msg=closed");
            exit;
        }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCsrfToken();
            $data = [
                'title' => htmlspecialchars($_POST['title'] ?? ''),
                'company' => htmlspecialchars($_POST['company'] ?? ''),
                'location' => htmlspecialchars($_POST['location'] ?? 'Remote'),
                'type' => $_POST['type'] ?? 'Remote',
                'description' => $_POST['description'] ?? '',
                'category' => htmlspecialchars($_POST['category'] ?? 'General'),
                'status' => $_POST['status'] ?? 'Active',
                'max_applicants' => (int)($_POST['max_applicants'] ?? 50),
                'recruiter_id' => $_SESSION['user_id']
            ];

            if (!empty($_POST['job_id'])) {
                $jobId = (int)$_POST['job_id'];
                $this->jobModel->updateJob($jobId, $data);
                header("Location: dashboard?msg=updated");
                exit;
            }

            $this->jobModel->createJob($data);
            header("Location: dashboard?msg=created");
            exit;
        }
    }

    public function assignExam() {
        $appId = $_GET['app_id'] ?? null;
        if ($appId) {
            $exam = $this->examModel->getByApplicationId($appId);
            if (!$exam) {
                header("Location: dashboard?msg=exam_missing_for_job");
                exit;
            }

            // Get current status to check if this is a reassignment
            $currentApp = $this->appModel->getById($appId);
            $currentStatus = $currentApp['status'] ?? '';

            // Allow retake from: Applied, Screened, Failed
            // Clear previous exam data when reassigning
            if (in_array($currentStatus, ['Applied', 'Screened', 'Failed'])) {
                $this->appModel->clearExamData($appId);
            }

            $this->appModel->assignExam($appId);
            $this->auditModel->logStatusChange($_SESSION['user_id'] ?? null, $appId, 'Exam Assigned');

            $applicant = $this->appModel->getById($appId);
            if ($applicant) {
                $jobTitle = $this->appModel->getJobById($applicant['job_id'])['title'] ?? 'this position';
                $this->notificationModel->create(
                    $applicant['user_id'],
                    'Exam Assigned',
                    "Your technical exam for {$jobTitle} is ready. Please visit your portal to begin."
                );
            }

            $this->notificationService->sendExamAssignmentEmail($appId);
        }

        header("Location: dashboard?msg=exam_assigned");
        exit;
    }

public function updateApplicationStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: dashboard");
            exit;
        }
        $this->validateCsrfToken();

        $appId = $_POST['app_id'] ?? null;
        $newStatus = $_POST['status'] ?? null;
        $jobId = $_POST['job_id'] ?? null;
        $action = $_POST['action'] ?? 'status_change'; // New: distinguish between status change and reset

        if ($appId) {
            if ($action === 'reset') {
                // Reset: Clear exam data but keep audit logs (history)
                $this->appModel->clearExamData($appId);
                $this->appModel->updateStatus($appId, 'Applied', $_SESSION['user_id'] ?? null);
                $msg = 'Application reset to Applied status.';
            } else {
                // Normal status change logic
                // Get current application status
                $currentApp = $this->appModel->getById($appId);
                $currentStatus = $currentApp['status'] ?? '';

                // Define valid status transitions (matches buttons in job_applicants_list.php)
                $allowedTransitions = [
                    'Applied' => ['Screened', 'Rejected', 'Exam Assigned'],
                    'Screened' => ['Applied', 'Exam Assigned', 'Rejected'],
                    'Exam Assigned' => ['Screened', 'Rejected'],
                    'Exam Completed' => ['Passed', 'Failed'],
                    'Passed' => ['Interviewing', 'Rejected'],
                    'Failed' => ['Applied'], // Reset to reconsider
                    'Interviewing' => ['Offered', 'Rejected'],
                    'Offered' => ['Rejected'],
                    'Rejected' => ['Applied']
                ];

                // Check if transition is valid
                if (!isset($allowedTransitions[$currentStatus]) || !in_array($newStatus, $allowedTransitions[$currentStatus])) {
                    $msg = urlencode("Cannot change from '$currentStatus' to '$newStatus'");
                    if (!empty($jobId)) {
                        header("Location: view-applicants?job_id=" . urlencode((string)$jobId) . "&msg=$msg");
                        exit;
                    }
                    header("Location: dashboard?msg=$msg");
                    exit;
                }

                // Special validation: Cannot re-assign exam if already Passed
                if ($currentStatus === 'Passed' && $newStatus === 'Exam Assigned') {
                    $msg = urlencode("Candidate has already passed. Create a new exam to retest.");
                    header("Location: view-applicants?job_id=" . urlencode((string)$jobId) . "&msg=$msg");
                    exit;
                }

                // Special validation: Check if exam exists when assigning
                if ($newStatus === 'Exam Assigned') {
                    $exam = $this->examModel->getByApplicationId($appId);
                    if (!$exam) {
                        if (!empty($jobId)) {
                            header("Location: view-applicants?job_id=" . urlencode((string)$jobId) . "&msg=exam_missing_for_job");
                            exit;
                        }
                        header("Location: dashboard?msg=exam_missing_for_job");
                        exit;
                    }
                }

                $this->appModel->updateStatus($appId, $newStatus, $_SESSION['user_id'] ?? null);
                $jobTitle = $this->appModel->getJobById($currentApp['job_id'])['title'] ?? 'this position';
                $this->notificationModel->create(
                    $currentApp['user_id'],
                    'Application status updated',
                    "Your application for {$jobTitle} is now '{$newStatus}'."
                );
                $this->notificationService->sendStatusChangeEmail($appId, $newStatus);
                $msg = 'status_updated';
            }
        }

        if (!empty($jobId)) {
            header("Location: view-applicants?job_id=" . urlencode((string)$jobId) . "&msg=status_updated");
            exit;
        }

        header("Location: dashboard?msg=status_updated");
        exit;
    }

    public function bulkUpdateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: dashboard");
            exit;
        }
        $this->validateCsrfToken();

        $appIds = $_POST['app_ids'] ?? [];
        $newStatus = $_POST['bulk_status'] ?? '';
        $jobId = $_POST['job_id'] ?? null;

        if (empty($appIds) || empty($newStatus)) {
            header("Location: view-applicants?job_id=" . urlencode((string)$jobId) . "&msg=no_selection");
            exit;
        }

        $validStatuses = ['Screened', 'Rejected', 'Passed', 'Failed'];
        if (!in_array($newStatus, $validStatuses)) {
            header("Location: view-applicants?job_id=" . urlencode((string)$jobId) . "&msg=invalid_status");
            exit;
        }

        foreach ($appIds as $appId) {
            $appId = (int)$appId;
            $currentApp = $this->appModel->getById($appId);
            if ($currentApp && $this->isValidTransition($currentApp['status'], $newStatus)) {
                $this->appModel->updateStatus($appId, $newStatus, $_SESSION['user_id'] ?? null);
                $jobTitle = $this->appModel->getJobById($currentApp['job_id'])['title'] ?? 'this position';
                $this->notificationModel->create(
                    $currentApp['user_id'],
                    'Application status updated',
                    "Your application for {$jobTitle} is now '{$newStatus}'."
                );
                $this->notificationService->sendStatusChangeEmail($appId, $newStatus);
            }
        }

        header("Location: view-applicants?job_id=" . urlencode((string)$jobId) . "&msg=bulk_updated");
        exit;
    }

    public function updateNotes() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: dashboard");
            exit;
        }
        $this->validateCsrfToken();

        $appId = (int)($_POST['app_id'] ?? 0);
        $notes = trim($_POST['notes'] ?? '');
        $jobId = $_POST['job_id'] ?? null;

        if (!$appId) {
            header("Location: view-applicants?job_id=" . urlencode((string)$jobId) . "&msg=invalid_app");
            exit;
        }

        $this->appModel->updateNotes($appId, $notes);

        header("Location: view-applicants?job_id=" . urlencode((string)$jobId) . "&msg=notes_updated");
        exit;
    }

public function processExam() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: manage-exams");
            exit;
        }
        $this->validateCsrfToken();

        $jobId = $_POST['job_id'] ?? null;
        $title = $_POST['title'] ?? '';
        $duration = (int)($_POST['duration'] ?? 0);
        $passMark = (int)($_POST['pass_mark'] ?? 0);

        try {
            $examId = $this->examModel->create($jobId, $title, $duration, $passMark);
            header("Location: edit-exam-questions?id=" . $examId);
            exit;
        } catch (PDOException $e) {
            header("Location: manage-exams?error=exists");
            exit;
        }
    }

public function processQuestion() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: manage-exams");
            exit;
        }
        $this->validateCsrfToken();

        $examId = $_POST['exam_id'] ?? null;
        $this->examModel->addQuestion(
            $examId,
            $_POST['question_text'] ?? '',
            $_POST['option_a'] ?? '',
            $_POST['option_b'] ?? '',
            $_POST['option_c'] ?? '',
            $_POST['option_d'] ?? '',
            $_POST['correct_answer'] ?? ''
        );

        header("Location: edit-exam-questions?id=" . urlencode((string)$examId));
        exit;
    }

    // Inside RecruiterController class
    public function manageExams() {
        $recruiter_id = $_SESSION['user_id']; // Securely get the logged-in user's ID
        
        // Fetch data filtered by this recruiter only
        $filteredExams = $this->examModel->getAllExamsWithJobs($recruiter_id);
        $availableJobs = $this->examModel->getAvailableJobsForExam($recruiter_id);
        
        // Define page-specific variables
        $pageTitle = "Manage Assessments";
        $script = "recruiter-dashboard.js";

        // NOW include the view. It will have access to $exams and $availableJobs
        include __DIR__ . '/../Views/recruiter/manage_exams.php';
    }

    public function updateExamSettings() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: manage-exams");
            exit;
        }
        $this->validateCsrfToken();

        $examId = $_POST['exam_id'] ?? null;
        $title = $_POST['title'] ?? '';
        $duration = (int)($_POST['duration_min'] ?? 0);
        $passMark = (int)($_POST['passing_mark'] ?? 0);

        $this->examModel->updateSettings($examId, $title, $duration, $passMark);
        header("Location: manage-exams?msg=updated");
        exit;
    }

    public function deleteQuestion() {
        $questionId = $_GET['id'] ?? null;
        $examId = $_GET['exam_id'] ?? null;

        if ($questionId && $examId) {
            $stmt = $this->db->prepare("DELETE FROM exam_answers WHERE question_id = :question_id");
            $stmt->execute([':question_id' => $questionId]);
            $this->examModel->deleteQuestion($questionId, $examId);
        }

        header("Location: edit-exam-questions?id=" . urlencode((string)$examId) . "&msg=deleted");
        exit;
    }

    public function deleteExam() {
        $examId = $_GET['id'] ?? null;
        if ($examId) {
            try {
                $this->examModel->deleteExam($examId);
                header("Location: manage-exams?msg=exam_deleted");
                exit;
            } catch (PDOException $e) {
                header("Location: manage-exams?error=delete_failed");
                exit;
            }
        }

        header("Location: manage-exams");
        exit;
    }
}


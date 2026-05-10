<?php

require_once __DIR__ . '/../Models/Job.php';
require_once __DIR__ . '/../Models/Application.php';
require_once __DIR__ . '/../Models/Skill.php';
require_once __DIR__ . '/../Models/UserProfile.php';
require_once __DIR__ . '/../Models/AuditModel.php';
require_once __DIR__ . '/../Models/Notification.php';

class CandidateController {
    private $db;
    private $jobModel;
    private $appModel;
    private $skillModel;
    private $userProfile;
    private $auditModel;
    private $notificationModel;

    public function __construct($db) {
        $this->db = $db;
        $this->jobModel = new Job($db);
        $this->appModel = new Application($db);
        $this->skillModel = new Skill($db);
        $this->userProfile = new UserProfile($db);
        $this->auditModel = new AuditModel($db);
        $this->notificationModel = new Notification($db);
    }

    public function portal() {
        $search = trim($_GET['search'] ?? '');
        $location = trim($_GET['location'] ?? '');
        $type = trim($_GET['type'] ?? '');
        $sort = $_GET['sort'] ?? 'newest';

        $jobs = $this->jobModel->getActiveJobsFiltered($search, $location, $type, $sort);
        $locations = $this->jobModel->getUniqueLocationsForActiveJobs();
        $types = $this->jobModel->getUniqueTypesForActiveJobs();
        $myApplications = $this->appModel->getCandidateApplications($_SESSION['user_id']);
        $notifications = $this->notificationModel->getForUser($_SESSION['user_id']);

        $interviewConfirmations = [];
        foreach ($myApplications as $app) {
            if ($app['status'] === 'Interviewing') {
                $latest = $this->auditModel->getLatestInterviewConfirmation($app['id']);
                if ($latest) {
                    $interviewConfirmations[$app['id']] = $latest['description'];
                }
            }
        }

        $pageTitle = 'Candidate Portal';
        $script = 'candidate-portal.js';

        include __DIR__ . '/../../templates/candidate/portal.php';
    }

    public function notificationsJson() {
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $notifications = $this->notificationModel->getForUser($_SESSION['user_id'], 10);
        $this->notificationModel->markAllRead($_SESSION['user_id']);
        echo json_encode(['notifications' => $notifications]);
    }

    public function notifications() {
        $this->notificationModel->markAllRead($_SESSION['user_id']);
        $notifications = $this->notificationModel->getForUser($_SESSION['user_id']);
        $pageTitle = 'Notifications';
        include __DIR__ . '/../../templates/candidate/notifications.php';
    }

    public function apply() {
        $job_id = (int)($_GET['id'] ?? 0);
        if (!$job_id) {
            header('Location: candidate/portal');
            exit;
        }

        $job = $this->jobModel->getJobById($job_id);
        if (!$job) {
            header('Location: candidate/portal');
            exit;
        }

        include __DIR__ . '/../../templates/candidate/apply.php';
    }

    public function profile() {
        $user_id = $_SESSION['user_id'];
        $user = $this->userProfile->getUserById($user_id);
        $skills = $this->skillModel->getAllSkills();
        $selectedSkillIds = $this->userProfile->getUserSkillIds($user_id);
        $pageTitle = 'My Profile';
        include __DIR__ . '/../../templates/candidate/profile.php';
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: profile');
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            header('Location: profile?error=csrf_invalid');
            exit;
        }

        $user_id = $_SESSION['user_id'];
        $bio = trim($_POST['bio'] ?? '');

        $skillIds = [];
        if (!empty($_POST['skills']) && is_array($_POST['skills'])) {
            foreach ($_POST['skills'] as $skillId) {
                $skillIds[] = (int)$skillId;
            }
        }

        $skillIds = array_filter(array_unique($skillIds), fn($value) => $value > 0);

        $this->userProfile->updateBio($user_id, $bio);
        $this->userProfile->saveUserSkills($user_id, $skillIds);

        header('Location: profile?msg=profile_saved');
        exit;
    }

    public function progress() {
        $myApplications = $this->appModel->getCandidateApplications($_SESSION['user_id']);
        $pageTitle = 'Application Progress';
        include __DIR__ . '/../../templates/candidate/progress.php';
    }

    public function confirmInterviewSlot() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: portal');
            exit;
        }

        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            header('Location: portal?error=csrf_invalid');
            exit;
        }

        $appId = (int)($_POST['app_id'] ?? 0);
        $slot = trim($_POST['slot'] ?? '');

        if (!$appId || empty($slot)) {
            header('Location: portal?error=invalid_slot');
            exit;
        }

        $application = $this->appModel->getById($appId);
        if (!$application || $application['user_id'] !== $_SESSION['user_id'] || $application['status'] !== 'Interviewing') {
            header('Location: portal?error=invalid_application');
            exit;
        }

        $this->auditModel->logAction(
            $_SESSION['user_id'], 
            'Interview Confirmed', 
            "Candidate confirmed interview for {$slot}", 
            $appId,
            $_SERVER['REMOTE_ADDR'] ?? null
        );

        $this->notificationModel->create(
            $_SESSION['user_id'],
            'Interview Confirmation',
            "Your interview slot has been confirmed for {$slot}."
        );

        header('Location: portal?msg=interview_confirmed&slot=' . urlencode($slot));
        exit;
    }
}

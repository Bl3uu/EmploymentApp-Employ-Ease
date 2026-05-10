<?php
// public/index.php

// --- SESSION COOKIE CONFIGURATION ---
// Ensure session cookie is only valid for browser session (not persistent)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('session.cookie_lifetime', 0);
ini_set('session.use_only_cookies', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
ini_set('session.cookie_samesite', 'Strict');
session_name('EMPLOY_EASE_PROD_SESSION');

date_default_timezone_set('UTC');
session_start();

// --- SESSION FIXATION PROTECTION ---
// Regenerate session ID on login to prevent hijacking
if (!isset($_SESSION['created_at'])) {
    session_regenerate_id(true);
    $_SESSION['created_at'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
}

// --- SESSION TIMEOUT SECURITY ---
$session_timeout = 900; // 15 minutes (900 seconds)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    session_unset();
    session_destroy();
    header("Location: login?error=session_expired");
    exit;
}

// --- SESSION BINDING VALIDATION ---
// Check if session is being used from different browser/IP
$current_ip = $_SERVER['REMOTE_ADDR'] ?? '';
$current_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Only validate if we have stored values (for backward compatibility)
if (isset($_SESSION['ip_address']) && isset($_SESSION['user_agent'])) {
    // For development, you might want to skip IP check
    // Uncomment the next line to enforce IP matching
    // if ($_SESSION['ip_address'] !== $current_ip) { ... }
    
    // Basic user agent check (can be relaxed for development)
    if (strlen($current_agent) > 0 && $_SESSION['user_agent'] !== $current_agent) {
        // Log potential session theft
        error_log("Session user agent mismatch - possible session theft attempt");
    }
}

if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}

// --- CSRF Token Generation ---
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Helper function to get CSRF token for forms ---
function csrf_token_field() {
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Models/UserAuth.php';
require_once __DIR__ . '/../src/Models/AuditModel.php';
require_once __DIR__ . '/../src/Controllers/AuthController.php';
require_once __DIR__ . '/../src/Controllers/RecruiterController.php';
require_once __DIR__ . '/../src/Controllers/AdminController.php';
require_once __DIR__ . '/../src/Controllers/CandidateController.php';

$database = new Database();
$db = $database->getConnection();
$authController = new AuthController($db);
$recruiterController = new RecruiterController($db);
$candidateController = new CandidateController($db);

// --- LOAD MODELS FOR ADMIN ---
require_once __DIR__ . '/../src/Models/Application.php';
require_once __DIR__ . '/../src/Models/ExamModel.php';

$appModel = new Application($db);
$auditModel = new AuditModel($db);
$examModel = new ExamModel($db);
$adminController = new AdminController($db, $appModel, $auditModel, $examModel);

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$scriptName = $_SERVER['SCRIPT_NAME'];
$baseDir = dirname($scriptName); 

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
define('BASE_URL', $protocol . $host . rtrim($baseDir, '/') . '/');

// Remove the base directory from the request URI
if ($baseDir !== '/' && strpos($requestUri, $baseDir) === 0) {
    $path = substr($requestUri, strlen($baseDir));
} else {
    $path = $requestUri;
}

// Clean up: Ensure path starts with / and remove trailing slashes
$path = '/' . ltrim($path, '/');
$path = ($path !== '/') ? rtrim($path, '/') : $path;

// --- 2. THE GATEKEEPER ---
// In public/index.php
$publicRoutes = [
    '/login', 
    '/login-submit', 
    '/signup', 
    '/signup-submit', 
    '/verify-otp',        // ADDED
    '/verify-otp-submit',  // ADDED
    '/setup-2fa'  // <--- ADD THIS LINE
];

// Check if user is fully logged in OR in the middle of a 2FA session
$is_authenticated = isset($_SESSION['user_id']);
$is_mid_2fa = isset($_SESSION['temp_2fa_user_id']);
$is_public_page = in_array($path, $publicRoutes);

// CRITICAL FIX: Add "&& $path !== '/login'" to ensure it never redirects to itself
if (!$is_authenticated && !$is_mid_2fa && !$is_public_page) {
    if ($path !== '/login' && $path !== '/') {
        header("Location: login"); 
        exit;
    }
}

// --- 3. ROUTING LOGIC ---
switch ($path) {
    case '/':
        if (!isset($_SESSION['user_id'])) {
            include __DIR__ . '/../templates/auth/login.php';
        } else {
            // Redirect based on role and admin status
            if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
                // Admin users go to admin dashboard
                header("Location: admin-dashboard");
            } else {
                // Regular recruiter or candidate
                match ((int)$_SESSION['role_id']) {
                    1 => header("Location: dashboard"),
                    2 => header("Location: portal"),
                    default => header("Location: logout"),
                };
            }
            exit;
        }
        break;

    case '/login':
        if (isset($_SESSION['user_id'])) {
            header("Location: ./"); 
            exit;
        }
        // Check if there is an error in the URL
        $error = $_GET['error'] ?? null;
        include __DIR__ . '/../templates/auth/login.php';
        break;

    case '/signup':
        $error = $_GET['error'] ?? null;
        include __DIR__ . '/../templates/auth/signup.php';
        break;

    case '/portal':
    case '/candidate/portal':
        if ($_SESSION['role_id'] != 2) { header("Location: ./"); exit; }
        $candidateController->portal();
        break;

    case '/notifications':
        if ($_SESSION['role_id'] != 2) { header("Location: ./"); exit; }
        $candidateController->notifications();
        break;

    case '/profile':
        if ($_SESSION['role_id'] != 2) { header("Location: ./"); exit; }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $candidateController->updateProfile();
        } else {
            $candidateController->profile();
        }
        break;

    case '/progress':
        if ($_SESSION['role_id'] != 2) { header("Location: ./"); exit; }
        $candidateController->progress();
        break;

    case '/confirm-interview':
        if ($_SESSION['role_id'] != 2) { header("Location: ./"); exit; }
        $candidateController->confirmInterviewSlot();
        break;

    case '/apply':
    case '/candidate/apply':
        if ($_SESSION['role_id'] != 2) { header("Location: ./"); exit; }
        $candidateController->apply();
        break;

    case '/submit-application':
        // 1. Security Check: Only candidates can apply
        if ($_SESSION['role_id'] != 2) { 
            header("Location: ./"); 
            exit; 
        }
        
        // 2. Load the Controller
        require_once __DIR__ . '/../src/Controllers/ApplicationController.php';
        
        // 3. Initialize and Execute
        $appController = new ApplicationController($db);
        $appController->handleSubmitApplication();
        break;

    // --- EXAM SYSTEM ROUTES ---

    case '/take-exam':
    case '/candidate/take-exam':
        if ($_SESSION['role_id'] != 2) { header("Location: ./"); exit; }
        include __DIR__ . '/../src/Views/candidate/take_exam.php';
        break;

    case '/log-violation':
        // This handles the background AJAX requests for cheating detection
        require_once __DIR__ . '/../src/Services/ProctorEngine.php';
        include __DIR__ . '/../API/log_violation.php';
        break;

    case '/submit-exam':
    case '/candidate/submit-exam':
        if ($_SESSION['role_id'] != 2) { header("Location: ./"); exit; }
        // We will build this handler next to calculate the score
        include __DIR__ . '/../src/Handler/submit_exam.php';
        break;

    case '/view-results':
    case '/candidate/view-results':
        if ($_SESSION['role_id'] != 2) { header("Location: ./"); exit; }
        include __DIR__ . '/../src/Views/candidate/results.php';
        break;

    // --- Recruiter ---
    case '/dashboard':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $script = "recruiter-dashboard.js";
        include __DIR__ . '/../src/Views/recruiter/dashboard.php';
        break;

    // --- RECRUITER JOB MANAGEMENT ---

    case '/post-job':
    case '/edit-job':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        
        require_once __DIR__ . '/../src/Models/Job.php';
        $jobModel = new Job($db);
        
        $pageTitle = ($path === '/edit-job') ? "Edit Job" : "Post New Job";
        // The view will handle fetching $jobData if ?id= is present
        include __DIR__ . '/../src/Views/recruiter/post_job.php'; 
        break;

    case '/process-job':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $recruiterController->processJob();
        break;

    case '/assign-exam':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $recruiterController->assignExam();
        break;

    // --- PROCTORING REPORT ---
    case '/view-report':
        // 1. Security: Only recruiters can see violation reports
        if ($_SESSION['role_id'] != 1) { 
            header("Location: ./"); 
            exit; 
        }

        // 2. Data Check: Ensure an ID is actually provided
        $app_id = $_GET['id'] ?? null;
        $script = "recruiter-dashboard.js";
        if (!$app_id) {
            header("Location: dashboard");
            exit;
        }

        $pageTitle = "Proctoring Report";
        // Pointing to where we saved the report UI
        include __DIR__ . '/../src/Views/recruiter/view_report.php'; 
        break;

    case '/review-exam':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $app_id = $_GET['id'] ?? null;
        if (!$app_id) { header("Location: dashboard"); exit; }
        
        $pageTitle = "Review Exam Answers";
        $script = "recruiter-dashboard.js";
        include __DIR__ . '/../src/Views/recruiter/review_exam.php'; 
        break;

    // Inside index.php switch statement
    case '/manage-exams':
        if ($_SESSION['role_id'] != 1) { 
            header("Location: ./"); 
            exit; 
        }
        // Call the controller method we just created
        $recruiterController->manageExams(); 
        break;

    case '/process-exam':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $recruiterController->processExam();
        break;

    case '/edit-exam-questions':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $exam_id = $_GET['id'] ?? null;
        if (!$exam_id) { header("Location: manage-exams"); exit; }
        
        $pageTitle = "Edit Questions";
        $script = "recruiter-dashboard.js";
        include __DIR__ . '/../src/Views/recruiter/edit_exam_questions.php';
        break;

    case '/process-question':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $recruiterController->processQuestion();
        break;

    case '/edit-exam-settings':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $exam_id = $_GET['id'] ?? null;
        if (!$exam_id) { header("Location: manage-exams"); exit; }
        
        $pageTitle = "Exam Settings";
        include __DIR__ . '/../src/Views/recruiter/edit_exam_settings.php';
        break;

    case '/update-exam-settings':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $recruiterController->updateExamSettings();
        break;

    case '/delete-question':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $recruiterController->deleteQuestion();
        break;

    case '/delete-exam':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $recruiterController->deleteExam();
        break;

    case '/view-applicants':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $job_id = $_GET['job_id'] ?? null;
        $pageTitle = "Review Applicants";
        include __DIR__ . '/../src/Views/recruiter/applicants.php';
        break;

    case '/update-application-status':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $recruiterController->updateApplicationStatus();
        break;

    case '/bulk-update-status':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $recruiterController->bulkUpdateStatus();
        break;

    case '/update-notes':
        if ($_SESSION['role_id'] != 1) { header("Location: ./"); exit; }
        $recruiterController->updateNotes();
        break;

    case '/view-resume':
        // Only recruiters or the owner should see this
        if (!isset($_SESSION['user_id'])) { header("Location: ./"); exit; }
        include __DIR__ . '/../src/Handler/view_resume.php';
        break;

    case '/login-submit':
        $authController->handleLogin();
        break;

    case '/signup-submit':
        $authController->handleSignup();
        break;

    case '/verify-otp':
        // Ensure the user actually has a pending 2FA session
        if (!isset($_SESSION['temp_2fa_user_id'])) {
            header("Location: login");
            exit;
        }
        $error = $_GET['error'] ?? null;
        include __DIR__ . '/../templates/auth/verify-otp.php';
        break;

    case '/verify-otp-submit':
        $authController->handleVerify2FA();
        break;

    case '/setup-2fa':
        if (!isset($_SESSION['temp_2fa_user_id'])) { 
            header("Location: login"); 
            exit; 
        }
        // Fetch the QR URL and Secret from the controller
        $qrData = $authController->getQRData(); 
        include __DIR__ . '/../templates/auth/setup-2fa.php';
        break;

    case '/logout':
        $authController->handleLogout();
        break;

    // --- ADMIN DASHBOARD ROUTES ---
    case '/admin-dashboard':
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { header("Location: ./"); exit; }
        $adminController->dashboard();
        break;

    case '/view-application-profile':
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { header("Location: ./"); exit; }
        $adminController->viewApplicationProfile();
        break;

    case '/generate-audit-report':
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { header("Location: ./"); exit; }
        $adminController->generateAuditReport();
        break;

    case '/admin-manage-jobs':
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { header("Location: ./"); exit; }
        $adminController->manageJobs();
        break;

    case '/admin-manage-exams':
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { header("Location: ./"); exit; }
        $adminController->manageExams();
        break;

    case '/admin-manage-users':
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { header("Location: ./"); exit; }
        $adminController->userManagement();
        break;

    case '/admin-change-role':
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { header("Location: ./"); exit; }
        $adminController->changeUserRole();
        break;

    case '/admin-skill-tags':
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { header("Location: ./"); exit; }
        $adminController->skillManagement();
        break;

    case '/admin-skill-action':
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { header("Location: ./"); exit; }
        $adminController->handleSkillAction();
        break;

    case '/admin-audit-feed':
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { header("Location: ./"); exit; }
        $adminController->auditFeed();
        break;

    case '/admin-settings':
        if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) { header("Location: ./"); exit; }
        $adminController->settings();
        break;

    default:
        http_response_code(404);
        echo "<h1>404 - Not Found</h1>";
        echo "<p>The path <b>$path</b> does not exist.</p>";
        break;
}
<?php
if (session_status() == PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../Database.php';
require_once __DIR__ . '/../../Models/UserAuth.php';
require_once __DIR__ . '/../../Models/Job.php';
require_once __DIR__ . '/../../Models/Application.php';

$database = new Database();
$db = $database->getConnection();
$auth = new UserAuth($db);

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header("Location: login");
    exit;
}
$auth->checkSessionTimeout(900);

$jobModel = new Job($db);
$appModel = new Application($db);

$recruiterId = $_SESSION['user_id'];
$recruiterName = htmlspecialchars(($_SESSION['first_name'] ?? 'Recruiter') . ' ' . ($_SESSION['last_name'] ?? ''));

// Get filter and search parameters
$search = $_GET['search'] ?? '';
$location = $_GET['location'] ?? '';
$type = $_GET['type'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Get filtered jobs
$myJobs = $jobModel->getJobsByRecruiterFiltered($recruiterId, $search, $location, $type, $status, $sort);

// Get filter options for dropdowns
$availableLocations = $jobModel->getUniqueLocations($recruiterId);
$availableTypes = $jobModel->getUniqueTypes($recruiterId);

$totalApplicants = $appModel->getTotalCountForRecruiter($recruiterId);
$recentApplicants = $appModel->getRecentApplicantsForRecruiter($recruiterId);

$activeJobCount = is_array($myJobs) ? count($myJobs) : 0;
$aiScreenedCount = 0;
if (is_array($recentApplicants)) {
    foreach ($recentApplicants as $app) {
        if (($app['ai_score'] ?? 0) > 0) $aiScreenedCount++;
    }
}

$topMatch = null;
if (!empty($recentApplicants)) {
    $tempApps = $recentApplicants;
    usort($tempApps, fn($a, $b) => $b['ai_score'] <=> $a['ai_score']);
    $topMatch = $tempApps[0];
}

include __DIR__ . '/../../../templates/recruiter/dashboard.php';


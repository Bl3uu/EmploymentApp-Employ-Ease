<?php
require_once __DIR__ . '/../../Models/Application.php';
require_once __DIR__ . '/../../Models/Job.php';
require_once __DIR__ . '/../../Models/AuditModel.php';

$appModel = new Application($db);
$jobModel = new Job($db);
$auditModel = new AuditModel($db);

$job_id = intval($_GET['job_id']);
$job = $jobModel->getJobById($job_id);
$applicants = $appModel->getApplicantsByJob($job_id);

// Add violation flags
foreach ($applicants as &$app) {
    $app['has_violations'] = $auditModel->hasExamViolations($app['app_id']);
}

include __DIR__ . '/../../../templates/recruiter/job_applicants_list.php';


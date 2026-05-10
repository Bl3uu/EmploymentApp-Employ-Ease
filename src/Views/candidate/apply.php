<?php
require_once __DIR__ . '/../../Models/Job.php';

$jobModel = new Job($db);
$jobId = $_GET['id'] ?? null;
$job = $jobId ? $jobModel->getJobById($jobId) : null;

if (!$job) {
    header("Location: portal");
    exit;
}

$pageTitle = "Apply for " . $job['title'];
$script = "candidate-portal.js";

include __DIR__ . '/../../../templates/candidate/apply.php';


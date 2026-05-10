<?php
require_once __DIR__ . '/../../Models/Job.php';
require_once __DIR__ . '/../../Models/Application.php';

$jobModel = new Job($db);
$appModel = new Application($db);

$jobs = $jobModel->getAllJobs();
$myApplications = $appModel->getCandidateApplications($_SESSION['user_id']);

$pageTitle = "Candidate Portal";
$script = "candidate-portal.js";

include __DIR__ . '/../../../templates/candidate/portal.php';


<?php
$jobData = null;
if (isset($_GET['id'])) {
    $jobData = $jobModel->getJobById($_GET['id']);
}

include __DIR__ . '/../../../templates/recruiter/post_job_form.php';


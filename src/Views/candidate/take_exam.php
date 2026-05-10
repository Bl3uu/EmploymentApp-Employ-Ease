<?php
require_once __DIR__ . '/../../Services/ExamHandler.php';

$examHandler = new ExamHandler($db);
$app_id = $_GET['app_id'] ?? null;
$examData = $examHandler->getExamByApplication($app_id);

if (!$examData) {
    header("Location: portal?error=exam_not_ready");
    exit;
}

$pageTitle = "Technical Assessment";

include __DIR__ . '/../../../templates/candidate/take_exam_form.php';


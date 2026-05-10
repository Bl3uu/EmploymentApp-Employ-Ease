<?php
require_once __DIR__ . '/../../Models/Application.php';

$appModel = new Application($db);
$app_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$candidate = $appModel->getExamReviewSummary($app_id);
$results = $appModel->getExamAnswers($app_id);

include __DIR__ . '/../../../templates/recruiter/review_exam_form.php';


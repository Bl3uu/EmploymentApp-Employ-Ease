<?php
require_once __DIR__ . '/../../Models/ExamModel.php';

$examModel = new ExamModel($db);
$exam_id = intval($_GET['id']);

$exam = $examModel->getExamWithJob($exam_id);
if (!$exam) {
    header("Location: manage-exams");
    exit;
}

include __DIR__ . '/../../../templates/recruiter/edit_exam_settings_form.php';


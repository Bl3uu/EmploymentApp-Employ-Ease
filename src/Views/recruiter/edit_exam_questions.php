<?php
require_once __DIR__ . '/../../Models/ExamModel.php';

$examModel = new ExamModel($db);
$exam_id = intval($_GET['id']);

$exam = $examModel->getExamWithJob($exam_id);
if (!$exam) { die("Exam not found."); }

$questions = $examModel->getAllQuestions($exam_id);

include __DIR__ . '/../../../templates/recruiter/edit_questions_form.php';


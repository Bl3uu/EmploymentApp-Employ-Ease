<?php
// src/Handler/submit_exam.php

require_once __DIR__ . '/../Database.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: portal");
    exit;
}

$app_id = $_POST['app_id'] ?? null;
$user_id = $_SESSION['user_id'];

// --- VALIDATION: Check if candidate is allowed to take exam ---
$checkStmt = $db->prepare("SELECT status FROM applications WHERE id = ? AND user_id = ?");
$checkStmt->execute([$app_id, $user_id]);
$currentStatus = $checkStmt->fetchColumn();

$allowedStatuses = ['Exam Assigned', 'Screened', 'Failed'];
if (!in_array($currentStatus, $allowedStatuses)) {
    header("Location: portal?error=exam_not_allowed");
    exit;
}

$isDisqualified = ($_POST['disqualified'] ?? '0') === '1'; 

require_once __DIR__ . '/../Services/ExamHandler.php';
require_once __DIR__ . '/../Models/Notification.php';
$examHandler = new ExamHandler($db);
$examData = $examHandler->getExamByApplication($app_id);
$notificationModel = new Notification($db);

$score = 0;
$newStatus = 'Exam Completed';
$resultStatus = 'Failed';

// Prepare the insert statement for the individual answers
$insertAns = $db->prepare("INSERT INTO exam_answers (application_id, question_id, selected_option, is_correct) VALUES (?, ?, ?, ?)");

if ($isDisqualified) {
    $score = 0;
    $resultStatus = 'Flagged';
} else {
    $totalQuestions = count($examData['questions']);
    $correctCount = 0;

    foreach ($examData['questions'] as $q) {
        // Match the naming convention from your form: name="q<?php echo $q['id'];"
        $submittedAnswer = $_POST['q' . $q['id']] ?? '';
        
        // Fetch the correct answer
        $stmt = $db->prepare("SELECT correct_answer FROM questions WHERE id = ?");
        $stmt->execute([$q['id']]);
        $actual = $stmt->fetchColumn();

        $isCorrect = ($submittedAnswer === $actual) ? 1 : 0;
        if ($isCorrect) {
            $correctCount++;
        }

        // Save the candidate answer
        $insertAns->execute([$app_id, $q['id'], $submittedAnswer, $isCorrect]);
    }
    
    $score = ($totalQuestions > 0) ? round(($correctCount / $totalQuestions) * 100) : 0;
    $resultStatus = ($score >= ($examData['passing_mark'] ?? 70)) ? 'Passed' : 'Failed';
}

// 1. Update the application status only; do not overwrite ai_score
$stmt = $db->prepare("UPDATE applications SET status = :status WHERE id = :app_id");
$stmt->execute([
    ':status' => $newStatus,
    ':app_id' => $app_id
]);

// 2. Save result to exam_results table
$resStmt = $db->prepare("INSERT INTO exam_results (user_id, exam_id, score, status) VALUES (?, ?, ?, ?)");
$resStmt->execute([$user_id, $examData['id'], $score, $resultStatus]);

// 3. Create an internal notification for the candidate
$jobStmt = $db->prepare("SELECT j.title FROM applications a JOIN jobs j ON a.job_id = j.id WHERE a.id = ? LIMIT 1");
$jobStmt->execute([$app_id]);
$jobTitle = $jobStmt->fetchColumn() ?: 'your application';
$message = $isDisqualified
    ? "Your exam for {$jobTitle} was flagged for review due to security events."
    : "Your exam for {$jobTitle} has been completed with status {$resultStatus} and score {$score}%.";
$notificationModel->create($user_id, 'Exam Completed', $message);

// Redirect back to portal
$msg = $isDisqualified ? 'voided' : 'exam_completed';
header("Location: portal?msg=$msg&score=$score");
exit;
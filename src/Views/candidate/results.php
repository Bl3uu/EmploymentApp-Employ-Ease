<?php
$user_id = $_SESSION['user_id'];
$exam_id = $_GET['exam_id'] ?? null;

$query = "SELECT er.*, e.title, e.passing_mark, a.ai_score
          FROM exam_results er
          JOIN exams e ON er.exam_id = e.id
          JOIN applications a ON a.user_id = er.user_id AND a.job_id = e.job_id
          WHERE er.user_id = ? AND er.exam_id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$user_id, $exam_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    header("Location: portal");
    exit;
}

$hasPassed = $result['score'] >= $result['passing_mark'];
$pageTitle = "My Results";

include __DIR__ . '/../../../templates/candidate/view_results.php';


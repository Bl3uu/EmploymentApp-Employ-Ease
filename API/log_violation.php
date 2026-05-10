<?php
// public/api/log-violation.php
header('Content-Type: application/json');

// session_start() is already called in index.php, so we don't need it here

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorised']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // We already have $db from index.php
    $proctor = new ProctorEngine($db);

    $app_id = isset($_POST['application_id']) ? (int)$_POST['application_id'] : 0;
    $type   = isset($_POST['type']) ? $_POST['type'] : 'Unknown';
    $user_id = $_SESSION['user_id'];

    if ($app_id > 0) {
        $isAI = in_array($type, ['Face Missing', 'Multiple Faces', 'Face Missing (Persistent)']);
        $details = $isAI 
            ? "AI Vision detection via Python backend service." 
            : "Browser event detection (Tab/Window focus API).";
            
        $success = $proctor->logViolation($app_id, $user_id, $type, $details);

        $isFlagged = $proctor->isCandidateFlagged($app_id);

        echo json_encode([
            'success' => $success,
            'flagged' => $isFlagged,
            'message' => $isFlagged ? 'Limit exceeded' : 'Violation logged'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    }
}
exit; // Prevent index.php from continuing
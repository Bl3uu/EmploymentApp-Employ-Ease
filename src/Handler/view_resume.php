<?php
// SECURE VIEW RESUME - Only allow authorized access

// 1. Require user to be logged in
if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access.");
}

// 2. Get the requested filename
$filename = $_GET['path'] ?? null;

if (!$filename) {
    die("No file specified.");
}

// 3. Validate filename format (prevent path traversal)
$filename = basename($filename);
if (empty($filename) || strpos($filename, '..') !== false) {
    die("Invalid filename.");
}

// 4. Only allow PDF files
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if ($extension !== 'pdf') {
    die("Only PDF files are allowed.");
}

// 5. Define your storage path
$filePath = __DIR__ . '/../../public/assets/uploads/resumes/' . $filename;

if (file_exists($filePath) && is_readable($filePath)) {
    // 6. Verify ownership for candidates (optional - if they should only see their own resume)
    // recruiters (role_id=1) can see all resumes
    
    // 7. Set secure headers
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    header('X-Content-Type-Options: nosniff');
    
    // 8. Serve the file
    readfile($filePath);
    exit;
} else {
    http_response_code(404);
    echo "File not found.";
}

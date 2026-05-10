<?php
// src/Controllers/ExamController.php

require_once __DIR__ . '/../Services/ExamHandler.php';
require_once __DIR__ . '/../Models/Application.php';

class ExamController {
    private $db;
    private $examHandler;
    private $appModel;

    public function __construct($db) {
        $this->db = $db;
        $this->examHandler = new ExamHandler($this->db);
        $this->appModel = new Application($this->db);
    }

    /**
     * Display the exam page to the candidate
     */
    public function showExam($job_id) {
        if (session_status() == PHP_SESSION_NONE) session_start();
        $user_id = $_SESSION['user_id'];

        // 1. Check if user already applied and get application_id
        if (!$this->appModel->hasApplied($user_id, $job_id)) {
            header("Location: /jobs?error=must_apply_first");
            exit;
        }

        // 2. Get application and check if allowed to take exam
        $appId = $this->appModel->getApplicationId($user_id, $job_id);
        $application = $this->appModel->getById($appId);
        $currentStatus = $application['status'] ?? '';

        // Only allow exam if status is 'Exam Assigned' or 'Screened' or 'Failed' (retake)
        $allowedStatuses = ['Exam Assigned', 'Screened', 'Failed'];
        if (!in_array($currentStatus, $allowedStatuses)) {
            header("Location: portal?error=cannot_take_exam");
            exit;
        }

        // 3. Fetch Exam Data
        $questions = $this->examHandler->getExamData($job_id);
        
        // This is the ID the Proctoring JavaScript needs!
        $current_application_id = $appId;

        // 4. Load the View
        require_once __DIR__ . '/../../views/candidate/take_exam.php';
    }

    /**
     * Handle the form submission when the candidate finishes
     */
    public function submitExam() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'];
            $exam_id = $_POST['exam_id'];
            $app_id  = $_POST['application_id'];
            $answers = $_POST['answers']; // Array of q_id => choice

            $result = $this->examHandler->calculateResult($user_id, $exam_id, $app_id, $answers);

            // Update application status to 'Screened'
            $this->appModel->updateStatus($app_id, 'Screened');

            header("Location: /candidate/results?status=" . $result['status']);
            exit;
        }
    }
}
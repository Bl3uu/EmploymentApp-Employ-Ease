<?php
// src/Controllers/ApplicationController.php

require_once __DIR__ . '/../Services/FileUploader.php';
require_once __DIR__ . '/../Services/NLPProcessor.php';
require_once __DIR__ . '/../Models/Job.php';
require_once __DIR__ . '/../Models/Application.php';
require_once __DIR__ . '/../Models/Skill.php';

class ApplicationController {
    private $db;
    private $applicationModel;
    private $jobModel;
    private $skillModel;
    private $uploader;
    private $nlp;

    public function __construct($db) {
        $this->db = $db;
        $this->applicationModel = new Application($this->db);
        $this->jobModel = new Job($this->db);
        $this->skillModel = new Skill($this->db);
        $this->uploader = new FileUploader();
        $this->nlp = new NLPProcessor();
    }

    public function handleSubmitApplication() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF Validation
            $token = $_POST['csrf_token'] ?? '';
            if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                error_log("CSRF validation failed for user: " . ($_SESSION['user_id'] ?? 'unknown'));
                header("Location: portal?error=csrf_invalid");
                exit;
            }

            $job_id = (int)$_POST['job_id']; // Keep this outside to use in the catch block
            
            try {
                $user_id = $_SESSION['user_id'];
                
                // 1. Check if already applied (Prevent duplicate entries)
                if ($this->applicationModel->hasApplied($user_id, $job_id)) {
                    throw new Exception("You have already applied for this position.");
                }

                // 2. File Upload Path (Physical path for move_uploaded_file)
                // This points to Employment_APP/public/assets/uploads/resumes/
                $targetFolder = __DIR__ . "/../../public/assets/uploads/resumes/";
                $secureFileName = $this->uploader->upload($_FILES['resume'], $targetFolder);

                // --- START AI PIPELINE ---
                
                // 3. Get Job Description for comparison
                $jobData = $this->jobModel->getJobById($job_id);
                $jobDesc = $jobData['description'] ?? '';

                // 4. Trigger NLP Engine (Using Absolute Path for the resume file)
                $absoluteFilePath = realpath($targetFolder . $secureFileName);
                $aiData = $this->nlp->calculateMatchScore($absoluteFilePath, $jobDesc);

                // --- END AI PIPELINE ---

                // 5. Save to Database
                $appId = $this->applicationModel->applyForJob($user_id, $job_id, $secureFileName);
                $this->applicationModel->updateScoreAndSummary($appId, $aiData['score'], $aiData['summary']);

                // 6. No application-specific skills are collected during apply anymore.

                // 7. Success Redirect (Matches case '/portal' in index.php)
                header("Location: portal?status=applied");
                exit;

            } catch (Exception $e) {
                // Error Redirect (Matches case '/apply' in index.php)
                header("Location: apply?id=$job_id&error=" . urlencode($e->getMessage()));
                exit;
            }
        }
    }
}
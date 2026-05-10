<?php
class Application {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // --- CANDIDATE METHODS ---

    /**
     * Creates a new application record when a candidate clicks 'Apply'
     */
    public function applyForJob($user_id, $job_id, $resume_path) {
        $query = "INSERT INTO applications (job_id, user_id, resume_path, status) 
                  VALUES (:job_id, :user_id, :resume_path, 'Applied')";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':job_id'      => $job_id,
            ':user_id'     => $user_id,
            ':resume_path' => $resume_path
        ]);
        
        return $this->db->lastInsertId(); // Return the ID so we can update it with a score later
    }

    /**
     * Updates the AI score and the AI-generated summary for a specific application.
     */
    public function updateScoreAndSummary($application_id, $score, $summary) {
        $query = "UPDATE applications SET ai_score = :score, ai_summary = :summary WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':score'   => $score,
            ':summary' => $summary,
            ':id'      => $application_id
        ]);
    }

    /**
     * Check if a candidate has already applied to this job (to prevent duplicates)
     */
    public function hasApplied($user_id, $job_id) {
        $query = "SELECT id FROM applications WHERE user_id = :user_id AND job_id = :job_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id, ':job_id' => $job_id]);
        return $stmt->fetch() ? true : false;
    }

    // --- EXISTING RECRUITER METHODS ---
    
    public function getTotalCountForRecruiter($recruiter_id) {
        $query = "SELECT COUNT(a.id) as total 
                  FROM applications a 
                  JOIN jobs j ON a.job_id = j.id 
                  WHERE j.recruiter_id = :recruiter_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':recruiter_id', $recruiter_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }

    /**
     * Updates the current status of an application (e.g., 'Applied' to 'Screened')
     */
    public function updateStatus($id, $status, $user_id = null) {
        try {
            $this->db->beginTransaction();

            // 1. Update status
            $query = "UPDATE applications SET status = :status WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':status' => $status, ':id' => $id]);

            // 2. Automatically create Audit Log
            $logQuery = "INSERT INTO audit_logs (application_id, user_id, action, description) 
                        VALUES (:app_id, :user_id, 'Status Update', :desc)";
            $logStmt = $this->db->prepare($logQuery);
            $logStmt->execute([
                ':app_id'  => $id,
                ':user_id' => $user_id,
                ':desc'    => "Changed status to $status"
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Fetches the application ID for a specific user and job
     */
    public function getApplicationId($user_id, $job_id) {
        $query = "SELECT id FROM applications WHERE user_id = :user_id AND job_id = :job_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id, ':job_id' => $job_id]);
        $row = $stmt->fetch();
        return $row['id'] ?? null;
    }

    /**
     * Fetches all applications for a specific candidate with job details and associated exam ID
     */
    public function getCandidateApplications($user_id) {
        // We add e.id AS exam_id and LEFT JOIN the exams table
        $query = "SELECT a.*, j.title, j.company, j.location, e.id AS exam_id 
                FROM applications a 
                JOIN jobs j ON a.job_id = j.id 
                LEFT JOIN exams e ON j.id = e.job_id
                WHERE a.user_id = :user_id 
                ORDER BY a.applied_at DESC";
        
        $stmt = $this->db->prepare($query);
        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Add this to your Application class in src/Application.class.php

    /**
     * Fetches the most recent applicants across all jobs posted by a specific recruiter.
     * Sorted by AI Score descending so the best candidates appear first.
     */
    public function getRecentApplicantsForRecruiter($recruiter_id, $limit = 10) {
        $query = "SELECT a.*, u.first_name, u.last_name, j.title as job_title, e.id AS exam_id
                FROM applications a 
                JOIN users u ON a.user_id = u.id 
                JOIN jobs j ON a.job_id = j.id 
                LEFT JOIN exams e ON e.job_id = j.id
                WHERE j.recruiter_id = :recruiter_id 
                ORDER BY a.ai_score DESC, a.applied_at DESC 
                LIMIT :limit";
                
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':recruiter_id', $recruiter_id, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Updates the application status to 'Exam Assigned'
     */
    public function assignExam($application_id) {
        $query = "UPDATE applications SET status = 'Exam Assigned' WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':id' => $application_id]);
    }

    public function getJobIdByApplication($application_id) {
        $query = "SELECT job_id FROM applications WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $application_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['job_id'] ?? null;
    }

    // --- NEW: Recruiter View Methods ---

    /**
     * Get single application by ID
     */
    public function getById($id) {
        $query = "SELECT * FROM applications WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Clear exam data when reassigning exam (prevents duplicate entries and violation accumulation)
     */
    public function clearExamData($application_id) {
        // Get user_id and exam_id for this application
        $app = $this->getById($application_id);
        if (!$app) return false;

        $user_id = $app['user_id'];
        
        // Get exam_id from job
        $jobStmt = $this->db->prepare("SELECT id FROM exams WHERE job_id = ?");
        $jobStmt->execute([$app['job_id']]);
        $exam = $jobStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($exam) {
            $exam_id = $exam['id'];
            
            // Delete previous exam answers
            $delAns = $this->db->prepare("DELETE FROM exam_answers WHERE application_id = ?");
            $delAns->execute([$application_id]);
            
            // Delete previous exam results
            $delRes = $this->db->prepare("DELETE FROM exam_results WHERE user_id = ? AND exam_id = ?");
            $delRes->execute([$user_id, $exam_id]);
        }
    }

    /**
     * Get job details by ID (for applicants view)
     */
    public function getJobById($job_id) {
        $query = "SELECT title FROM jobs WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$job_id]);
        return $stmt->fetch();
    }

    /**
     * Get all applicants for a job with exam and result data
     */
    public function getApplicantsByJob($job_id) {
        $query = "SELECT
            u.first_name, u.last_name, u.email, u.id as user_id,
            a.id as app_id, a.ai_score, a.ai_summary, a.recruiter_notes,
            a.status, a.resume_path,
            e.id AS exam_id,
            er.score as exam_score, er.status as exam_status
          FROM applications a
          JOIN users u ON a.user_id = u.id
          LEFT JOIN exams e ON a.job_id = e.job_id
          LEFT JOIN exam_results er ON (er.user_id = u.id AND er.exam_id = e.id)
          WHERE a.job_id = ?
          GROUP BY u.first_name, u.last_name, u.email, u.id,
                   a.id, a.ai_score, a.ai_summary, a.recruiter_notes, a.status, a.resume_path,
                   e.id, er.score, er.status
          ORDER BY a.ai_score DESC, er.score DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$job_id]);
        $applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add profile skills for each applicant
        foreach ($applicants as &$app) {
            $profileSkillsStmt = $this->db->prepare("
                SELECT GROUP_CONCAT(DISTINCT s.name ORDER BY s.name SEPARATOR ', ') as profile_skills
                FROM user_skills us
                JOIN skills s ON us.skill_id = s.id
                WHERE us.user_id = ?
            ");
            $profileSkillsStmt->execute([$app['user_id']]);
            $result = $profileSkillsStmt->fetch(PDO::FETCH_ASSOC);
            $app['profile_skills'] = $result['profile_skills'] ?? null;
        }
        
        return $applicants;
    }

    /**
     * Get summary info for exam review
     */
    public function getExamReviewSummary($application_id) {
        $query = "SELECT u.first_name, u.last_name, e.title as exam_title, a.status, er.score
                  FROM applications a
                  JOIN users u ON a.user_id = u.id
                  LEFT JOIN exams e ON a.job_id = e.job_id
                  LEFT JOIN exam_results er ON (er.user_id = u.id AND er.exam_id = e.id)
                  WHERE a.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$application_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get applicant's exam answers for review
     */
    public function getExamAnswers($application_id) {
        $query = "SELECT q.question_text, q.option_a, q.option_b, q.option_c, q.option_d,
                        q.correct_answer as correct_option, ans.selected_option
                  FROM exam_answers ans
                  JOIN questions q ON ans.question_id = q.id
                  WHERE ans.application_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$application_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get report data for all applicants of recruiter's jobs
     */
    public function getRecruiterReportData($recruiter_id) {
        $query = "SELECT u.first_name, u.last_name, e.title as exam_title, a.status, a.ai_score
                  FROM applications a
                  JOIN users u ON a.user_id = u.id
                  JOIN jobs j ON a.job_id = j.id
                  LEFT JOIN exams e ON j.id = e.job_id
                  WHERE j.recruiter_id = ?
                  ORDER BY a.applied_at DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$recruiter_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get candidate info for report view
     */
    public function getCandidateForReport($application_id) {
        $query = "SELECT u.first_name, u.last_name, e.title as exam_title, a.status, a.ai_score,
                         er.score as exam_score, er.status as exam_result_status
                  FROM applications a
                  JOIN users u ON a.user_id = u.id
                  LEFT JOIN exams e ON a.job_id = e.job_id
                  LEFT JOIN exam_results er ON er.user_id = a.user_id AND er.exam_id = e.id
                  WHERE a.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$application_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateNotes($application_id, $notes) {
        $query = "UPDATE applications SET recruiter_notes = :notes WHERE id = :id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([':notes' => $notes, ':id' => $application_id]);
    }
}
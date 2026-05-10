<?php

class ExamModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($job_id, $title, $duration, $passing_mark) {
        $query = "INSERT INTO exams (job_id, title, duration_min, passing_mark)
                  VALUES (:job_id, :title, :duration, :passing_mark)";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':job_id' => $job_id,
            ':title' => $title,
            ':duration' => (int)$duration,
            ':passing_mark' => (int)$passing_mark
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function getByJobId($job_id) {
        $query = "SELECT * FROM exams WHERE job_id = :job_id LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':job_id' => $job_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getByApplicationId($application_id) {
        $query = "SELECT e.*
                  FROM exams e
                  JOIN applications a ON a.job_id = e.job_id
                  WHERE a.id = :application_id
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':application_id' => $application_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getQuestions($exam_id) {
        $query = "SELECT id, question_text, option_a, option_b, option_c, option_d
                  FROM questions
                  WHERE exam_id = :exam_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':exam_id' => $exam_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addQuestion($exam_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_answer) {
        $query = "INSERT INTO questions
                  (exam_id, question_text, option_a, option_b, option_c, option_d, correct_answer)
                  VALUES
                  (:exam_id, :question_text, :option_a, :option_b, :option_c, :option_d, :correct_answer)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':exam_id' => $exam_id,
            ':question_text' => $question_text,
            ':option_a' => $option_a,
            ':option_b' => $option_b,
            ':option_c' => $option_c,
            ':option_d' => $option_d,
            ':correct_answer' => $correct_answer
        ]);
    }

    public function updateSettings($exam_id, $title, $duration_min, $passing_mark) {
        $query = "UPDATE exams
                  SET title = :title, duration_min = :duration_min, passing_mark = :passing_mark
                  WHERE id = :exam_id";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':exam_id' => $exam_id,
            ':title' => $title,
            ':duration_min' => (int)$duration_min,
            ':passing_mark' => (int)$passing_mark
        ]);
    }

    public function deleteExam($exam_id) {
        $stmt = $this->db->prepare("DELETE FROM exams WHERE id = :exam_id");
        return $stmt->execute([':exam_id' => $exam_id]);
    }

    public function deleteQuestion($question_id, $exam_id) {
        $stmt = $this->db->prepare("DELETE FROM questions WHERE id = :question_id AND exam_id = :exam_id");
        return $stmt->execute([
            ':question_id' => $question_id,
            ':exam_id' => $exam_id
        ]);
    }

    public function saveExamResult($user_id, $exam_id, $score, $status) {
        $query = "INSERT INTO exam_results (user_id, exam_id, score, status)
                  VALUES (:user_id, :exam_id, :score, :status)";
        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            ':user_id' => $user_id,
            ':exam_id' => $exam_id,
            ':score' => (int)$score,
            ':status' => $status
        ]);
    }

    // --- NEW: Recruiter View Methods ---

    /**
     * Get all exams with job titles (for manage_exams view)
     */
    public function getAllExamsWithJobs($recruiter_id) {
        // We join with jobs to check who owns the job the exam belongs to
        $query = "SELECT e.*, j.title as job_title
                FROM exams e
                JOIN jobs j ON e.job_id = j.id
                WHERE j.recruiter_id = :recruiter_id
                ORDER BY e.id DESC";
                
        $stmt = $this->db->prepare($query);
        $stmt->execute([':recruiter_id' => $recruiter_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get jobs that don't have an exam yet (for creating new exams)
     */
    public function getAvailableJobsForExam($recruiter_id = null) {
        $query = "SELECT id, title FROM jobs
             WHERE id NOT IN (SELECT job_id FROM exams)
             AND status != 'Closed'";
        if ($recruiter_id) {
            $query .= " AND recruiter_id = :recruiter_id";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':recruiter_id' => $recruiter_id]);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get exam with job title by exam ID
     */
    public function getExamWithJob($exam_id) {
        $query = "SELECT e.*, j.title as job_title FROM exams e 
                  JOIN jobs j ON e.job_id = j.id 
                  WHERE e.id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$exam_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get all questions for an exam
     */
    public function getAllQuestions($exam_id) {
        $query = "SELECT * FROM questions WHERE exam_id = ? ORDER BY id ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$exam_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get exam results with user info
     */
    public function getExamResultsWithUser($exam_id) {
        $query = "SELECT u.first_name, u.last_name, e.title as exam_title, er.score, er.status, er.attempted_at
                  FROM exam_results er
                  JOIN users u ON er.user_id = u.id
                  JOIN exams e ON er.exam_id = e.id
                  WHERE er.exam_id = ?
                  ORDER BY er.score DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$exam_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}


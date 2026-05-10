<?php
// src/Services/ExamHandler.php

include __DIR__ . '/ProctorEngine.php';

class ExamHandler {
    private $db;
    private $proctor;

    public function __construct($db) {
        $this->db = $db;
        $this->proctor = new ProctorEngine($this->db);
    }

    /**
     * Fetch questions for a specific job's exam
     */
    public function getExamData($job_id) {
        $query = "SELECT e.*, q.id as q_id, q.question_text, q.option_a, q.option_b, q.option_c, q.option_d 
                  FROM exams e 
                  JOIN questions q ON e.id = q.exam_id 
                  WHERE e.job_id = :job_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':job_id' => $job_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Grades the exam and checks for proctoring flags
     */
    public function calculateResult($user_id, $exam_id, $app_id, $answers) {
        // 1. Get correct answers from DB
        $query = "SELECT id, correct_answer FROM questions WHERE exam_id = :exam_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':exam_id' => $exam_id]);
        $correctAnswers = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // 2. Score calculation
        $score = 0;
        foreach ($answers as $q_id => $user_ans) {
            if (isset($correctAnswers[$q_id]) && $correctAnswers[$q_id] === $user_ans) {
                $score++;
            }
        }

        // 3. Determine Pass/Fail (Fetch passing_mark first)
        $stmt = $this->db->prepare("SELECT passing_mark FROM exams WHERE id = :id");
        $stmt->execute([':id' => $exam_id]);
        $exam = $stmt->fetch();
        
        $status = ($score >= $exam['passing_mark']) ? 'Passed' : 'Failed';

        // 4. Save to ExamResult table
        $query = "INSERT INTO exam_results (user_id, exam_id, score, status) VALUES (?, ?, ?, ?)";
        $this->db->prepare($query)->execute([$user_id, $exam_id, $score, $status]);

        return ['score' => $score, 'status' => $status];
    }

    /**
     * Get exam details and questions by Application ID
     */
    public function getExamByApplication($app_id) {
        // First, find the job_id associated with this application
        $query = "SELECT job_id FROM applications WHERE id = :app_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':app_id' => $app_id]);
        $app = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$app) return null;

        // Get the exam for that job
        $query = "SELECT * FROM exams WHERE job_id = :job_id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':job_id' => $app['job_id']]);
        $exam = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$exam) return null;

        // Get the questions for that exam
        $query = "SELECT id, question_text, option_a, option_b, option_c, option_d 
                  FROM questions WHERE exam_id = :exam_id ORDER BY RAND()";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':exam_id' => $exam['id']]);
        $exam['questions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $exam;
    }
}

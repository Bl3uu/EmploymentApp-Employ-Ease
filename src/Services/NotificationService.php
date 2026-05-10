<?php

class NotificationService {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function sendExamAssignmentEmail($application_id) {
        return $this->sendStatusChangeEmail($application_id, 'Exam Assigned');
    }

    public function sendStatusChangeEmail($application_id, $status) {
        $allowedStatuses = ['Exam Assigned', 'Interviewing', 'Offered'];
        if (!in_array($status, $allowedStatuses, true)) {
            return false;
        }

        $context = $this->fetchNotificationContext($application_id);
        if (!$context) {
            return false;
        }

        $subject = "Update for your application: {$context['job_title']}";
        $body = $this->buildEmailBody($context, $status);

        return $this->sendEmail($context['email'], $subject, $body);
    }

    private function fetchNotificationContext($application_id) {
        $query = "SELECT u.email, u.first_name, u.last_name, j.title as job_title
                  FROM applications a
                  JOIN users u ON a.user_id = u.id
                  JOIN jobs j ON a.job_id = j.id
                  WHERE a.id = ?
                  LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$application_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function buildEmailBody(array $context, string $status) {
        $firstName = $context['first_name'];
        $jobTitle = $context['job_title'];

        switch ($status) {
            case 'Exam Assigned':
                $headline = 'Your technical assessment is ready.';
                $message = "A technical exam has been assigned for your application to {$jobTitle}. Please log in to your portal to review the instructions and begin the assessment.";
                break;
            case 'Interviewing':
                $headline = 'Interview time requested.';
                $message = "Your application for {$jobTitle} has advanced to the interview stage. Please confirm a suitable interview slot in your candidate portal.";
                break;
            case 'Offered':
                $headline = 'You have an offer.';
                $message = "Congratulations! Your application for {$jobTitle} has progressed to an offer. Visit your portal for details and next steps.";
                break;
            default:
                $headline = 'Application update';
                $message = "Your application status has changed to {$status}. Please check your candidate portal for the latest update.";
                break;
        }

        return "Hello {$firstName},\n\n{$headline}\n\n{$message}\n\nThank you,\nThe Recruitment Team";
    }

    private function sendEmail(string $to, string $subject, string $body) {
        $headers = "From: no-reply@localhost\r\n" .
                   "Reply-To: no-reply@localhost\r\n" .
                   "Content-Type: text/plain; charset=UTF-8\r\n";

        $autoload = __DIR__ . '/../../vendor/autoload.php';
        if (file_exists($autoload)) {
            require_once $autoload;
        }

        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            try {
                $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                $mail->setFrom('no-reply@localhost', 'Recruitment Portal');
                $mail->addAddress($to);
                $mail->Subject = $subject;
                $mail->Body = $body;
                $mail->isHTML(false);
                return $mail->send();
            } catch (Exception $e) {
                error_log('PHPMailer failed: ' . $e->getMessage());
            }
        }

        return mail($to, $subject, $body, $headers);
    }
}

<?php
// src/Controllers/AuthController.php
// 1. Manually include PHPMailer files
require_once __DIR__ . '/../Libs/PHPMailer/Exception.php';
require_once __DIR__ . '/../Libs/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../Libs/PHPMailer/SMTP.php';

// 2. Import the namespaces so the code below works
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

class AuthController {
    private $auth;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->auth = new UserAuth($this->db);
    }

// --- CSRF Validation Helper ---
    private function validateCsrfToken() {
        $token = $_POST['csrf_token'] ?? '';
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            // Invalid CSRF token - log and redirect
            error_log("CSRF validation failed for user: " . ($_SESSION['user_id'] ?? 'unknown'));
            header("Location: login?error=csrf_invalid");
            exit;
        }
        return true;
    }

    public function handleSignup() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->validateCsrfToken();
            $fname    = htmlspecialchars(strip_tags($_POST['first_name']));
            $lname    = htmlspecialchars(strip_tags($_POST['last_name']));
            $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
            $phone    = htmlspecialchars(strip_tags($_POST['phone']));
            $password = $_POST['password'];
            $role_id  = (int)$_POST['role_id'];

            // Capture the result from the model
            $result = $this->auth->register($fname, $lname, $email, $phone, $password, $role_id);

            // USE TRIPLE EQUALS (===) HERE
            if ($result === true) {
                header("Location: login?status=success");
            } 
            elseif ($result === "email_exists") {
                // This will now correctly send the user back to the signup page
                header("Location: signup?error=exists");
            } 
            else {
                header("Location: signup?error=registration_failed");
            }
            exit;
        }
    }

    public function handleLogin() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->validateCsrfToken();
            $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
            $password = $_POST['password'];

            $query = "SELECT id, email, password, role_id, two_fa_secret, two_fa_enabled FROM users WHERE email = :email LIMIT 1";
            $stmt = $this->db->prepare($query);
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['temp_2fa_user_id'] = $user['id'];
                $_SESSION['temp_user_email'] = $user['email'];
                $_SESSION['role_id'] = $user['role_id']; // Store role for later

                // --- REVISED 2FA DECISION TREE ---

                if (!empty($user['two_fa_secret']) && $user['two_fa_enabled'] == 1) {
                    // CASE 1: App is fully set up and active.
                    $_SESSION['2fa_method'] = 'totp';
                    header("Location: verify-otp"); 
                    exit;
                } 
                else {
                    // CASE 2: App is NOT enabled (even if a secret key exists in the DB).
                    // This forces the user to the QR scan page to complete setup.
                    $_SESSION['2fa_method'] = 'setup';
                    header("Location: setup-2fa");
                    exit;
                }
            } else {
                header("Location: login?error=invalid_credentials");
                exit;
            }
        }
    }

    // Add this to src/Controllers/AuthController.php

    private function sendOTPEmail($email, $otp) {
        $mail = new PHPMailer(true);
        $env = $this->loadEnvConfig();

        // Use Port 465 and ENCRYPTION_SMTPS for better compatibility on local networks
        $smtpHost = $env['SMTP_HOST'] ?? 'smtp.gmail.com';
        $smtpUser = $env['SMTP_USER'] ?? ''; 
        $smtpPass = $env['SMTP_PASS'] ?? ''; 
        $smtpPort = $env['SMTP_PORT'] ?? 465;
        $smtpSecure = ($env['SMTP_SECURE'] === 'ssl') ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

        try {
            $mail->isSMTP();
            $mail->SMTPDebug = 0; // Set to 2 if you still have issues to see the log
            $mail->Host       = $smtpHost;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtpUser;
            $mail->Password   = $smtpPass;
            $mail->SMTPSecure = $smtpSecure;
            $mail->Port       = (int)$smtpPort;

            // --- THE LOCALHOST FIX ---
            // This stops PHPMailer from failing because it doesn't trust your local SSL cert
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false, 
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            $fromEmail = $env['MAIL_FROM_ADDRESS'] ?? 'no-reply@recruitmentportal.com';
            $fromName  = $env['MAIL_FROM_NAME'] ?? 'Recruitment Portal';

            $mail->setFrom($env['MAIL_FROM_ADDRESS'] ?? 'no-reply@recruitmentportal.com', $env['MAIL_FROM_NAME'] ?? 'Recruitment Portal');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Login Verification Code';
            $mail->Body    = "Your verification code is: <b>$otp</b>";

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mail Error: {$mail->ErrorInfo}");
            return false;
        }
    }

    private function loadEnvConfig() {
        $path = __DIR__ . '/../../.env';
        if (!file_exists($path)) {
            error_log("Warning: .env file not found at $path");
            return [];
        }
        // parse_ini_file is cleaner for simple key=value pairs
        return parse_ini_file($path);
    }

    public function handleVerify2FA() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $this->validateCsrfToken();
            
            $userId = $_SESSION['temp_2fa_user_id'] ?? null;
            $method = $_SESSION['2fa_method'] ?? 'email'; 
            $code = $_POST['otp_code'] ?? '';

            if (!$userId) {
                header("Location: login");
                exit;
            }

            if ($method === 'totp' || $method === 'setup') { // Handle both as TOTP
                $success = $this->auth->verifyTOTP($userId, $code);
            } else {
                $success = $this->auth->verify2FA($userId, $code);
            }

            if ($success) {
                unset($_SESSION['temp_2fa_user_id']);
                unset($_SESSION['temp_user_email']);
                unset($_SESSION['2fa_method']);
                
                $this->redirectByUserRole($_SESSION['role_id']);
            } else {
                // FIX: Redirect back to the correct page on failure
                $target = ($method === 'setup') ? "setup-2fa" : "verify-otp";
                header("Location: $target?error=invalid_code");
                exit;
            }
        }
    }

    public function handleLogout() {
        $this->auth->logout(); 
        // Redirect to the root of the public folder
        header("Location: ./login?status=logged_out");
        exit;
    }

    private function startSecureSession() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function redirectByUserRole($role_id) {
        // We use the relative paths that match your index.php switch cases
        if ($role_id == 1) {
            header("Location: dashboard");
        } elseif ($role_id == 2) {
            header("Location: portal");
        } elseif ($role_id == 3) {
            // Admin role
            header("Location: admin-dashboard");
        } else {
            // Defaulting to root if role is unknown
            header("Location: ./");
        }
        exit;
    }

    public function getQRData() {
        $userId = $_SESSION['temp_2fa_user_id'] ?? null;
        $email = $_SESSION['temp_user_email'] ?? '';

        if (!$userId) return null;

        $ga = new PHPGangsta_GoogleAuthenticator();
        
        // 1. Get or create the secret
        $query = "SELECT two_fa_secret FROM users WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
        
        $secret = $user['two_fa_secret'] ?? $this->auth->generateTOTPSecret($userId);

        // 2. Create the URI string (standard format for QR codes)
        // Format: otpauth://totp/Issuer:UserEmail?secret=SECRET&issuer=Issuer
        $issuer = 'EmployEase'; // Change this to your app name
        $otpauthString = "otpauth://totp/" . rawurlencode($issuer) . ":" . rawurlencode($email) . "?secret=" . $secret . "&issuer=" . rawurlencode($issuer);

        return [
            'otpauth' => $otpauthString,
            'secret' => $secret
        ];
    }
}
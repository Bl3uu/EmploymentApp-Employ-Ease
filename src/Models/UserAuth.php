<?php

require_once __DIR__ . '/../Libs/GoogleAuthenticator.php';

class UserAuth {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    // --- REGISTRATION FUNCTION ---
    public function register($fname, $lname, $email, $phone, $password, $role_id) {
        // Check if email exists first
        $checkSql = "SELECT id FROM users WHERE email = :email";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([':email' => $email]);
        if ($checkStmt->fetch()) {
            return "email_exists"; // Return a specific string to handle in Controller
        }
        // Hash the password for security (Bcrypt)
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO users (first_name, last_name, email, phone_number, password, role_id) 
                VALUES (:fname, :lname, :email, :phone, :password, :role_id)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':fname'    => $fname,
            ':lname'    => $lname,
            ':email'    => $email,
            ':phone'    => $phone,
            ':password' => $hashed_password,
            ':role_id'  => $role_id
        ]);
    }

    // --- LOGIN FUNCTION ---
    public function login($email, $password) {
        $query = "SELECT id, first_name, last_name, password, role_id FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->execute([':email' => $email]);

        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (password_verify($password, $user['password'])) {
                $this->createSession($user);
                return true;
            }
        }
        return false;
    }

    private function createSession($user) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name']; // Ensure this line exists!
        $_SESSION['role_id'] = $user['role_id'];
        // Set admin flag if role_id is 3
        $_SESSION['is_admin'] = ($user['role_id'] == 3) ? true : false;
        $_SESSION['logged_in'] = true;
    }

    //Session Guard Protocol
    public function checkSessionTimeout($timeout_limit = 1800) { // Default 30 mins
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        if (isset($_SESSION['last_activity'])) {
            $duration = time() - $_SESSION['last_activity'];

            if ($duration > $timeout_limit) {
                // Inactivity threshold reached
                $this->logout();
                exit;
            }
        }
        // Update activity timestamp so the timer resets on every page refresh
        $_SESSION['last_activity'] = time();
    }

    public function logout() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        session_unset();
        session_destroy();
        // No header() here if you're already handling it in AuthController->handleLogout()
    }

    // Add these inside your UserAuth class
    public function generate2FA($userId) {
        $otp = sprintf("%06d", random_int(0, 999999));
        $expires = gmdate("Y-m-d H:i:s", time() + 600); // 10 minutes from now in UTC

        $sql = "UPDATE users SET two_fa_code = :code, two_fa_expires_at = :expires WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':code' => $otp,
            ':expires' => $expires,
            ':id' => $userId
        ]) ? $otp : false;
    }

    public function verify2FA($userId, $code) {
        $sql = "SELECT id, first_name, last_name, role_id, two_fa_expires_at FROM users 
                WHERE id = :id AND two_fa_code = :code LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId, ':code' => $code]);
        
        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $expiresAt = strtotime($user['two_fa_expires_at']);
            if ($expiresAt !== false && $expiresAt > time()) {
                // Clear code after successful use
                $this->db->prepare("UPDATE users SET two_fa_code = NULL, two_fa_expires_at = NULL WHERE id = ?")
                        ->execute([$userId]);
                $this->createSession($user);
                return true;
            }
        }
        return false;
    }

    public function generateTOTPSecret($userId) {
        $ga = new PHPGangsta_GoogleAuthenticator();
        $secret = $ga->createSecret();

        $sql = "UPDATE users SET two_fa_secret = :secret WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':secret' => $secret, ':id' => $userId]) ? $secret : false;
    }

    // Verify the 6-digit code from the Authenticator App
    public function verifyTOTP($userId, $inputCode) {
        $sql = "SELECT id, first_name, last_name, role_id, two_fa_secret FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['two_fa_secret']) {
            $ga = new PHPGangsta_GoogleAuthenticator();
            // 2 = tolerance (allows for 30 seconds of clock drift before/after)
            $checkResult = $ga->verifyCode($user['two_fa_secret'], $inputCode, 2);

            if ($checkResult) {
                // Important: Mark 2FA as enabled now that they've successfully verified it once
                $this->db->prepare("UPDATE users SET two_fa_enabled = 1 WHERE id = ?")->execute([$userId]);
                $this->createSession($user);
                return true;
            }
        }
        return false;
    }
}
?>
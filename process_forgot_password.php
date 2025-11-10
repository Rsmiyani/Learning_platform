<?php
require_once 'config/database.php';
require_once 'config/email.php';
initSession();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot-password.php');
    exit;
}

// Get form data
$email = trim($_POST['email'] ?? '');

// Validation
if (empty($email)) {
    $_SESSION['error'] = "Email is required";
    header('Location: forgot-password.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format";
    header('Location: forgot-password.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT user_id, first_name, email FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if (!$user) {
        // Don't reveal if email exists or not (security best practice)
        $_SESSION['success'] = "If an account exists with this email, you will receive password reset instructions.";
        header('Location: forgot-password.php');
        exit;
    }
    
    // Generate reset token
    $reset_token = bin2hex(random_bytes(32));
    $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
    
    // Invalidate any existing unused tokens for this user
    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ? AND used = 0");
    $stmt->execute([$user['user_id']]);
    
    // Store token in database
    try {
        $stmt = $pdo->prepare("
            INSERT INTO password_resets (user_id, email, token, expires_at, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$user['user_id'], $email, $reset_token, $token_expiry]);
        
        // Generate reset link (get base URL dynamically)
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        $path = dirname($_SERVER['SCRIPT_NAME']);
        // Normalize path (remove trailing slash if not root)
        $path = rtrim($path, '/');
        if ($path === '' || $path === '.') {
            $path = '';
        }
        $base_url = $protocol . '://' . $host . $path;
        // URL encode the token to handle special characters properly (use rawurlencode for better compatibility)
        $reset_link = $base_url . "/reset-password.php?token=" . rawurlencode($reset_token);
        
        // Debug: Log the actual token stored in DB and the link
        error_log("Token stored in DB: " . $reset_token . " (length: " . strlen($reset_token) . ")");
        error_log("Reset link generated: " . $reset_link);
        
        // Send password reset email
        $mailer = getMailer();
        $email_sent = $mailer->sendPasswordResetEmail(
            $email,
            $user['first_name'],
            $reset_token,
            $reset_link
        );
        
        if ($email_sent) {
            error_log("Password reset email sent successfully to: " . $email);
            $_SESSION['success'] = "Password reset instructions have been sent to your email address. Please check your inbox.";
        } else {
            error_log("Failed to send password reset email to: " . $email);
            // Still show success message for security (don't reveal if email failed)
            // But also store link in session for testing purposes
            $_SESSION['reset_link'] = $reset_link;
            $_SESSION['success'] = "Password reset link has been generated. Check your email or use the link below (testing mode).";
        }
        
    } catch (PDOException $e) {
        // Check if password_resets table exists
        if ($e->getCode() == '42S02') {
            $_SESSION['error'] = "Password reset feature is not fully configured. Please contact administrator.";
            error_log("Password reset error: password_resets table does not exist. Run database/password_resets.sql");
        } else {
            error_log("Password reset error: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred. Please try again later.";
        }
        header('Location: forgot-password.php');
        exit;
    }
    
    header('Location: forgot-password.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Forgot password error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header('Location: forgot-password.php');
    exit;
}
?>

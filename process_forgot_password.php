<?php
require_once 'config/database.php';
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
    
    // Store token in database (you'll need to add this table)
    // For now, we'll just show a success message
    // TODO: Create password_resets table and send email
    
    // In a real application, you would:
    // 1. Store the token in a password_resets table with expiry
    // 2. Send an email with reset link: reset-password.php?token=$reset_token
    // 3. User clicks link, validates token, and sets new password
    
    // For demonstration, we'll just show success
    $_SESSION['success'] = "Password reset instructions have been sent to your email. Please check your inbox.";
    
    // Log the reset request
    error_log("Password reset requested for: " . $email . " | Token: " . $reset_token);
    
    header('Location: forgot-password.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Forgot password error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again later.";
    header('Location: forgot-password.php');
    exit;
}
?>

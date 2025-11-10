<?php
require_once 'config/database.php';
initSession();

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: forgot-password.php');
    exit;
}

// Get form data
$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validation
if (empty($token) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = "All fields are required";
    header('Location: reset-password.php?token=' . urlencode($token));
    exit;
}

if ($password !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match";
    header('Location: reset-password.php?token=' . urlencode($token));
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters long";
    header('Location: reset-password.php?token=' . urlencode($token));
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Find valid reset token
    $stmt = $pdo->prepare("
        SELECT pr.reset_id, pr.user_id, pr.email, pr.expires_at, pr.used 
        FROM password_resets pr
        WHERE pr.token = ? AND pr.used = 0 AND pr.expires_at > NOW()
        ORDER BY pr.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    if (!$reset) {
        $_SESSION['error'] = 'Invalid or expired reset token. Please request a new password reset.';
        header('Location: forgot-password.php');
        exit;
    }
    
    // Check if user exists and is active
    $stmt = $pdo->prepare("SELECT user_id, status FROM users WHERE user_id = ?");
    $stmt->execute([$reset['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = 'User not found.';
        header('Location: forgot-password.php');
        exit;
    }
    
    if ($user['status'] !== 'active') {
        $_SESSION['error'] = 'Your account is inactive. Please contact support.';
        header('Location: forgot-password.php');
        exit;
    }
    
    // Hash new password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Update user password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    $stmt->execute([$hashed_password, $reset['user_id']]);
    
    // Mark reset token as used
    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE reset_id = ?");
    $stmt->execute([$reset['reset_id']]);
    
    // Invalidate all other reset tokens for this user
    $stmt = $pdo->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ? AND reset_id != ?");
    $stmt->execute([$reset['user_id'], $reset['reset_id']]);
    
    // Clear reset session
    unset($_SESSION['reset_user_id']);
    unset($_SESSION['reset_token']);
    
    $_SESSION['success'] = "Password reset successful! You can now login with your new password.";
    header('Location: login.php');
    exit;
    
} catch (PDOException $e) {
    error_log("Reset password error: " . $e->getMessage());
    $_SESSION['error'] = 'An error occurred. Please try again.';
    header('Location: reset-password.php?token=' . urlencode($token));
    exit;
}
?>


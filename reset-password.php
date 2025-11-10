<?php
require_once 'config/database.php';
initSession();

// Get token from URL and decode it properly
$token = trim($_GET['token'] ?? '');

if (empty($token)) {
    $_SESSION['error'] = 'Invalid or missing reset token.';
    header('Location: forgot-password.php');
    exit;
}

// Decode URL-encoded token (in case it was encoded)
$token = rawurldecode($token);
$token = trim($token);

// Validate token format (should be 64 hex characters)
if (strlen($token) !== 64 || !ctype_xdigit($token)) {
    error_log("Invalid token format - Length: " . strlen($token) . ", Token start: " . substr($token, 0, 20));
    error_log("Raw token from URL: " . ($_GET['token'] ?? 'none'));
    $_SESSION['error'] = 'Invalid reset token format. Token length: ' . strlen($token) . ' (expected 64). Please request a new password reset.';
    header('Location: forgot-password.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Debug: Log token for troubleshooting
    error_log("Reset password attempt - Token received: " . $token . " (length: " . strlen($token) . ")");
    
    // Try multiple approaches to find the token
    $reset = null;
    
    // Approach 1: Exact match with timezone-aware comparison
    // Use UNIX_TIMESTAMP for better timezone handling
    $stmt = $pdo->prepare("
        SELECT pr.reset_id, pr.user_id, pr.email, pr.expires_at, pr.used, u.first_name 
        FROM password_resets pr
        INNER JOIN users u ON pr.user_id = u.user_id
        WHERE pr.token = ? AND pr.used = 0 AND UNIX_TIMESTAMP(pr.expires_at) > UNIX_TIMESTAMP(NOW())
        ORDER BY pr.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$token]);
    $reset = $stmt->fetch();
    
    // If still not found, try without expiry check (timezone issue workaround)
    if (!$reset) {
        $stmt = $pdo->prepare("
            SELECT pr.reset_id, pr.user_id, pr.email, pr.expires_at, pr.used, u.first_name 
            FROM password_resets pr
            INNER JOIN users u ON pr.user_id = u.user_id
            WHERE pr.token = ? AND pr.used = 0
            ORDER BY pr.created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$token]);
        $reset_temp = $stmt->fetch();
        
        // Manually check expiry with PHP time
        if ($reset_temp) {
            $expires_timestamp = strtotime($reset_temp['expires_at']);
            if ($expires_timestamp > time()) {
                $reset = $reset_temp;
                error_log("Token validated with PHP time check (bypassing MySQL timezone issue)");
            }
        }
    }
    
    // Approach 2: Try with decoded token if exact match fails
    if (!$reset) {
        $decoded_token = rawurldecode($token);
        if ($decoded_token !== $token) {
            error_log("Trying decoded token: " . $decoded_token);
            $stmt->execute([$decoded_token]);
            $reset = $stmt->fetch();
        }
    }
    
    // Approach 3: Try trimming and cleaning
    if (!$reset) {
        $clean_token = trim($token);
        if ($clean_token !== $token) {
            error_log("Trying cleaned token: " . $clean_token);
            $stmt->execute([$clean_token]);
            $reset = $stmt->fetch();
        }
    }
    
    // Debug: Check what tokens exist in database
    if (!$reset) {
        $debug_stmt = $pdo->prepare("SELECT token, LENGTH(token) as token_len, used, expires_at, created_at FROM password_resets WHERE user_id IN (SELECT user_id FROM users WHERE email = ?) ORDER BY created_at DESC LIMIT 5");
        // Try to get email from recent tokens
        $debug_stmt2 = $pdo->query("SELECT token, email, LENGTH(token) as token_len, used, expires_at FROM password_resets ORDER BY created_at DESC LIMIT 5");
        $debug_tokens = $debug_stmt2->fetchAll();
        error_log("Recent tokens in DB: " . json_encode($debug_tokens));
        
        // Check if token exists at all (even if expired or used)
        $stmt = $pdo->prepare("SELECT pr.reset_id, pr.used, pr.expires_at, pr.token, LENGTH(pr.token) as token_len FROM password_resets pr WHERE pr.token = ? OR pr.token LIKE ? ORDER BY pr.created_at DESC LIMIT 1");
        $stmt->execute([$token, substr($token, 0, 20) . '%']);
        $token_check = $stmt->fetch();
        
        if ($token_check) {
            $expires_at = $token_check['expires_at'];
            $expires_timestamp = strtotime($expires_at);
            $current_timestamp = time();
            $is_expired = $expires_timestamp <= $current_timestamp;
            $is_used = ($token_check['used'] == 1);
            
            error_log("Token found but validation failed - Used: {$token_check['used']}, Expires: {$expires_at}, Expires timestamp: {$expires_timestamp}, Current timestamp: {$current_timestamp}, Is expired: " . ($is_expired ? 'YES' : 'NO') . ", Token length in DB: {$token_check['token_len']}");
            
            if ($is_used) {
                $_SESSION['error'] = 'This reset link has already been used. Please request a new password reset.';
            } else if ($is_expired) {
                $time_diff = $expires_timestamp - $current_timestamp;
                $_SESSION['error'] = 'This reset link has expired. Please request a new password reset. (Expired ' . abs(round($time_diff / 60)) . ' minutes ago)';
            } else {
                // Token exists but query failed - try query without expiry check
                $stmt2 = $pdo->prepare("
                    SELECT pr.reset_id, pr.user_id, pr.email, pr.expires_at, pr.used, u.first_name 
                    FROM password_resets pr
                    INNER JOIN users u ON pr.user_id = u.user_id
                    WHERE pr.token = ? AND pr.used = 0
                    ORDER BY pr.created_at DESC
                    LIMIT 1
                ");
                $stmt2->execute([$token]);
                $reset_without_expiry = $stmt2->fetch();
                
                if ($reset_without_expiry) {
                    // Token is valid, proceed anyway (might be timezone issue)
                    $reset = $reset_without_expiry;
                    error_log("Token validated without expiry check (timezone issue suspected)");
                } else {
                    $_SESSION['error'] = 'Token found but validation failed. Please request a new password reset.';
                }
            }
        } else {
            $_SESSION['error'] = 'Invalid reset token. No matching token found in database. Please request a new password reset.';
        }
        header('Location: forgot-password.php');
        exit;
    }
    
    // Store user_id in session for security
    $_SESSION['reset_user_id'] = $reset['user_id'];
    $_SESSION['reset_token'] = $token;
    
} catch (PDOException $e) {
    error_log("Reset password error: " . $e->getMessage());
    error_log("Error code: " . $e->getCode());
    $_SESSION['error'] = 'An error occurred: ' . htmlspecialchars($e->getMessage()) . '. Please try again or contact support.';
    header('Location: forgot-password.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - TrainAI</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <style>
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <!-- Left Side - Image with Text Overlay -->
        <div class="auth-left">
            <div class="auth-image-container">
                <img src="assets/images/photo.png" alt="Professional Learning Environment" class="auth-image">
                <div class="auth-overlay">
                    <div class="auth-branding">
                        <div class="brand-logo">🎓</div>
                        <h1>TrainAI</h1>
                        <p class="tagline">by DebugThugs</p>
                        
                        <p class="description">
                            Create a new secure password for your account. 
                            Make sure it's strong and memorable!
                        </p>

                        <div class="auth-stats">
                            <div class="stat-small">
                                <h3>🔒</h3>
                                <p>Secure Reset</p>
                            </div>
                            <div class="stat-small">
                                <h3>⚡</h3>
                                <p>Quick Process</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Reset Password Form -->
        <div class="auth-right">
            <div class="auth-form-container">
                <div class="form-header">
                    <h2>Reset Password</h2>
                    <p>Enter your new password</p>
                </div>

                <?php
                if (isset($_SESSION['success'])) {
                    echo '<div class="success-message">' . htmlspecialchars($_SESSION['success']) . '</div>';
                    unset($_SESSION['success']);
                }
                if (isset($_SESSION['error'])) {
                    echo '<div class="error-message">' . htmlspecialchars($_SESSION['error']) . '</div>';
                    unset($_SESSION['error']);
                }
                ?>

                <form action="process_reset_password.php" method="POST" class="auth-form" id="resetForm">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter new password"
                            required
                            minlength="6"
                        >
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Confirm new password"
                            required
                            minlength="6"
                        >
                    </div>

                    <button type="submit" class="btn-submit">Reset Password</button>

                    <p class="form-footer" style="margin-top: 20px;">
                        Remember your password? <a href="login.php" class="link-primary">Login</a>
                    </p>
                </form>

                <div class="back-home">
                    <a href="index.html">← Back to Home</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Client-side password validation
        document.getElementById('resetForm').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Password must be at least 6 characters long!');
                return false;
            }
        });
    </script>
</body>
</html>


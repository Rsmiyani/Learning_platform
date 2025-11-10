<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - TrainAI</title>
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
                            Don't worry! Resetting your password is easy. 
                            Just enter your email address and we'll help you get back on track.
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

        <!-- Right Side - Forgot Password Form -->
        <div class="auth-right">
            <div class="auth-form-container">
                <div class="form-header">
                    <h2>Forgot Password?</h2>
                    <p>Enter your email to reset your password</p>
                </div>

                <?php
                if (isset($_SESSION['success'])) {
                    echo '<div class="success-message">' . htmlspecialchars($_SESSION['success']);
                    // Show reset link if available (for testing)
                    if (isset($_SESSION['reset_link'])) {
                        echo '<br><br><strong>Reset Link (for testing):</strong><br>';
                        echo '<a href="' . htmlspecialchars($_SESSION['reset_link']) . '" style="color: #155724; text-decoration: underline; word-break: break-all;">' . htmlspecialchars($_SESSION['reset_link']) . '</a>';
                        unset($_SESSION['reset_link']);
                    }
                    echo '</div>';
                    unset($_SESSION['success']);
                }
                if (isset($_SESSION['error'])) {
                    echo '<div class="error-message">' . htmlspecialchars($_SESSION['error']) . '</div>';
                    unset($_SESSION['error']);
                }
                ?>

                <form action="process_forgot_password.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            placeholder="Enter your registered email"
                            required
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
</body>
</html>

<?php
/**
 * Email System Test Script
 * 
 * This script tests all email functionality without requiring database interaction.
 * Use this to verify email configuration before deploying.
 */

require_once 'config/email.php';

// Prevent direct access in production
if (!defined('TESTING_MODE')) {
    define('TESTING_MODE', true);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email System Test - TrainAI</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .status {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .status.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .status.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .status.info {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
        }
        .test-section {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .test-section h2 {
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
        }
        input[type="email"],
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        input[type="email"]:focus,
        input[type="text"]:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.3s;
        }
        button:hover {
            opacity: 0.9;
        }
        .config-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .config-info h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 16px;
        }
        .config-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }
        .config-item:last-child {
            border-bottom: none;
        }
        .config-label {
            color: #666;
            font-weight: 500;
        }
        .config-value {
            color: #333;
            font-family: monospace;
        }
        .back-link {
            text-align: center;
            margin-top: 30px;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 Email System Test</h1>
        <p class="subtitle">TrainAI Learning Platform</p>

        <!-- Configuration Status -->
        <div class="config-info">
            <h3>📋 Current Configuration</h3>
            <div class="config-item">
                <span class="config-label">Email Enabled:</span>
                <span class="config-value"><?php echo EMAIL_ENABLED ? '✅ Yes' : '❌ No (Test Mode)'; ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">SMTP Enabled:</span>
                <span class="config-value"><?php echo USE_SMTP ? '✅ Yes' : '❌ No (PHP mail)'; ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">From Address:</span>
                <span class="config-value"><?php echo EMAIL_FROM_ADDRESS; ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">From Name:</span>
                <span class="config-value"><?php echo EMAIL_FROM_NAME; ?></span>
            </div>
            <div class="config-item">
                <span class="config-label">Log Path:</span>
                <span class="config-value"><?php echo file_exists(EMAIL_LOG_PATH) ? '✅ Exists' : '⚠️ Will be created'; ?></span>
            </div>
        </div>

        <?php
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $test_email = $_POST['test_email'] ?? '';
            $test_type = $_POST['test_type'] ?? '';
            
            if (empty($test_email) || !filter_var($test_email, FILTER_VALIDATE_EMAIL)) {
                echo '<div class="status error">❌ Please enter a valid email address.</div>';
            } else {
                $mailer = getMailer();
                $success = false;
                
                switch ($test_type) {
                    case 'password_reset':
                        $reset_link = 'http://localhost/Learning-Platform - Copy/reset-password.php?token=TEST123456';
                        $success = $mailer->sendPasswordResetEmail($test_email, 'Test User', 'TEST123456', $reset_link);
                        break;
                        
                    case 'welcome':
                        $success = $mailer->sendWelcomeEmail($test_email, 'Test User', 'Trainee');
                        break;
                        
                    case 'certificate':
                        $cert_link = 'http://localhost/Learning-Platform - Copy/pages/trainee/certificates.php';
                        $success = $mailer->sendCertificateEmail($test_email, 'Test User', 'Introduction to PHP', 'CERT-TEST-123', $cert_link);
                        break;
                        
                    case 'exam_pass':
                        $success = $mailer->sendExamResultEmail($test_email, 'Test User', 'Introduction to PHP', 85.5, true);
                        break;
                        
                    case 'exam_fail':
                        $success = $mailer->sendExamResultEmail($test_email, 'Test User', 'Introduction to PHP', 65.0, false);
                        break;
                }
                
                if ($success) {
                    if (EMAIL_ENABLED) {
                        echo '<div class="status success">✅ Email sent successfully to ' . htmlspecialchars($test_email) . '! Check your inbox.</div>';
                    } else {
                        echo '<div class="status info">ℹ️ Email logged successfully (EMAIL_ENABLED is false). Check logs/email.log</div>';
                    }
                } else {
                    echo '<div class="status error">❌ Failed to send email. Check logs/email.log for details.</div>';
                }
            }
        }
        ?>

        <!-- Test Forms -->
        <div class="test-section">
            <h2>🔐 Test Password Reset Email</h2>
            <form method="POST">
                <input type="hidden" name="test_type" value="password_reset">
                <div class="form-group">
                    <label for="email1">Recipient Email:</label>
                    <input type="email" id="email1" name="test_email" placeholder="test@example.com" required>
                </div>
                <button type="submit">Send Password Reset Email</button>
            </form>
        </div>

        <div class="test-section">
            <h2>👋 Test Welcome Email</h2>
            <form method="POST">
                <input type="hidden" name="test_type" value="welcome">
                <div class="form-group">
                    <label for="email2">Recipient Email:</label>
                    <input type="email" id="email2" name="test_email" placeholder="test@example.com" required>
                </div>
                <button type="submit">Send Welcome Email</button>
            </form>
        </div>

        <div class="test-section">
            <h2>🎓 Test Certificate Email</h2>
            <form method="POST">
                <input type="hidden" name="test_type" value="certificate">
                <div class="form-group">
                    <label for="email3">Recipient Email:</label>
                    <input type="email" id="email3" name="test_email" placeholder="test@example.com" required>
                </div>
                <button type="submit">Send Certificate Email</button>
            </form>
        </div>

        <div class="test-section">
            <h2>✅ Test Exam Pass Email</h2>
            <form method="POST">
                <input type="hidden" name="test_type" value="exam_pass">
                <div class="form-group">
                    <label for="email4">Recipient Email:</label>
                    <input type="email" id="email4" name="test_email" placeholder="test@example.com" required>
                </div>
                <button type="submit">Send Exam Pass Email</button>
            </form>
        </div>

        <div class="test-section">
            <h2>❌ Test Exam Fail Email</h2>
            <form method="POST">
                <input type="hidden" name="test_type" value="exam_fail">
                <div class="form-group">
                    <label for="email5">Recipient Email:</label>
                    <input type="email" id="email5" name="test_email" placeholder="test@example.com" required>
                </div>
                <button type="submit">Send Exam Fail Email</button>
            </form>
        </div>

        <!-- Instructions -->
        <div class="status info">
            <strong>ℹ️ Testing Instructions:</strong><br>
            1. Enter your email address in any form above<br>
            2. Click the button to send a test email<br>
            3. Check your inbox (and spam folder)<br>
            4. If EMAIL_ENABLED is false, check logs/email.log instead<br>
            5. Configure SMTP in config/email.php for production use
        </div>

        <div class="back-link">
            <a href="index.html">← Back to Home</a>
        </div>
    </div>
</body>
</html>

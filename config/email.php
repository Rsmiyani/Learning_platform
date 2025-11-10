<?php
/**
 * Email Configuration for TrainAI Learning Platform
 * 
 * This file contains email settings and the Mailer class for sending emails.
 * Supports both SMTP and PHP mail() function.
 */

// Email Configuration
define('EMAIL_FROM_ADDRESS', 'noreply@trainai.com');
define('EMAIL_FROM_NAME', 'TrainAI Learning Platform');
define('EMAIL_REPLY_TO', 'support@trainai.com');

// SMTP Configuration (Gmail)
define('USE_SMTP', true); // Set to true to use SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'rudramiyani2008@gmail.com'); // Your Gmail address
define('SMTP_PASSWORD', 'pjrjfeaqxhalussa'); // Your App Password (spaces removed)
define('SMTP_ENCRYPTION', 'tls'); // 'tls' or 'ssl'

// Email Settings
define('EMAIL_ENABLED', true); // Set to false to disable all emails (for testing)
define('EMAIL_LOG_PATH', __DIR__ . '/../logs/email.log');

/**
 * Mailer Class - Handles email sending
 */
class Mailer {
    private $from_email;
    private $from_name;
    private $reply_to;
    
    public function __construct() {
        $this->from_email = EMAIL_FROM_ADDRESS;
        $this->from_name = EMAIL_FROM_NAME;
        $this->reply_to = EMAIL_REPLY_TO;
    }
    
    /**
     * Send an email
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $html_body HTML email body
     * @param string $plain_body Plain text email body (optional)
     * @return bool Success status
     */
    public function send($to, $subject, $html_body, $plain_body = '') {
        // Check if email is enabled
        if (!EMAIL_ENABLED) {
            $this->log("Email disabled. Would have sent to: $to - Subject: $subject");
            return true; // Return true for testing purposes
        }
        
        try {
            if (USE_SMTP) {
                return $this->sendViaSMTP($to, $subject, $html_body, $plain_body);
            } else {
                return $this->sendViaPHPMail($to, $subject, $html_body, $plain_body);
            }
        } catch (Exception $e) {
            $this->log("Email send failed: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send email using PHP mail() function
     */
    private function sendViaPHPMail($to, $subject, $html_body, $plain_body) {
        // Create email headers
        $headers = [];
        $headers[] = "MIME-Version: 1.0";
        $headers[] = "Content-Type: text/html; charset=UTF-8";
        $headers[] = "From: {$this->from_name} <{$this->from_email}>";
        $headers[] = "Reply-To: {$this->reply_to}";
        $headers[] = "X-Mailer: PHP/" . phpversion();
        
        // Send email
        $success = mail($to, $subject, $html_body, implode("\r\n", $headers));
        
        if ($success) {
            $this->log("Email sent successfully to: $to - Subject: $subject");
        } else {
            $this->log("Email send failed to: $to - Subject: $subject");
        }
        
        return $success;
    }
    
    /**
     * Send email using SMTP with PHPMailer
     */
    private function sendViaSMTP($to, $subject, $html_body, $plain_body) {
        // Load PHPMailer
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
        require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_ENCRYPTION;
            $mail->Port = SMTP_PORT;
            
            // Recipients
            $mail->setFrom($this->from_email, $this->from_name);
            $mail->addAddress($to);
            $mail->addReplyTo($this->reply_to, $this->from_name);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html_body;
            $mail->AltBody = $plain_body ?: strip_tags($html_body);
            
            // Send
            $mail->send();
            $this->log("Email sent successfully via SMTP to: $to - Subject: $subject");
            return true;
            
        } catch (Exception $e) {
            $this->log("SMTP send failed to: $to - Error: {$mail->ErrorInfo}");
            return false;
        }
    }
    
    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail($to, $user_name, $reset_token, $reset_link) {
        $subject = "Reset Your TrainAI Password";
        
        $html_body = $this->getPasswordResetTemplate($user_name, $reset_link);
        $plain_body = "Hi $user_name,\n\nYou requested to reset your password. Click the link below to reset it:\n\n$reset_link\n\nThis link will expire in 1 hour.\n\nIf you didn't request this, please ignore this email.\n\nBest regards,\nTrainAI Team";
        
        return $this->send($to, $subject, $html_body, $plain_body);
    }
    
    /**
     * Send certificate earned email
     */
    public function sendCertificateEmail($to, $user_name, $course_name, $certificate_code, $certificate_link) {
        $subject = "🎉 Congratulations! You've Earned a Certificate";
        
        $html_body = $this->getCertificateTemplate($user_name, $course_name, $certificate_code, $certificate_link);
        $plain_body = "Congratulations $user_name!\n\nYou have successfully completed the course: $course_name\n\nYour certificate code: $certificate_code\n\nView and download your certificate here: $certificate_link\n\nKeep up the great work!\n\nBest regards,\nTrainAI Team";
        
        return $this->send($to, $subject, $html_body, $plain_body);
    }
    
    /**
     * Send exam result email
     */
    public function sendExamResultEmail($to, $user_name, $course_name, $score, $passed) {
        $subject = $passed ? "🎉 Exam Passed - $course_name" : "Exam Results - $course_name";
        
        $html_body = $this->getExamResultTemplate($user_name, $course_name, $score, $passed);
        $plain_body = "Hi $user_name,\n\nYour exam results for $course_name:\n\nScore: $score%\nStatus: " . ($passed ? "PASSED" : "FAILED") . "\n\n" . ($passed ? "Congratulations! Your certificate has been generated." : "Don't worry! You can retake the exam.") . "\n\nBest regards,\nTrainAI Team";
        
        return $this->send($to, $subject, $html_body, $plain_body);
    }
    
    /**
     * Send welcome email
     */
    public function sendWelcomeEmail($to, $user_name, $role) {
        $subject = "Welcome to TrainAI Learning Platform! 🎓";
        
        $html_body = $this->getWelcomeTemplate($user_name, $role);
        $plain_body = "Welcome to TrainAI, $user_name!\n\nYour account has been created successfully as a $role.\n\nStart exploring courses and begin your learning journey today!\n\nLogin at: " . $this->getBaseUrl() . "/login.php\n\nBest regards,\nTrainAI Team";
        
        return $this->send($to, $subject, $html_body, $plain_body);
    }
    
    /**
     * Get password reset email template
     */
    private function getPasswordResetTemplate($user_name, $reset_link) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; line-height: 1.6; color: #1A1A1A; background-color: #F5F1E8; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #FDFCFA; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #E8E4D9; }
        .header { background: #1A1A1A; color: #F5F1E8; padding: 35px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 700; }
        .header p { margin: 8px 0 0 0; font-size: 14px; opacity: 0.9; }
        .content { padding: 40px 35px; background: #FDFCFA; }
        .content h2 { color: #6B5B95; margin-top: 0; font-size: 22px; font-weight: 600; }
        .content p { color: #4A4A4A; margin: 15px 0; }
        .button { display: inline-block; padding: 14px 32px; background: #1A1A1A; color: #F5F1E8; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: 600; transition: all 0.3s ease; }
        .button:hover { background: #000000; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
        .footer { background: #F5F1E8; padding: 25px; text-align: center; color: #6B6B6B; font-size: 13px; border-top: 1px solid #E8E4D9; }
        .warning { background: #FFF9E6; border-left: 4px solid #F59E0B; padding: 16px; margin: 20px 0; border-radius: 6px; color: #4A4A4A; }
        .info-box { background: #F0F7FF; border-left: 4px solid #009B95; padding: 16px; margin: 20px 0; border-radius: 6px; color: #4A4A4A; }
        .link-text { word-break: break-all; color: #6B5B95; font-size: 13px; background: #F5F1E8; padding: 12px; border-radius: 6px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 TrainAI</h1>
            <p>Learning Platform</p>
        </div>
        <div class="content">
            <h2>Password Reset Request</h2>
            <p>Hi <strong>$user_name</strong>,</p>
            <p>We received a request to reset your password for your TrainAI account. Click the button below to create a new password:</p>
            <div style="text-align: center;">
                <a href="$reset_link" class="button">Reset Password</a>
            </div>
            <div class="info-box">
                <strong>⏰ This link will expire in 1 hour</strong>
            </div>
            <p>If the button doesn't work, copy and paste this link into your browser:</p>
            <div class="link-text">$reset_link</div>
            <div class="warning">
                <strong>⚠️ Security Notice:</strong><br>
                If you didn't request this password reset, please ignore this email. Your password will remain unchanged.
            </div>
        </div>
        <div class="footer">
            <p style="margin: 0 0 8px 0; font-weight: 600; color: #1A1A1A;">&copy; 2025 TrainAI Learning Platform</p>
            <p style="margin: 0;">by Team DebugThugs • Marwadi University, Rajkot, Gujarat</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get certificate email template
     */
    private function getCertificateTemplate($user_name, $course_name, $certificate_code, $certificate_link) {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; line-height: 1.6; color: #1A1A1A; background-color: #F5F1E8; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #FDFCFA; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #E8E4D9; }
        .header { background: #6B5B95; color: #F5F1E8; padding: 35px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 700; }
        .header p { margin: 8px 0 0 0; font-size: 14px; opacity: 0.95; }
        .content { padding: 40px 35px; background: #FDFCFA; }
        .content h2 { color: #6B5B95; margin-top: 0; font-size: 22px; font-weight: 600; }
        .content p { color: #4A4A4A; margin: 15px 0; }
        .button { display: inline-block; padding: 14px 32px; background: #1A1A1A; color: #F5F1E8; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: 600; transition: all 0.3s ease; }
        .button:hover { background: #000000; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
        .footer { background: #F5F1E8; padding: 25px; text-align: center; color: #6B6B6B; font-size: 13px; border-top: 1px solid #E8E4D9; }
        .certificate-box { background: #F5F1E8; border: 2px solid #6B5B95; border-radius: 10px; padding: 25px; margin: 25px 0; text-align: center; }
        .certificate-code { font-size: 20px; font-weight: 700; color: #6B5B95; margin: 12px 0; letter-spacing: 1.5px; font-family: 'Courier New', monospace; }
        .achievement { background: #FFF9E6; border: 1px solid #E8E4D9; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: center; }
        .achievement h3 { margin: 0; color: #1A1A1A; font-size: 18px; font-weight: 600; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Congratulations!</h1>
            <p>You've Earned a Certificate</p>
        </div>
        <div class="content">
            <h2>Well Done, $user_name!</h2>
            <p>We're thrilled to inform you that you have successfully completed the course:</p>
            <div class="achievement">
                <h3>📚 $course_name</h3>
            </div>
            <p>Your dedication and hard work have paid off! Here are your certificate details:</p>
            <div class="certificate-box">
                <p style="margin: 0; font-size: 13px; color: #6B6B6B; text-transform: uppercase; letter-spacing: 1px;">Certificate Code</p>
                <div class="certificate-code">$certificate_code</div>
                <p style="margin: 10px 0 0 0; font-size: 12px; color: #6B6B6B;">Keep this code for verification</p>
            </div>
            <div style="text-align: center;">
                <a href="$certificate_link" class="button">📄 View & Download Certificate</a>
            </div>
            <p style="text-align: center; margin-top: 30px; color: #1A1A1A;">
                <strong>🏆 Keep up the excellent work!</strong><br>
                <span style="color: #4A4A4A;">Continue exploring more courses to enhance your skills.</span>
            </p>
        </div>
        <div class="footer">
            <p style="margin: 0 0 8px 0; font-weight: 600; color: #1A1A1A;">&copy; 2025 TrainAI Learning Platform</p>
            <p style="margin: 0;">by Team DebugThugs • Marwadi University, Rajkot, Gujarat</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get exam result email template
     */
    private function getExamResultTemplate($user_name, $course_name, $score, $passed) {
        $status_color = $passed ? '#10B981' : '#EF4444';
        $status_text = $passed ? 'PASSED' : 'FAILED';
        $emoji = $passed ? '🎉' : '📝';
        $header_bg = $passed ? '#6B5B95' : '#1A1A1A';
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; line-height: 1.6; color: #1A1A1A; background-color: #F5F1E8; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #FDFCFA; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #E8E4D9; }
        .header { background: $header_bg; color: #F5F1E8; padding: 35px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 700; }
        .content { padding: 40px 35px; background: #FDFCFA; }
        .content h2 { color: #6B5B95; margin-top: 0; font-size: 22px; font-weight: 600; }
        .content p { color: #4A4A4A; margin: 15px 0; }
        .footer { background: #F5F1E8; padding: 25px; text-align: center; color: #6B6B6B; font-size: 13px; border-top: 1px solid #E8E4D9; }
        .result-box { background: #F5F1E8; border: 2px solid $status_color; border-radius: 10px; padding: 35px; margin: 25px 0; text-align: center; }
        .score { font-size: 52px; font-weight: 700; color: $status_color; margin: 10px 0; }
        .status { font-size: 24px; font-weight: 600; color: $status_color; margin: 10px 0; letter-spacing: 2px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>$emoji Exam Results</h1>
        </div>
        <div class="content">
            <h2>Hi $user_name,</h2>
            <p>Your exam results for <strong>$course_name</strong> are ready:</p>
            <div class="result-box">
                <div class="score">$score%</div>
                <div class="status">$status_text</div>
            </div>
HTML;
        
        if ($passed) {
            $html_body .= <<<HTML
            <p style="text-align: center; color: #10B981; font-weight: 600; font-size: 16px;">
                🎊 Congratulations! You've passed the exam!<br>
                <span style="color: #4A4A4A; font-weight: 400; font-size: 14px;">Your certificate has been generated and is ready to download.</span>
            </p>
HTML;
        } else {
            $html_body .= <<<HTML
            <p style="text-align: center; color: #EF4444; font-weight: 600; font-size: 16px;">
                Don't worry! You can retake the exam to improve your score.<br>
                <span style="color: #4A4A4A; font-weight: 400; font-size: 14px;">Review the course materials and try again when you're ready.</span>
            </p>
HTML;
        }
        
        $html_body .= <<<HTML
        </div>
        <div class="footer">
            <p style="margin: 0 0 8px 0; font-weight: 600; color: #1A1A1A;">&copy; 2025 TrainAI Learning Platform</p>
            <p style="margin: 0;">by Team DebugThugs • Marwadi University, Rajkot, Gujarat</p>
        </div>
    </div>
</body>
</html>
HTML;
        
        return $html_body;
    }
    
    /**
     * Get welcome email template
     */
    private function getWelcomeTemplate($user_name, $role) {
        $login_link = $this->getBaseUrl() . '/login.php';
        
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; line-height: 1.6; color: #1A1A1A; background-color: #F5F1E8; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 20px auto; background: #FDFCFA; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.07), 0 2px 4px rgba(0,0,0,0.05); border: 1px solid #E8E4D9; }
        .header { background: #009B95; color: #F5F1E8; padding: 35px 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 28px; font-weight: 700; }
        .content { padding: 40px 35px; background: #FDFCFA; }
        .content h2 { color: #6B5B95; margin-top: 0; font-size: 22px; font-weight: 600; }
        .content p { color: #4A4A4A; margin: 15px 0; }
        .button { display: inline-block; padding: 14px 32px; background: #1A1A1A; color: #F5F1E8; text-decoration: none; border-radius: 8px; margin: 20px 0; font-weight: 600; transition: all 0.3s ease; }
        .button:hover { background: #000000; transform: translateY(-1px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); }
        .footer { background: #F5F1E8; padding: 25px; text-align: center; color: #6B6B6B; font-size: 13px; border-top: 1px solid #E8E4D9; }
        .feature-list { background: #F5F1E8; border-radius: 10px; padding: 25px 30px; margin: 20px 0; border: 1px solid #E8E4D9; }
        .feature-list ul { margin: 0; padding: 0; list-style: none; }
        .feature-list li { margin: 12px 0; color: #1A1A1A; font-size: 15px; padding-left: 8px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎓 Welcome to TrainAI!</h1>
        </div>
        <div class="content">
            <h2>Hi $user_name,</h2>
            <p>Welcome to TrainAI Learning Platform! Your account has been successfully created as a <strong>$role</strong>.</p>
            <p>We're excited to have you join our learning community. Here's what you can do:</p>
            <div class="feature-list">
                <ul>
                    <li>📚 Browse and enroll in courses</li>
                    <li>🎥 Watch video lessons and read PDFs</li>
                    <li>📝 Take exams and earn certificates</li>
                    <li>🏆 Earn points and climb the leaderboard</li>
                    <li>📊 Track your learning progress</li>
                </ul>
            </div>
            <div style="text-align: center;">
                <a href="$login_link" class="button">Start Learning Now</a>
            </div>
            <p style="text-align: center; margin-top: 30px; color: #6B6B6B; font-size: 14px;">
                If you have any questions, feel free to reach out to our support team.
            </p>
        </div>
        <div class="footer">
            <p style="margin: 0 0 8px 0; font-weight: 600; color: #1A1A1A;">&copy; 2025 TrainAI Learning Platform</p>
            <p style="margin: 0;">by Team DebugThugs • Marwadi University, Rajkot, Gujarat</p>
        </div>
    </div>
</body>
</html>
HTML;
    }
    
    /**
     * Get base URL of the application
     */
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }
    
    /**
     * Log email activity
     */
    private function log($message) {
        $log_dir = dirname(EMAIL_LOG_PATH);
        if (!file_exists($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[$timestamp] $message\n";
        file_put_contents(EMAIL_LOG_PATH, $log_message, FILE_APPEND);
    }
}

/**
 * Helper function to get Mailer instance
 */
function getMailer() {
    return new Mailer();
}
?>

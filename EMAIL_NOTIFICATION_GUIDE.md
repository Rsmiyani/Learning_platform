# 📧 Email Notification System - Implementation Guide

## Overview

The TrainAI Learning Platform now includes a comprehensive email notification system that sends automated emails for:
- **Password Reset** - Secure token-based password recovery
- **Welcome Emails** - New user registration confirmation
- **Certificate Earned** - Congratulations on course completion
- **Exam Results** - Pass/fail notifications with scores

---

## 📁 Files Added/Modified

### New Files:
1. **`config/email.php`** - Email configuration and Mailer class
2. **`logs/.gitkeep`** - Email logs directory
3. **`EMAIL_NOTIFICATION_GUIDE.md`** - This documentation

### Modified Files:
1. **`process_forgot_password.php`** - Added password reset email
2. **`process_register.php`** - Added welcome email
3. **`pages/trainee/take-exam.php`** - Added certificate and exam result emails

---

## ⚙️ Configuration

### Email Settings

Edit `config/email.php` to configure email settings:

```php
// Basic Configuration
define('EMAIL_FROM_ADDRESS', 'noreply@trainai.com');
define('EMAIL_FROM_NAME', 'TrainAI Learning Platform');
define('EMAIL_REPLY_TO', 'support@trainai.com');

// Enable/Disable Emails
define('EMAIL_ENABLED', true); // Set to false for testing without sending emails

// SMTP Configuration (Optional - for production)
define('USE_SMTP', false); // Set to true to use SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_ENCRYPTION', 'tls');
```

---

## 🚀 Quick Start

### For Development (Using PHP mail())

1. **Enable PHP mail() function** in XAMPP:
   - Open `php.ini` (XAMPP Control Panel → Apache → Config → php.ini)
   - Find and configure:
     ```ini
     [mail function]
     SMTP=localhost
     smtp_port=25
     sendmail_from=noreply@trainai.com
     ```
   - Restart Apache

2. **Test Mode** (Recommended for development):
   - Set `EMAIL_ENABLED = false` in `config/email.php`
   - Emails will be logged but not sent
   - Check logs in `logs/email.log`

### For Production (Using SMTP)

1. **Get SMTP Credentials**:
   - **Gmail**: Create an App Password (Settings → Security → 2-Step Verification → App Passwords)
   - **SendGrid**: Get API key from dashboard
   - **Mailgun**: Get SMTP credentials from dashboard

2. **Configure SMTP** in `config/email.php`:
   ```php
   define('USE_SMTP', true);
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_USERNAME', 'your-email@gmail.com');
   define('SMTP_PASSWORD', 'your-app-password');
   ```

3. **Install PHPMailer** (for SMTP support):
   ```bash
   cd "d:\XAMPP\htdocs\Learning-Platform - Copy"
   composer require phpmailer/phpmailer
   ```

---

## 📧 Email Types

### 1. Password Reset Email

**Trigger**: User requests password reset via forgot-password.php

**Features**:
- Secure reset link with token
- 1-hour expiration notice
- Security warning if not requested
- Beautiful HTML template

**Code Location**: `process_forgot_password.php` (lines 76-94)

**Example**:
```php
$mailer = getMailer();
$mailer->sendPasswordResetEmail(
    $email,
    $user_name,
    $reset_token,
    $reset_link
);
```

---

### 2. Welcome Email

**Trigger**: New user registration

**Features**:
- Welcome message with user's name
- Feature highlights
- Login link
- Role-specific content

**Code Location**: `process_register.php` (lines 85-93)

**Example**:
```php
$mailer = getMailer();
$mailer->sendWelcomeEmail($email, $first_name, $role);
```

---

### 3. Certificate Email

**Trigger**: Student passes exam (75%+)

**Features**:
- Congratulations message
- Certificate code display
- Direct link to certificate
- Encouragement to continue learning

**Code Location**: `pages/trainee/take-exam.php` (lines 164-191)

**Example**:
```php
$mailer = getMailer();
$mailer->sendCertificateEmail(
    $email,
    $user_name,
    $course_name,
    $certificate_code,
    $certificate_link
);
```

---

### 4. Exam Result Email

**Trigger**: Student completes exam (pass or fail)

**Features**:
- Score display
- Pass/fail status
- Encouragement message
- Next steps guidance

**Code Location**: `pages/trainee/take-exam.php` (lines 198-222)

**Example**:
```php
$mailer = getMailer();
$mailer->sendExamResultEmail(
    $email,
    $user_name,
    $course_name,
    $score_percentage,
    $passed
);
```

---

## 🎨 Email Templates

All email templates are built-in with:
- **Responsive HTML design**
- **Professional styling**
- **Gradient headers**
- **Clear call-to-action buttons**
- **TrainAI branding**
- **Mobile-friendly layout**

### Template Features:
- ✅ Clean, modern design
- ✅ Inline CSS for email client compatibility
- ✅ Fallback plain text versions
- ✅ Consistent branding
- ✅ Print-friendly

---

## 📊 Logging

All email activity is logged to `logs/email.log`:

```
[2025-11-10 11:50:23] Email sent successfully to: user@example.com - Subject: Reset Your TrainAI Password
[2025-11-10 11:51:15] Email disabled. Would have sent to: test@example.com - Subject: Welcome to TrainAI!
```

**Log Format**:
```
[TIMESTAMP] MESSAGE
```

**Log Location**: `logs/email.log`

---

## 🧪 Testing

### Test Without Sending Emails

1. Set `EMAIL_ENABLED = false` in `config/email.php`
2. Perform actions (register, reset password, take exam)
3. Check `logs/email.log` for logged emails
4. Verify email content in logs

### Test With Local Mail Server

1. Install **Papercut SMTP** (Windows) or **MailHog** (Cross-platform)
2. Configure SMTP settings to point to local server
3. View emails in the local mail server UI

### Test With Real Emails

1. Use a test email service like **Mailtrap.io**
2. Configure SMTP settings with Mailtrap credentials
3. Check emails in Mailtrap inbox

---

## 🔧 Troubleshooting

### Emails Not Sending

**Check 1**: Is `EMAIL_ENABLED` set to `true`?
```php
define('EMAIL_ENABLED', true);
```

**Check 2**: Are PHP mail settings configured?
- Open `php.ini`
- Verify SMTP settings
- Restart Apache

**Check 3**: Check error logs
```
logs/email.log
xampp/apache/logs/error.log
```

### SMTP Authentication Failed

**Solution 1**: Use App Password (Gmail)
- Don't use your regular password
- Generate App Password in Google Account settings

**Solution 2**: Enable "Less Secure Apps" (Not recommended)
- Or use OAuth2 authentication

**Solution 3**: Check firewall/antivirus
- May block SMTP connections

### Emails Going to Spam

**Solution 1**: Configure SPF/DKIM records (Production)
**Solution 2**: Use reputable SMTP service (SendGrid, Mailgun)
**Solution 3**: Avoid spam trigger words in subject/body

---

## 🔐 Security Best Practices

1. **Never hardcode credentials** in code
   - Use environment variables
   - Use `.env` file (add to `.gitignore`)

2. **Use App Passwords** for Gmail
   - Don't use main account password
   - Revoke if compromised

3. **Validate email addresses**
   - Already implemented with `filter_var()`

4. **Rate limiting** (Future enhancement)
   - Prevent email spam/abuse
   - Limit emails per user per hour

5. **Secure tokens**
   - Already using `random_bytes()` and SHA-256

---

## 📈 Future Enhancements

### Planned Features:
- [ ] Email templates in database (customizable)
- [ ] Email queue system for bulk sending
- [ ] Email preferences (opt-in/opt-out)
- [ ] Email verification on registration
- [ ] Digest emails (weekly summary)
- [ ] Course enrollment notifications
- [ ] Assignment deadline reminders
- [ ] Admin notification emails

### Advanced Features:
- [ ] Multi-language email support
- [ ] Rich text editor for custom templates
- [ ] Email analytics (open rate, click rate)
- [ ] A/B testing for email content
- [ ] Scheduled email campaigns

---

## 🛠️ Mailer Class API

### Available Methods:

#### `send($to, $subject, $html_body, $plain_body = '')`
Send a custom email

**Parameters**:
- `$to` (string): Recipient email
- `$subject` (string): Email subject
- `$html_body` (string): HTML email content
- `$plain_body` (string): Plain text fallback (optional)

**Returns**: `bool` - Success status

---

#### `sendPasswordResetEmail($to, $user_name, $reset_token, $reset_link)`
Send password reset email

**Parameters**:
- `$to` (string): User email
- `$user_name` (string): User's first name
- `$reset_token` (string): Reset token
- `$reset_link` (string): Full reset URL

**Returns**: `bool` - Success status

---

#### `sendCertificateEmail($to, $user_name, $course_name, $certificate_code, $certificate_link)`
Send certificate earned email

**Parameters**:
- `$to` (string): User email
- `$user_name` (string): User's first name
- `$course_name` (string): Course name
- `$certificate_code` (string): Unique certificate code
- `$certificate_link` (string): Certificate page URL

**Returns**: `bool` - Success status

---

#### `sendExamResultEmail($to, $user_name, $course_name, $score, $passed)`
Send exam result email

**Parameters**:
- `$to` (string): User email
- `$user_name` (string): User's first name
- `$course_name` (string): Course name
- `$score` (float): Score percentage
- `$passed` (bool): Pass/fail status

**Returns**: `bool` - Success status

---

#### `sendWelcomeEmail($to, $user_name, $role)`
Send welcome email to new users

**Parameters**:
- `$to` (string): User email
- `$user_name` (string): User's first name
- `$role` (string): User role (Trainee, Trainer, Admin)

**Returns**: `bool` - Success status

---

## 📝 Usage Examples

### Example 1: Send Custom Email
```php
require_once 'config/email.php';

$mailer = getMailer();
$success = $mailer->send(
    'user@example.com',
    'Custom Subject',
    '<h1>Hello!</h1><p>This is a custom email.</p>',
    'Hello! This is a custom email.'
);

if ($success) {
    echo "Email sent!";
}
```

### Example 2: Send Password Reset
```php
require_once 'config/email.php';

$reset_token = bin2hex(random_bytes(32));
$reset_link = "https://trainai.com/reset-password.php?token=$reset_token";

$mailer = getMailer();
$mailer->sendPasswordResetEmail(
    'user@example.com',
    'John',
    $reset_token,
    $reset_link
);
```

### Example 3: Send Certificate
```php
require_once 'config/email.php';

$mailer = getMailer();
$mailer->sendCertificateEmail(
    'student@example.com',
    'Jane',
    'Introduction to PHP',
    'CERT-ABC123-20251110',
    'https://trainai.com/certificates.php'
);
```

---

## 📞 Support

For issues or questions:
1. Check `logs/email.log` for errors
2. Review this documentation
3. Check PHP error logs
4. Contact Team DebugThugs

---

## ✅ Checklist for Production

Before deploying to production:

- [ ] Configure SMTP with production credentials
- [ ] Set `EMAIL_ENABLED = true`
- [ ] Test all email types
- [ ] Configure SPF/DKIM records
- [ ] Set up email monitoring
- [ ] Add rate limiting
- [ ] Configure proper from/reply-to addresses
- [ ] Test spam score
- [ ] Add unsubscribe links (if required)
- [ ] Backup email logs regularly

---

## 📄 License

Part of TrainAI Learning Platform  
© 2025 Team DebugThugs - Marwadi University

---

**🎓 TrainAI - Empowering Learning Through Technology**

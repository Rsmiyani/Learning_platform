# 📧 Email Notifications - Quick Setup Guide

## ⚡ 5-Minute Setup

### Step 1: Test in Development Mode (Recommended First)

1. **Email is already configured** to log instead of send
2. **Test the system**:
   - Visit: `http://localhost/Learning-Platform - Copy/test-email.php`
   - Enter your email and test each email type
   - Check `logs/email.log` to see logged emails

### Step 2: Enable Email Sending (Optional)

Edit `config/email.php`:

```php
// Change this line:
define('EMAIL_ENABLED', true);  // Set to true to actually send emails
```

### Step 3: Configure PHP Mail (For Local Testing)

**Windows (XAMPP)**:
1. Open XAMPP Control Panel
2. Click Apache → Config → php.ini
3. Find `[mail function]` section
4. Configure:
   ```ini
   SMTP=localhost
   smtp_port=25
   sendmail_from=noreply@trainai.com
   ```
5. Restart Apache

**Note**: PHP mail() may not work on all systems. For production, use SMTP.

---

## 🚀 Production Setup (SMTP)

### Option 1: Gmail (Free, Easy)

1. **Get App Password**:
   - Go to Google Account → Security
   - Enable 2-Step Verification
   - Generate App Password

2. **Configure** in `config/email.php`:
   ```php
   define('USE_SMTP', true);
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'your-email@gmail.com');
   define('SMTP_PASSWORD', 'your-16-char-app-password');
   define('SMTP_ENCRYPTION', 'tls');
   ```

3. **Install PHPMailer**:
   ```bash
   cd "d:\XAMPP\htdocs\Learning-Platform - Copy"
   composer require phpmailer/phpmailer
   ```

### Option 2: SendGrid (Professional)

1. Sign up at sendgrid.com
2. Create API key
3. Configure SMTP settings

### Option 3: Mailgun (Professional)

1. Sign up at mailgun.com
2. Get SMTP credentials
3. Configure SMTP settings

---

## ✅ What's Already Working

### 1. Password Reset Emails ✅
- **Trigger**: User clicks "Forgot Password"
- **File**: `process_forgot_password.php`
- **Features**: Secure token, 1-hour expiry, beautiful template

### 2. Welcome Emails ✅
- **Trigger**: New user registration
- **File**: `process_register.php`
- **Features**: Personalized greeting, feature highlights

### 3. Certificate Emails ✅
- **Trigger**: Student passes exam (75%+)
- **File**: `pages/trainee/take-exam.php`
- **Features**: Certificate code, download link

### 4. Exam Result Emails ✅
- **Trigger**: Student completes any exam
- **File**: `pages/trainee/take-exam.php`
- **Features**: Score display, pass/fail status

---

## 🧪 Testing Checklist

- [ ] Visit `test-email.php` in browser
- [ ] Test password reset email
- [ ] Test welcome email
- [ ] Test certificate email
- [ ] Test exam pass email
- [ ] Test exam fail email
- [ ] Check `logs/email.log` for logs
- [ ] Verify email templates look good
- [ ] Test on mobile device (email client)

---

## 📊 Email Templates Preview

All emails include:
- ✅ Professional HTML design
- ✅ Responsive layout
- ✅ TrainAI branding
- ✅ Clear call-to-action buttons
- ✅ Security notices (where applicable)
- ✅ Footer with contact info

---

## 🔧 Configuration Reference

### Email Settings (config/email.php)

```php
// Basic Settings
EMAIL_FROM_ADDRESS    = 'noreply@trainai.com'
EMAIL_FROM_NAME       = 'TrainAI Learning Platform'
EMAIL_REPLY_TO        = 'support@trainai.com'

// Control
EMAIL_ENABLED         = false  // true = send, false = log only
USE_SMTP             = false  // true = SMTP, false = PHP mail()

// SMTP (if USE_SMTP = true)
SMTP_HOST            = 'smtp.gmail.com'
SMTP_PORT            = 587
SMTP_USERNAME        = 'your-email@gmail.com'
SMTP_PASSWORD        = 'your-app-password'
SMTP_ENCRYPTION      = 'tls'  // or 'ssl'

// Logging
EMAIL_LOG_PATH       = 'logs/email.log'
```

---

## 🐛 Common Issues & Solutions

### Issue: Emails not sending

**Solution 1**: Check if `EMAIL_ENABLED = true`  
**Solution 2**: Check `logs/email.log` for errors  
**Solution 3**: Verify PHP mail() is configured  
**Solution 4**: Try SMTP instead of PHP mail()

### Issue: Emails go to spam

**Solution 1**: Use reputable SMTP service (SendGrid, Mailgun)  
**Solution 2**: Configure SPF/DKIM records (production)  
**Solution 3**: Avoid spam trigger words

### Issue: Gmail SMTP not working

**Solution 1**: Use App Password, not regular password  
**Solution 2**: Enable "Less secure apps" (not recommended)  
**Solution 3**: Check firewall/antivirus settings

---

## 📝 Next Steps

1. **Test locally** with `EMAIL_ENABLED = false`
2. **Review email templates** in `config/email.php`
3. **Customize** from address and branding
4. **Set up SMTP** for production
5. **Test with real emails** before going live
6. **Monitor** `logs/email.log` regularly

---

## 📚 Full Documentation

For detailed information, see:
- **EMAIL_NOTIFICATION_GUIDE.md** - Complete documentation
- **config/email.php** - Source code and templates
- **test-email.php** - Interactive testing tool

---

## 🎯 Production Checklist

Before deploying:

- [ ] Set `EMAIL_ENABLED = true`
- [ ] Configure SMTP with production credentials
- [ ] Test all email types
- [ ] Update from/reply-to addresses
- [ ] Set up SPF/DKIM records
- [ ] Add email monitoring
- [ ] Test spam score
- [ ] Backup email logs

---

## 💡 Pro Tips

1. **Start with logging** (`EMAIL_ENABLED = false`) to test without sending
2. **Use Mailtrap.io** for safe testing with real SMTP
3. **Monitor logs** regularly for failed sends
4. **Keep credentials secure** - never commit to git
5. **Use environment variables** for production config

---

## 📞 Support

**Email System Working?** ✅  
**Need Help?** Check logs or contact Team DebugThugs

**Test URL**: http://localhost/Learning-Platform - Copy/test-email.php

---

**🎓 TrainAI - Now with Email Notifications!**

© 2025 Team DebugThugs - Marwadi University

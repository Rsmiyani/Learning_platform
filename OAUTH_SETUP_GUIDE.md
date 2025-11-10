# OAuth Setup Guide for Google and GitHub

This guide will help you set up OAuth authentication for Google and GitHub in your TrainAI Learning Platform.

## Prerequisites

- Access to Google Cloud Console (for Google OAuth)
- GitHub account (for GitHub OAuth)
- Your application running on a URL (localhost works for development)

---

## Step 1: Database Setup

First, run the SQL migration to add OAuth fields:

```sql
-- Run this SQL in your database
ALTER TABLE `users` 
ADD COLUMN `oauth_provider` VARCHAR(20) NULL DEFAULT NULL AFTER `password`,
ADD COLUMN `oauth_id` VARCHAR(255) NULL DEFAULT NULL AFTER `oauth_provider`,
ADD INDEX `idx_oauth` (`oauth_provider`, `oauth_id`);

ALTER TABLE `users` MODIFY COLUMN `password` VARCHAR(255) NULL DEFAULT NULL;
```

Or import the file: `database/add_oauth_fields.sql`

---

## Step 2: Google OAuth Setup

### 2.1 Create Google OAuth Credentials

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Enable the **Google+ API**:
   - Go to **APIs & Services** > **Library**
   - Search for "Google+ API" and enable it
4. Create OAuth 2.0 credentials:
   - Go to **APIs & Services** > **Credentials**
   - Click **Create Credentials** > **OAuth client ID**
   - Choose **Web application**
   - Add authorized redirect URI:
     ```
     http://localhost/Learning-Platform%20-%20Copy/oauth/google-callback.php
     ```
     (Replace with your actual URL for production)
   - Copy the **Client ID** and **Client Secret**

### 2.2 Update Configuration

Edit `config/oauth.php` and replace:
```php
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');
define('GOOGLE_REDIRECT_URI', 'http://localhost/Learning-Platform%20-%20Copy/oauth/google-callback.php');
```

---

## Step 3: GitHub OAuth Setup

### 3.1 Create GitHub OAuth App

1. Go to GitHub Settings:
   - Click your profile > **Settings**
   - Go to **Developer settings** > **OAuth Apps**
   - Click **New OAuth App**
2. Fill in the application details:
   - **Application name**: TrainAI Learning Platform
   - **Homepage URL**: `http://localhost/Learning-Platform%20-%20Copy`
   - **Authorization callback URL**: 
     ```
     http://localhost/Learning-Platform%20-%20Copy/oauth/github-callback.php
     ```
   - Click **Register application**
3. Copy the **Client ID**
4. Generate a **Client Secret**:
   - Click **Generate a new client secret**
   - Copy the secret (you won't see it again!)

### 3.2 Update Configuration

Edit `config/oauth.php` and replace:
```php
define('GITHUB_CLIENT_ID', 'YOUR_GITHUB_CLIENT_ID');
define('GITHUB_CLIENT_SECRET', 'YOUR_GITHUB_CLIENT_SECRET');
define('GITHUB_REDIRECT_URI', 'http://localhost/Learning-Platform%20-%20Copy/oauth/github-callback.php');
```

---

## Step 4: Update Redirect URIs for Production

When deploying to production, update the redirect URIs in:

1. **Google Cloud Console**: Add your production callback URL
2. **GitHub OAuth App**: Update the callback URL
3. **config/oauth.php**: Update `GOOGLE_REDIRECT_URI` and `GITHUB_REDIRECT_URI`

Example for production:
```php
define('GOOGLE_REDIRECT_URI', 'https://yourdomain.com/oauth/google-callback.php');
define('GITHUB_REDIRECT_URI', 'https://yourdomain.com/oauth/github-callback.php');
```

---

## Step 5: Test OAuth Login

1. Make sure your database migration is applied
2. Update `config/oauth.php` with your credentials
3. Go to the login page
4. Click "Google" or "GitHub" button
5. You should be redirected to the OAuth provider
6. After authorization, you'll be redirected back and logged in

---

## Troubleshooting

### "OAuth is not configured" error
- Make sure you've updated `config/oauth.php` with your actual credentials
- Check that client IDs and secrets are correct

### "Invalid OAuth state" error
- This usually happens if sessions aren't working properly
- Make sure PHP sessions are enabled
- Check that cookies are allowed in your browser

### Redirect URI mismatch
- Make sure the redirect URI in `config/oauth.php` exactly matches the one in Google/GitHub console
- URL encoding matters: spaces should be `%20`, not `+`

### User creation fails
- Check database permissions
- Make sure the `users` table has the OAuth fields added
- Check PHP error logs for detailed error messages

---

## Security Notes

1. **Never commit** `config/oauth.php` with real credentials to version control
2. Add `config/oauth.php` to `.gitignore` if it contains secrets
3. Use environment variables for production (recommended)
4. Keep your OAuth secrets secure

---

## Features

✅ **Automatic Account Creation**: New users are automatically created with role 'trainee'  
✅ **Account Linking**: Existing users can link their OAuth accounts  
✅ **CSRF Protection**: State tokens prevent CSRF attacks  
✅ **Secure Sessions**: OAuth tokens are never stored in the database  

---

## Support

For issues or questions, check the error logs in your PHP error log file or contact the development team.


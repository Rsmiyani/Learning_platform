<?php
/**
 * Google OAuth Login Handler
 * Initiates Google OAuth flow
 */

require_once '../config/oauth.php';
require_once '../config/database.php';
initSession();

// Generate state token for CSRF protection
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
$_SESSION['oauth_provider'] = 'google';

// Check if credentials are configured
if (GOOGLE_CLIENT_ID === 'YOUR_GOOGLE_CLIENT_ID' || empty(GOOGLE_CLIENT_ID)) {
    $_SESSION['error'] = 'Google OAuth is not configured. Please contact administrator.';
    header('Location: ../login.php');
    exit;
}

// Build authorization URL
$params = array(
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => GOOGLE_SCOPES,
    'state' => $state,
    'access_type' => 'online',
    'prompt' => 'select_account'
);

$auth_url = GOOGLE_AUTH_URL . '?' . http_build_query($params);

// Redirect to Google
header('Location: ' . $auth_url);
exit;
?>


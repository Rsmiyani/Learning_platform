<?php
/**
 * GitHub OAuth Login Handler
 * Initiates GitHub OAuth flow
 */

require_once '../config/oauth.php';
require_once '../config/database.php';
initSession();

// Generate state token for CSRF protection
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;
$_SESSION['oauth_provider'] = 'github';

// Check if credentials are configured
if (GITHUB_CLIENT_ID === 'YOUR_GITHUB_CLIENT_ID' || empty(GITHUB_CLIENT_ID)) {
    $_SESSION['error'] = 'GitHub OAuth is not configured. Please contact administrator.';
    header('Location: ../login.php');
    exit;
}

// Build authorization URL
$params = array(
    'client_id' => GITHUB_CLIENT_ID,
    'redirect_uri' => GITHUB_REDIRECT_URI,
    'scope' => GITHUB_SCOPES,
    'state' => $state
);

$auth_url = GITHUB_AUTH_URL . '?' . http_build_query($params);

// Redirect to GitHub
header('Location: ' . $auth_url);
exit;
?>


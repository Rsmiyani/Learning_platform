<?php
/**
 * Google OAuth Callback Handler
 * Handles the OAuth callback from Google
 */

require_once '../config/oauth.php';
require_once '../config/database.php';
initSession();

// Verify state token (CSRF protection)
if (!isset($_GET['state']) || !isset($_SESSION['oauth_state']) || $_GET['state'] !== $_SESSION['oauth_state']) {
    $_SESSION['error'] = 'Invalid OAuth state. Please try again.';
    header('Location: ../login.php');
    exit;
}

// Check for error from Google
if (isset($_GET['error'])) {
    $_SESSION['error'] = 'OAuth error: ' . htmlspecialchars($_GET['error']);
    header('Location: ../login.php');
    exit;
}

// Get authorization code
if (!isset($_GET['code'])) {
    $_SESSION['error'] = 'Authorization code not received.';
    header('Location: ../login.php');
    exit;
}

$code = $_GET['code'];

try {
    // Exchange code for access token
    $token_params = array(
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    );

    $ch = curl_init(GOOGLE_TOKEN_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($token_params));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
    
    $token_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        throw new Exception('Failed to exchange code for token');
    }

    $token_data = json_decode($token_response, true);
    if (!isset($token_data['access_token'])) {
        throw new Exception('Access token not received');
    }

    $access_token = $token_data['access_token'];

    // Get user info from Google
    $ch = curl_init(GOOGLE_USERINFO_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token));
    
    $user_response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        throw new Exception('Failed to get user info');
    }

    $user_data = json_decode($user_response, true);
    
    if (!isset($user_data['email']) || !isset($user_data['id'])) {
        throw new Exception('Invalid user data received');
    }

    $email = $user_data['email'];
    $oauth_id = $user_data['id'];
    $first_name = $user_data['given_name'] ?? '';
    $last_name = $user_data['family_name'] ?? '';
    $name = $user_data['name'] ?? ($first_name . ' ' . $last_name);
    
    // Split name if first/last not provided
    if (empty($first_name) && !empty($name)) {
        $name_parts = explode(' ', $name, 2);
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? $name_parts[1] : '';
    }

    // Connect to database
    $pdo = getDBConnection();

    // Check if user exists by email or OAuth ID
    $stmt = $pdo->prepare("
        SELECT user_id, first_name, last_name, email, role, status, oauth_provider, oauth_id 
        FROM users 
        WHERE email = ? OR (oauth_provider = 'google' AND oauth_id = ?)
    ");
    $stmt->execute([$email, $oauth_id]);
    $user = $stmt->fetch();

    if ($user) {
        // User exists - update OAuth info if needed and login
        if ($user['oauth_provider'] !== 'google' || $user['oauth_id'] !== $oauth_id) {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET oauth_provider = 'google', oauth_id = ?, last_login = NOW()
                WHERE user_id = ?
            ");
            $stmt->execute([$oauth_id, $user['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
            $stmt->execute([$user['user_id']]);
        }

        // Check if account is active
        if ($user['status'] !== 'active') {
            $_SESSION['error'] = "Your account is inactive. Please contact support.";
            header('Location: ../login.php');
            exit;
        }

        // Set session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['first_name'] = $user['first_name'];
        $_SESSION['last_name'] = $user['last_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        // Clear OAuth state
        unset($_SESSION['oauth_state']);
        unset($_SESSION['oauth_provider']);

        // Redirect based on role
        switch ($user['role']) {
            case 'admin':
                header('Location: ../dashboard/admin/index.php');
                break;
            case 'trainer':
                header('Location: ../dashboard/trainer/index.php');
                break;
            case 'trainee':
                header('Location: ../dashboard/trainee/index.php');
                break;
            default:
                header('Location: ../dashboard/trainee/index.php');
        }
        exit;

    } else {
        // New user - create account with default role 'trainee'
        $stmt = $pdo->prepare("
            INSERT INTO users (first_name, last_name, email, oauth_provider, oauth_id, role, status, created_at, last_login)
            VALUES (?, ?, ?, 'google', ?, 'trainee', 'active', NOW(), NOW())
        ");
        $stmt->execute([$first_name, $last_name, $email, $oauth_id]);
        
        $user_id = $pdo->lastInsertId();

        // Initialize user points
        $stmt = $pdo->prepare("
            INSERT INTO user_points (user_id, total_points, level, last_updated)
            VALUES (?, 0, 1, NOW())
        ");
        $stmt->execute([$user_id]);

        // Set session
        $_SESSION['user_id'] = $user_id;
        $_SESSION['first_name'] = $first_name;
        $_SESSION['last_name'] = $last_name;
        $_SESSION['email'] = $email;
        $_SESSION['role'] = 'trainee';
        $_SESSION['logged_in'] = true;
        $_SESSION['success'] = 'Account created successfully with Google!';

        // Clear OAuth state
        unset($_SESSION['oauth_state']);
        unset($_SESSION['oauth_provider']);

        // Redirect to trainee dashboard
        header('Location: ../dashboard/trainee/index.php');
        exit;
    }

} catch (Exception $e) {
    error_log("Google OAuth error: " . $e->getMessage());
    $_SESSION['error'] = 'OAuth authentication failed. Please try again.';
    header('Location: ../login.php');
    exit;
}
?>


<?php
/**
 * Diagnostic script to test password reset token
 * Access this file directly in browser: test-reset-token.php?token=YOUR_TOKEN
 */

require_once 'config/database.php';
initSession();

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die("Please provide a token in URL: test-reset-token.php?token=YOUR_TOKEN");
}

echo "<h2>Password Reset Token Diagnostic</h2>";
echo "<pre>";

echo "Token from URL: " . htmlspecialchars($token) . "\n";
echo "Token length: " . strlen($token) . "\n";
echo "Token decoded: " . htmlspecialchars(rawurldecode($token)) . "\n";
echo "Token decoded length: " . strlen(rawurldecode($token)) . "\n\n";

try {
    $pdo = getDBConnection();
    
    // Check all tokens in database
    echo "=== All tokens in password_resets table ===\n";
    $stmt = $pdo->query("SELECT reset_id, user_id, email, token, LENGTH(token) as token_len, used, expires_at, created_at FROM password_resets ORDER BY created_at DESC LIMIT 10");
    $all_tokens = $stmt->fetchAll();
    
    if (empty($all_tokens)) {
        echo "No tokens found in database!\n";
    } else {
        foreach ($all_tokens as $row) {
            echo "ID: {$row['reset_id']}, User: {$row['user_id']}, Email: {$row['email']}\n";
            echo "  Token: " . substr($row['token'], 0, 20) . "... (length: {$row['token_len']})\n";
            echo "  Used: {$row['used']}, Expires: {$row['expires_at']}\n";
            echo "  Match: " . ($row['token'] === $token ? "YES" : "NO") . "\n\n";
        }
    }
    
    // Try exact match
    echo "\n=== Trying exact match ===\n";
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->execute([$token]);
    $exact_match = $stmt->fetch();
    
    if ($exact_match) {
        echo "EXACT MATCH FOUND!\n";
        print_r($exact_match);
    } else {
        echo "No exact match found.\n";
    }
    
    // Try decoded match
    $decoded_token = rawurldecode($token);
    echo "\n=== Trying decoded match ===\n";
    echo "Decoded token: " . $decoded_token . "\n";
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ?");
    $stmt->execute([$decoded_token]);
    $decoded_match = $stmt->fetch();
    
    if ($decoded_match) {
        echo "DECODED MATCH FOUND!\n";
        print_r($decoded_match);
    } else {
        echo "No decoded match found.\n";
    }
    
    // Check if any token starts with our token
    echo "\n=== Checking partial matches ===\n";
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token LIKE ?");
    $stmt->execute([substr($token, 0, 20) . '%']);
    $partial_matches = $stmt->fetchAll();
    
    if ($partial_matches) {
        echo "Partial matches found:\n";
        foreach ($partial_matches as $match) {
            echo "  Token: " . $match['token'] . " (length: " . strlen($match['token']) . ")\n";
        }
    } else {
        echo "No partial matches found.\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "</pre>";
?>


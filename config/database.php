<?php
// Database Configuration for ai-train1
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ai-train1');

// Create PDO connection (secure)
function getDBConnection() {
    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        return $pdo;
        
    } catch (PDOException $e) {
        error_log("Database connection error: " . $e->getMessage());
        die("Database connection failed. Please try again later.");
    }
}

// Start session
function initSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Calculate user level based on total points
// Level progression: Every 100 points = 1 level
// Level 1: 0-99 points
// Level 2: 100-199 points
// Level 3: 200-299 points, etc.
function calculateLevel($total_points) {
    return floor($total_points / 100) + 1;
}

// Update user level based on current points
function updateUserLevel($pdo, $user_id) {
    try {
        // Get current total points
        $stmt = $pdo->prepare("SELECT total_points FROM user_points WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch();
        
        if ($result) {
            $total_points = $result['total_points'];
            $new_level = calculateLevel($total_points);
            
            // Update level
            $stmt = $pdo->prepare("UPDATE user_points SET level = ? WHERE user_id = ?");
            $stmt->execute([$new_level, $user_id]);
            
            return $new_level;
        }
    } catch (PDOException $e) {
        error_log("Level update error: " . $e->getMessage());
    }
    return 1;
}

// Award achievement when milestone is reached (called only when points are added)
function awardAchievementIfEligible($pdo, $user_id, $new_total_points) {
    try {
        // Define achievement milestones
        $milestones = [
            100 => 1,   // First Step
            200 => 4,   // 100% Champion
            300 => 2,   // Week Warrior
            400 => 6,   // Speed Reader
            500 => 5,   // Learning Enthusiast
            600 => 3,   // Month Master
            700 => 7,   // Time Master
            800 => 8,   // Early Bird
        ];
        
        // Check if user just crossed any milestone
        foreach ($milestones as $points_required => $achievement_id) {
            // Only check if user has exactly this amount or just crossed it
            if ($new_total_points >= $points_required) {
                // Check if user already has this achievement
                $stmt = $pdo->prepare("SELECT * FROM user_achievements WHERE user_id = ? AND achievement_id = ?");
                $stmt->execute([$user_id, $achievement_id]);
                
                if ($stmt->rowCount() == 0) {
                    // Award the achievement
                    $stmt = $pdo->prepare("INSERT INTO user_achievements (user_id, achievement_id, earned_at) VALUES (?, ?, NOW())");
                    $stmt->execute([$user_id, $achievement_id]);
                    
                    // Get achievement details
                    $stmt = $pdo->prepare("SELECT achievement_name FROM achievements WHERE achievement_id = ?");
                    $stmt->execute([$achievement_id]);
                    $achievement = $stmt->fetch();
                    
                    // Send notification
                    $stmt = $pdo->prepare("
                        INSERT INTO notifications (user_id, notification_type, title, message, created_at)
                        VALUES (?, 'achievement_earned', ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $user_id,
                        'Achievement Unlocked! 🏆',
                        "You earned '{$achievement['achievement_name']}' achievement!"
                    ]);
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Achievement award error: " . $e->getMessage());
    }
}
?>

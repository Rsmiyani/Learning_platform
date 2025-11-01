<?php
// Script to remove duplicate achievements from database
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    echo "<h2>🧹 Cleaning up duplicate achievements...</h2>";
    echo "<hr>";
    
    // Get all users who have achievements
    $stmt = $pdo->prepare("SELECT DISTINCT user_id FROM user_achievements");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    $total_removed = 0;
    
    foreach ($users as $user) {
        $user_id = $user['user_id'];
        
        echo "<h3>User ID: $user_id</h3>";
        
        // For each achievement_id, keep only the earliest one (first earned)
        $stmt = $pdo->prepare("
            SELECT achievement_id, MIN(user_achievement_id) as keep_id, COUNT(*) as count
            FROM user_achievements
            WHERE user_id = ?
            GROUP BY achievement_id
            HAVING count > 1
        ");
        $stmt->execute([$user_id]);
        $duplicates = $stmt->fetchAll();
        
        if (count($duplicates) > 0) {
            foreach ($duplicates as $dup) {
                $achievement_id = $dup['achievement_id'];
                $keep_id = $dup['keep_id'];
                $duplicate_count = $dup['count'] - 1; // Subtract 1 because we're keeping one
                
                // Get achievement name
                $stmt = $pdo->prepare("SELECT achievement_name FROM achievements WHERE achievement_id = ?");
                $stmt->execute([$achievement_id]);
                $ach_name = $stmt->fetch()['achievement_name'] ?? 'Unknown';
                
                // Delete all duplicates except the one we're keeping
                $stmt = $pdo->prepare("
                    DELETE FROM user_achievements 
                    WHERE user_id = ? 
                    AND achievement_id = ? 
                    AND user_achievement_id != ?
                ");
                $stmt->execute([$user_id, $achievement_id, $keep_id]);
                
                $removed = $stmt->rowCount();
                $total_removed += $removed;
                
                echo "✓ Removed <strong>$removed duplicate(s)</strong> of '<strong>$ach_name</strong>'<br>";
            }
        } else {
            echo "✓ No duplicates found for this user<br>";
        }
        
        echo "<br>";
    }
    
    echo "<hr>";
    echo "<h2 style='color: green;'>✅ Cleanup Complete!</h2>";
    echo "<p><strong>Total duplicates removed: $total_removed</strong></p>";
    echo "<p><a href='pages/trainee/achievements.php' style='padding: 10px 20px; background: #4a9d9a; color: white; text-decoration: none; border-radius: 5px;'>Go to Achievements Page</a></p>";
    echo "<p><a href='dashboard/trainee/' style='padding: 10px 20px; background: #6B5B95; color: white; text-decoration: none; border-radius: 5px;'>Go to Dashboard</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>

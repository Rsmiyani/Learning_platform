<?php
// One-time script to update all user levels based on current points
require_once 'config/database.php';

try {
    $pdo = getDBConnection();
    
    // Get all users with points
    $stmt = $pdo->prepare("SELECT user_id, total_points FROM user_points");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    $updated = 0;
    foreach ($users as $user) {
        $user_id = $user['user_id'];
        $total_points = $user['total_points'];
        
        // Calculate correct level
        $correct_level = calculateLevel($total_points);
        
        // Update level
        $stmt = $pdo->prepare("UPDATE user_points SET level = ? WHERE user_id = ?");
        $stmt->execute([$correct_level, $user_id]);
        
        $updated++;
        echo "Updated User ID {$user_id}: {$total_points} points → Level {$correct_level}<br>";
    }
    
    echo "<br><strong>✅ Successfully updated {$updated} user(s)!</strong><br>";
    echo "<a href='dashboard/trainee/'>Go to Dashboard</a>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

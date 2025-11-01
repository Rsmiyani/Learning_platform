<?php
require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'trainee') {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'] ?? 'User';

try {
    $pdo = getDBConnection();
    
    // Get user achievements
    $stmt = $pdo->prepare("
        SELECT ua.earned_at, a.achievement_name, a.achievement_icon, a.description, a.points_reward
        FROM user_achievements ua
        JOIN achievements a ON ua.achievement_id = a.achievement_id
        WHERE ua.user_id = ?
        ORDER BY ua.earned_at DESC
    ");
    $stmt->execute([$user_id]);
    $achievements = $stmt->fetchAll();
    
    // Get all achievements for comparison
    $stmt = $pdo->prepare("SELECT * FROM achievements ORDER BY achievement_name");
    $stmt->execute();
    $all_achievements = $stmt->fetchAll();
    
} catch (Exception $e) {
    $achievements = [];
    $all_achievements = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Achievements - TrainAI</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="../../dashboard/trainee/" class="sidebar-logo">
                <span class="logo-icon">🎓</span>
                <span class="logo-text">TrainAI</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="../../dashboard/trainee/" class="nav-item">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="../../pages/trainee/my-courses.php" class="nav-item">
                <span class="nav-icon">📚</span>
                <span class="nav-text">My Courses</span>
            </a>
            <a href="../../pages/trainee/achievements.php" class="nav-item active">
                <span class="nav-icon">🏆</span>
                <span class="nav-text">Achievements</span>
            </a>
            <a href="../../pages/trainee/certificates.php" class="nav-item">
                <span class="nav-icon">🎓</span>
                <span class="nav-text">Certificates</span>
            </a>
            <a href="../../pages/trainee/analytics.php" class="nav-item">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Analytics</span>
            </a>
            <a href="../../pages/trainee/settings.php" class="nav-item">
                <span class="nav-icon">⚙️</span>
                <span class="nav-text">Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="../../logout.php" class="logout-btn">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle">☰</button>
                <h2 style="margin: 0; color: var(--text-primary);">🏆 Achievements</h2>
            </div>
            <div class="topbar-right">
                <div class="user-profile">
                    <div class="user-avatar"><?php echo strtoupper(substr($first_name, 0, 1)); ?></div>
                    <div class="user-info">
                        <p class="user-name"><?php echo htmlspecialchars($first_name); ?></p>
                        <p class="user-level">Trainee</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-container">
            <div class="welcome-banner">
                <div class="banner-content">
                    <h1>Your Achievements</h1>
                    <p>Unlock badges and earn rewards by completing milestones</p>
                </div>
            </div>

            <!-- Earned Achievements -->
            <div style="margin-top: 30px;">
                <h2 style="margin-bottom: 20px; color: var(--text-primary);">🌟 Earned Achievements (<?php echo count($achievements); ?>)</h2>
                
                <?php if (count($achievements) > 0): ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-bottom: 40px;">
                        <?php foreach ($achievements as $ach): ?>
                        <div class="card premium-card" style="border-left: 4px solid var(--accent-primary);">
                            <div style="padding: 20px; text-align: center;">
                                <div style="font-size: 48px; margin-bottom: 10px;"><?php echo htmlspecialchars($ach['achievement_icon']); ?></div>
                                <h3 style="margin: 10px 0; color: var(--text-primary);">
                                    <?php echo htmlspecialchars($ach['achievement_name']); ?>
                                </h3>
                                <p style="margin: 10px 0; color: var(--text-muted); font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($ach['description']); ?>
                                </p>
                                <p style="margin: 10px 0; color: var(--accent-primary); font-weight: 600;">
                                    +<?php echo htmlspecialchars($ach['points_reward']); ?> Points
                                </p>
                                <p style="margin: 10px 0; color: var(--text-muted); font-size: 0.75rem;">
                                    Earned: <?php echo date('M d, Y', strtotime($ach['earned_at'])); ?>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- All Achievements -->
                <h2 style="margin: 40px 0 20px 0; color: var(--text-primary);">🎯 All Achievements</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
                    <?php foreach ($all_achievements as $ach): 
                        $is_earned = count(array_filter($achievements, fn($a) => $a['achievement_name'] === $ach['achievement_name'])) > 0;
                    ?>
                    <div class="card premium-card" style="opacity: <?php echo $is_earned ? '1' : '0.6'; ?>; border: 2px solid <?php echo $is_earned ? 'var(--accent-primary)' : 'var(--border-color)'; ?>;">
                        <div style="padding: 20px; text-align: center;">
                            <div style="font-size: 48px; margin-bottom: 10px; filter: <?php echo $is_earned ? 'none' : 'grayscale(100%)'; ?>;">
                                <?php echo htmlspecialchars($ach['achievement_icon']); ?>
                            </div>
                            <h3 style="margin: 10px 0; color: var(--text-primary);">
                                <?php echo htmlspecialchars($ach['achievement_name']); ?>
                            </h3>
                            <p style="margin: 10px 0; color: var(--text-muted); font-size: 0.875rem;">
                                <?php echo htmlspecialchars($ach['description']); ?>
                            </p>
                            <p style="margin: 10px 0; color: var(--accent-primary); font-weight: 600;">
                                +<?php echo htmlspecialchars($ach['points_reward']); ?> Points
                            </p>
                            <span style="display: inline-block; margin-top: 10px; padding: 6px 12px; background: <?php echo $is_earned ? 'var(--accent-green)' : 'var(--border-color)'; ?>; color: <?php echo $is_earned ? 'white' : 'var(--text-muted)'; ?>; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                                <?php echo $is_earned ? '✓ Earned' : 'Locked'; ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>

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
    
    // Auto-update user level
    updateUserLevel($pdo, $user_id);
    
    // Get user stats
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM course_enrollments WHERE user_id = ?) as courses_enrolled,
            (SELECT COALESCE(SUM(hours_studied), 0) FROM study_logs WHERE user_id = ?) as total_hours,
            (SELECT COALESCE(total_points, 0) FROM user_points WHERE user_id = ?) as user_points,
            (SELECT COALESCE(level, 1) FROM user_points WHERE user_id = ?) as user_level,
            (SELECT COUNT(*) FROM user_achievements WHERE user_id = ?) as achievements_earned,
            (SELECT COUNT(*) FROM user_certificates WHERE user_id = ?) as certificates
    ");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
    $stats = $stmt->fetch();
    
} catch (Exception $e) {
    $stats = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - TrainAI</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
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
            <a href="../../pages/trainee/achievements.php" class="nav-item">
                <span class="nav-icon">🏆</span>
                <span class="nav-text">Achievements</span>
            </a>
            <a href="../../pages/trainee/certificates.php" class="nav-item">
                <span class="nav-icon">🎓</span>
                <span class="nav-text">Certificates</span>
            </a>
            <a href="../../pages/trainee/analytics.php" class="nav-item active">
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
                <h2 style="margin: 0; color: var(--text-primary);">📊 Analytics</h2>
            </div>
            <div class="topbar-right">
                <div class="user-profile">
                    <div class="user-avatar"><?php echo strtoupper(substr($first_name, 0, 1)); ?></div>
                    <div class="user-info">
                        <p class="user-name"><?php echo htmlspecialchars($first_name); ?></p>
                        <p class="user-level">Level <?php echo htmlspecialchars($stats['user_level'] ?? 1); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="dashboard-container">
            <div class="welcome-banner">
                <div class="banner-content">
                    <h1>Your Learning Analytics</h1>
                    <p>Track your progress and achievements</p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 30px;">
                <div class="card premium-card">
                    <div style="padding: 25px; text-align: center;">
                        <div style="font-size: 40px; margin-bottom: 10px;">📚</div>
                        <p style="margin: 10px 0; color: var(--text-muted); font-size: 0.875rem;">Courses Enrolled</p>
                        <h3 style="margin: 10px 0; font-size: 2.5rem; color: var(--accent-primary);">
                            <?php echo htmlspecialchars($stats['courses_enrolled'] ?? 0); ?>
                        </h3>
                    </div>
                </div>

                <div class="card premium-card">
                    <div style="padding: 25px; text-align: center;">
                        <div style="font-size: 40px; margin-bottom: 10px;">⏱️</div>
                        <p style="margin: 10px 0; color: var(--text-muted); font-size: 0.875rem;">Learning Hours</p>
                        <h3 style="margin: 10px 0; font-size: 2.5rem; color: var(--accent-primary);">
                            <?php echo round($stats['total_hours'] ?? 0, 1); ?>h
                        </h3>
                    </div>
                </div>

                <div class="card premium-card">
                    <div style="padding: 25px; text-align: center;">
                        <div style="font-size: 40px; margin-bottom: 10px;">⭐</div>
                        <p style="margin: 10px 0; color: var(--text-muted); font-size: 0.875rem;">Points</p>
                        <h3 style="margin: 10px 0; font-size: 2.5rem; color: var(--accent-primary);">
                            <?php echo htmlspecialchars($stats['user_points'] ?? 0); ?>
                        </h3>
                    </div>
                </div>

                <div class="card premium-card">
                    <div style="padding: 25px; text-align: center;">
                        <div style="font-size: 40px; margin-bottom: 10px;">🏆</div>
                        <p style="margin: 10px 0; color: var(--text-muted); font-size: 0.875rem;">Achievements</p>
                        <h3 style="margin: 10px 0; font-size: 2.5rem; color: var(--accent-primary);">
                            <?php echo htmlspecialchars($stats['achievements_earned'] ?? 0); ?>
                        </h3>
                    </div>
                </div>

                <div class="card premium-card">
                    <div style="padding: 25px; text-align: center;">
                        <div style="font-size: 40px; margin-bottom: 10px;">🎓</div>
                        <p style="margin: 10px 0; color: var(--text-muted); font-size: 0.875rem;">Certificates</p>
                        <h3 style="margin: 10px 0; font-size: 2.5rem; color: var(--accent-primary);">
                            <?php echo htmlspecialchars($stats['certificates'] ?? 0); ?>
                        </h3>
                    </div>
                </div>

                <div class="card premium-card">
                    <div style="padding: 25px; text-align: center;">
                        <div style="font-size: 40px; margin-bottom: 10px;">🎯</div>
                        <p style="margin: 10px 0; color: var(--text-muted); font-size: 0.875rem;">Current Level</p>
                        <h3 style="margin: 10px 0; font-size: 2.5rem; color: var(--accent-primary);">
                            <?php echo htmlspecialchars($stats['user_level'] ?? 1); ?>
                        </h3>
                    </div>
                </div>
            </div>

            <!-- Chart -->
            <div class="card premium-card" style="margin-top: 30px;">
                <div style="padding: 20px; border-bottom: 1px solid var(--border-color);">
                    <h2 style="margin: 0; color: var(--text-primary);">Learning Progress</h2>
                </div>
                <div style="padding: 20px; position: relative; height: 300px;">
                    <canvas id="progressChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('progressChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Courses Enrolled', 'Hours Studied', 'Points Earned'],
                    datasets: [{
                        data: [
                            <?php echo htmlspecialchars($stats['courses_enrolled'] ?? 0); ?>,
                            <?php echo round($stats['total_hours'] ?? 0); ?>,
                            <?php echo htmlspecialchars($stats['user_points'] ?? 0); ?>
                        ],
                        backgroundColor: [
                            '#6B5B95',
                            '#009B95',
                            '#F59E0B'
                        ],
                        borderColor: 'white',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>

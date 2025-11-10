<?php
// Trainee Sidebar Navigation - Consistent across all pages
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar Navigation -->
<div class="sidebar">
    <div class="sidebar-header">
        <a href="../../dashboard/trainee/" class="sidebar-logo">
            <span class="logo-icon">🎓</span>
            <span class="logo-text">TrainAI</span>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <a href="../../dashboard/trainee/" class="nav-item <?php echo ($current_page == 'index.php' && strpos($_SERVER['PHP_SELF'], 'dashboard/trainee') !== false) ? 'active' : ''; ?>">
            <span class="nav-icon">🏠</span>
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="../../pages/trainee/my-courses.php" class="nav-item <?php echo ($current_page == 'my-courses.php') ? 'active' : ''; ?>">
            <span class="nav-icon">📚</span>
            <span class="nav-text">My Courses</span>
        </a>
        <a href="../../pages/trainee/achievements.php" class="nav-item <?php echo ($current_page == 'achievements.php') ? 'active' : ''; ?>">
            <span class="nav-icon">🏆</span>
            <span class="nav-text">Achievements</span>
        </a>
        <a href="../../pages/trainee/certificates.php" class="nav-item <?php echo ($current_page == 'certificates.php') ? 'active' : ''; ?>">
            <span class="nav-icon">🎓</span>
            <span class="nav-text">Certificates</span>
        </a>
        <a href="../../pages/trainee/analytics.php" class="nav-item <?php echo ($current_page == 'analytics.php') ? 'active' : ''; ?>">
            <span class="nav-icon">📊</span>
            <span class="nav-text">Analytics</span>
        </a>
        <a href="../../pages/trainee/become-trainer.php" class="nav-item <?php echo ($current_page == 'become-trainer.php') ? 'active' : ''; ?>">
            <span class="nav-icon">👨‍🏫</span>
            <span class="nav-text">Become Trainer</span>
        </a>
        <a href="../../pages/trainee/settings.php" class="nav-item <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
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

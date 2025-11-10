<?php
// Trainer Sidebar Navigation - Consistent across all pages
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar Navigation -->
<div class="sidebar">
    <div class="sidebar-header">
        <a href="../../dashboard/trainer/" class="sidebar-logo">
            <span class="logo-icon">👨‍🏫</span>
            <span class="logo-text">TrainAI</span>
        </a>
    </div>
    
    <nav class="sidebar-nav">
        <a href="../../dashboard/trainer/" class="nav-item <?php echo ($current_page == 'index.php' && strpos($_SERVER['PHP_SELF'], 'dashboard/trainer') !== false) ? 'active' : ''; ?>">
            <span class="nav-icon">🏠</span>
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="../../pages/trainer/my-courses.php" class="nav-item <?php echo ($current_page == 'my-courses.php') ? 'active' : ''; ?>">
            <span class="nav-icon">📚</span>
            <span class="nav-text">My Courses</span>
        </a>
        <a href="../../pages/trainer/create-course.php" class="nav-item <?php echo ($current_page == 'create-course.php') ? 'active' : ''; ?>">
            <span class="nav-icon">➕</span>
            <span class="nav-text">Create Course</span>
        </a>
        <a href="../../pages/trainer/students.php" class="nav-item <?php echo ($current_page == 'students.php') ? 'active' : ''; ?>">
            <span class="nav-icon">👥</span>
            <span class="nav-text">Students</span>
        </a>
        <a href="../../pages/trainer/analytics.php" class="nav-item <?php echo ($current_page == 'analytics.php') ? 'active' : ''; ?>">
            <span class="nav-icon">📊</span>
            <span class="nav-text">Analytics</span>
        </a>
        <a href="../../pages/trainer/settings.php" class="nav-item <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>">
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

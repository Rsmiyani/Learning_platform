<?php
require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || $_SESSION['role'] !== 'trainee') {
    header('Location: ../../login.php');
    exit;
}

$first_name = $_SESSION['first_name'] ?? 'User';
$user_id = $_SESSION['user_id'] ?? '';
$search_query = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';

try {
    $pdo = getDBConnection();
    
    // Auto-update user level based on current points (runs every time dashboard loads)
    updateUserLevel($pdo, $user_id);
    
    // Get user stats
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM course_enrollments WHERE user_id = ?) as courses_enrolled,
            (SELECT COALESCE(SUM(hours_studied), 0) FROM study_logs WHERE user_id = ?) as total_hours,
            (SELECT COALESCE(total_points, 0) FROM user_points WHERE user_id = ?) as user_points,
            (SELECT COALESCE(level, 1) FROM user_points WHERE user_id = ?) as user_level
    ");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id]);
    $stats = $stmt->fetch();
    
    // Get enrolled courses with search and filter
    $course_query = "
        SELECT ce.*, c.course_name, c.course_id, c.duration_hours, c.rating, c.category, c.thumbnail_url
        FROM course_enrollments ce
        JOIN courses c ON ce.course_id = c.course_id
        WHERE ce.user_id = ?
    ";
    $params = [$user_id];
    
    if ($search_query) {
        $course_query .= " AND c.course_name LIKE ?";
        $params[] = '%' . $search_query . '%';
    }
    
    if ($category_filter) {
        $course_query .= " AND c.category = ?";
        $params[] = $category_filter;
    }
    
    $course_query .= " ORDER BY ce.last_accessed DESC LIMIT 6";
    $stmt = $pdo->prepare($course_query);
    $stmt->execute($params);
    $courses = $stmt->fetchAll();
    
    // Calculate progress for each course based on exam completion
    foreach ($courses as &$course) {
        if ($course['status'] === 'completed') {
            $course['progress_percentage'] = 100;
        } else {
            // Check if exam has been attempted
            $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM exam_results WHERE user_id = ? AND course_id = ?");
            $stmt->execute([$user_id, $course['course_id']]);
            $exam_attempted = $stmt->fetch()['cnt'] > 0;
            
            if ($exam_attempted) {
                $course['progress_percentage'] = 75; // Exam attempted but not passed
            } else {
                // Check if any modules exist
                $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM course_modules WHERE course_id = ?");
                $stmt->execute([$course['course_id']]);
                $has_modules = $stmt->fetch()['cnt'] > 0;
                
                if ($has_modules) {
                    $course['progress_percentage'] = 25; // Course started (has modules)
                } else {
                    $course['progress_percentage'] = 0; // Not started
                }
            }
        }
    }
    
    // Get bookmarked courses
    $stmt = $pdo->prepare("
        SELECT c.* FROM user_bookmarks ub
        JOIN courses c ON ub.course_id = c.course_id
        WHERE ub.user_id = ?
        ORDER BY ub.bookmarked_at DESC
        LIMIT 5
    ");
    $stmt->execute([$user_id]);
    $bookmarks = $stmt->fetchAll();
    
    // Get user interests
    $stmt = $pdo->prepare("SELECT interest_name FROM user_interests WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user_interests = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get recommended courses based on interests
    if (!empty($user_interests)) {
        // Build query to match courses with user interests
        $placeholders = str_repeat('?,', count($user_interests) - 1) . '?';
        $stmt = $pdo->prepare("
            SELECT DISTINCT c.*, u.first_name as instructor_name,
                   (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.course_id) as total_enrollments
            FROM courses c
            JOIN users u ON c.instructor_id = u.user_id
            WHERE c.course_id NOT IN (SELECT course_id FROM course_enrollments WHERE user_id = ?)
            AND (c.category IN ($placeholders) OR c.course_name LIKE CONCAT('%', ?, '%'))
            ORDER BY c.rating DESC, total_enrollments DESC
            LIMIT 6
        ");
        $params = array_merge([$user_id], $user_interests, [$user_interests[0]]);
        $stmt->execute($params);
        $recommended = $stmt->fetchAll();
    } else {
        // Fallback to popular courses if no interests
        $stmt = $pdo->prepare("
            SELECT c.*, u.first_name as instructor_name,
                   (SELECT COUNT(*) FROM course_enrollments WHERE course_id = c.course_id) as total_enrollments
            FROM courses c
            JOIN users u ON c.instructor_id = u.user_id
            WHERE c.course_id NOT IN (SELECT course_id FROM course_enrollments WHERE user_id = ?)
            ORDER BY c.rating DESC, total_enrollments DESC
            LIMIT 6
        ");
        $stmt->execute([$user_id]);
        $recommended = $stmt->fetchAll();
    }
    
    // Get leaderboard
    $stmt = $pdo->prepare("
        SELECT u.user_id, u.first_name, u.last_name, COALESCE(up.total_points, 0) as total_points,
               (SELECT COUNT(*) FROM course_enrollments WHERE user_id = u.user_id) as courses_count
        FROM users u
        LEFT JOIN user_points up ON u.user_id = up.user_id
        WHERE u.role = 'trainee'
        ORDER BY total_points DESC
        LIMIT 5
    ");
    $stmt->execute();
    $leaderboard = $stmt->fetchAll();
    
    // Get user's rank - Calculate rank based on position in sorted list
    // First get current user's points
    $stmt = $pdo->prepare("
        SELECT COALESCE(up.total_points, 0) as my_points
        FROM users u
        LEFT JOIN user_points up ON u.user_id = up.user_id
        WHERE u.user_id = ?
    ");
    $stmt->execute([$user_id]);
    $my_points = $stmt->fetch()['my_points'] ?? 0;
    
    // Now count how many users have MORE points
    $stmt = $pdo->prepare("
        SELECT COUNT(*) + 1 as user_rank
        FROM users u
        LEFT JOIN user_points up ON u.user_id = up.user_id
        WHERE u.role = 'trainee' 
        AND COALESCE(up.total_points, 0) > ?
    ");
    $stmt->execute([$my_points]);
    $user_rank = $stmt->fetch()['user_rank'] ?? 'N/A';
    
    // Get recommended courses (UPDATED - More courses with better sorting)
    $stmt = $pdo->prepare("
        SELECT * FROM courses
        WHERE is_recommended = TRUE
        ORDER BY rating DESC, total_ratings DESC
        LIMIT 12
    ");
    $stmt->execute();
    $recommended = $stmt->fetchAll();
    
    // Get user certificates
    $stmt = $pdo->prepare("
        SELECT uc.*, c.course_name FROM user_certificates uc
        JOIN courses c ON uc.course_id = c.course_id
        WHERE uc.user_id = ?
        ORDER BY uc.issued_date DESC
    ");
    $stmt->execute([$user_id]);
    $certificates = $stmt->fetchAll();
    
    // Get recent achievements
    $stmt = $pdo->prepare("
        SELECT ua.earned_at, a.achievement_name, a.achievement_icon, a.description
        FROM user_achievements ua
        JOIN achievements a ON ua.achievement_id = a.achievement_id
        WHERE ua.user_id = ?
        ORDER BY ua.earned_at DESC
        LIMIT 8
    ");
    $stmt->execute([$user_id]);
    $achievements = $stmt->fetchAll();
    
    // Get unread notifications only
    $stmt = $pdo->prepare("
        SELECT * FROM notifications
        WHERE user_id = ? AND is_read = 0
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll();
    
    // Get unique categories
    $stmt = $pdo->prepare("
        SELECT DISTINCT category FROM courses
        WHERE category IS NOT NULL AND category != ''
        ORDER BY category
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll();
    
    // Get weekly data
    $stmt = $pdo->prepare("
        SELECT DATE(study_date) as date, COALESCE(SUM(hours_studied), 0) as hours
        FROM study_logs
        WHERE user_id = ? AND study_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        GROUP BY DATE(study_date)
        ORDER BY study_date
    ");
    $stmt->execute([$user_id]);
    $weekly_data = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard error: " . $e->getMessage());
    $stats = $courses = $bookmarks = $leaderboard = $recommended = $certificates = $achievements = $notifications = $categories = $weekly_data = [];
    $user_rank = 'N/A';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - TrainAI Premium</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
</head>
<body>
    <!-- Sidebar Navigation -->
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="../../dashboard/trainee/" class="sidebar-logo">
                <span class="logo-icon">🎓</span>
                <span class="logo-text">TrainAI</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="../../dashboard/trainee/" class="nav-item active" title="Dashboard">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="../../pages/trainee/my-courses.php" class="nav-item" title="My Courses">
                <span class="nav-icon">📚</span>
                <span class="nav-text">My Courses</span>
            </a>
            <a href="../../pages/trainee/achievements.php" class="nav-item" title="Achievements">
                <span class="nav-icon">🏆</span>
                <span class="nav-text">Achievements</span>
            </a>
            <a href="../../pages/trainee/certificates.php" class="nav-item" title="Certificates">
                <span class="nav-icon">🎓</span>
                <span class="nav-text">Certificates</span>
            </a>
            <a href="../../pages/trainee/analytics.php" class="nav-item" title="Analytics">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Analytics</span>
            </a>
            <a href="../../pages/trainee/become-trainer.php" class="nav-item" title="Become Trainer">
                <span class="nav-icon">👨‍🏫</span>
                <span class="nav-text">Become Trainer</span>
            </a>
            <a href="../../pages/trainee/settings.php" class="nav-item" title="Settings">
                <span class="nav-icon">⚙️</span>
                <span class="nav-text">Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="../../logout.php" class="logout-btn" title="Logout from TrainAI">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Logout</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navigation -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle">☰</button>
                <div class="search-container">
                    <input type="text" placeholder="Search courses..." class="search-input" id="globalSearch">
                    <span class="search-icon">🔍</span>
                </div>
            </div>
            
            <div class="topbar-right">
                <button class="notification-btn" id="notificationBtn">
                    🔔
                    <?php if (count($notifications) > 0): ?>
                    <span class="notification-badge"><?php echo count($notifications); ?></span>
                    <?php endif; ?>
                </button>
                <div class="notification-dropdown" id="notificationDropdown">
                    <?php if (count($notifications) > 0): ?>
                        <?php foreach ($notifications as $notif): ?>
                        <div class="notification-item" onclick="markAsRead(<?php echo $notif['notification_id']; ?>)" style="cursor: pointer;">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <p class="notif-title"><?php echo htmlspecialchars($notif['title']); ?></p>
                                <span style="font-size: 10px; color: #999;"><?php echo date('M d', strtotime($notif['created_at'])); ?></span>
                            </div>
                            <p class="notif-message"><?php echo htmlspecialchars($notif['message']); ?></p>
                        </div>
                        <?php endforeach; ?>
                        <div style="text-align: center; padding: 10px; border-top: 1px solid #e5e7eb;">
                            <button onclick="markAllAsRead()" style="background: none; border: none; color: #4a9d9a; cursor: pointer; font-size: 12px; font-weight: 600;">
                                ✓ Mark all as read
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="notification-item" style="text-align: center; color: #999;">
                            <p>🔔 No new notifications</p>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="user-profile">
                    <div class="user-avatar"><?php echo strtoupper(substr($first_name, 0, 1)); ?></div>
                    <div class="user-info">
                        <p class="user-name"><?php echo htmlspecialchars($first_name); ?></p>
                        <p class="user-level">Level <?php echo htmlspecialchars($stats['user_level'] ?? 1); ?> 🎯</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Container -->
        <div class="dashboard-container">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <div class="banner-content">
                    <h1>Welcome back, <span class="name"><?php echo htmlspecialchars($first_name); ?></span>! 👋</h1>
                    <p>Keep pushing towards your learning goals</p>
                    <?php 
                    $current_points = $stats['user_points'] ?? 0;
                    $current_level = $stats['user_level'] ?? 1;
                    $points_for_current_level = ($current_level - 1) * 100;
                    $points_for_next_level = $current_level * 100;
                    $points_in_level = $current_points - $points_for_current_level;
                    $progress_to_next = ($points_in_level / 100) * 100;
                    ?>
                    <div style="margin-top: 20px; max-width: 450px; background: rgba(255,255,255,0.15); padding: 15px; border-radius: 12px; backdrop-filter: blur(10px);">
                        <div style="display: flex; justify-content: space-between; font-size: 13px; font-weight: 600; color: #ffffff; margin-bottom: 8px;">
                            <span>Level <?php echo $current_level; ?></span>
                            <span style="background: rgba(255,255,255,0.2); padding: 2px 10px; border-radius: 12px;"><?php echo $current_points; ?> / <?php echo $points_for_next_level; ?> XP</span>
                            <span>Level <?php echo $current_level + 1; ?></span>
                        </div>
                        <div style="width: 100%; height: 12px; background: rgba(0,0,0,0.2); border-radius: 10px; overflow: hidden; box-shadow: inset 0 2px 4px rgba(0,0,0,0.2);">
                            <div style="width: <?php echo $progress_to_next; ?>%; height: 100%; background: linear-gradient(90deg, #FFD700, #FFA500); transition: width 0.3s ease; box-shadow: 0 0 10px rgba(255,215,0,0.5);"></div>
                        </div>
                        <div style="text-align: center; margin-top: 8px; font-size: 11px; color: rgba(255,255,255,0.8);">
                            <?php echo (100 - $progress_to_next); ?> XP to next level
                        </div>
                    </div>
                </div>
                <div class="level-badge">
                    <div class="level-circle">
                        <span class="level-number"><?php echo htmlspecialchars($stats['user_level'] ?? 1); ?></span>
                    </div>
                    <p>Level</p>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-box">
                    <div class="stat-icon stat-icon-blue">📚</div>
                    <div class="stat-content">
                        <p class="stat-label">Courses</p>
                        <h3 class="stat-number"><?php echo htmlspecialchars($stats['courses_enrolled'] ?? 0); ?></h3>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon stat-icon-green">⏱️</div>
                    <div class="stat-content">
                        <p class="stat-label">Learning Hours</p>
                        <h3 class="stat-number"><?php echo htmlspecialchars(round($stats['total_hours'] ?? 0, 1)); ?>h</h3>
                    </div>
                </div>
                <div class="stat-box">
                    <div class="stat-icon stat-icon-purple">⭐</div>
                    <div class="stat-content">
                        <p class="stat-label">Points</p>
                        <h3 class="stat-number"><?php echo htmlspecialchars($stats['user_points'] ?? 0); ?></h3>
                    </div>
                </div>
            </div>

            <!-- Main Grid -->
            <div class="dashboard-grid premium-grid">
                <!-- Left Column -->
                <div class="left-column premium-left">
                    <!-- FEATURE 1: Course Search & Filter -->
                    <section class="card premium-card">
                        <div class="card-header">
                            <h2>📚 My Courses</h2>
                            <a href="../../pages/trainee/my-courses.php" class="view-all">View all →</a>
                        </div>
                        
                        <!-- Search & Filter Bar -->
                        <div class="filter-bar">
                            <form method="GET" class="filter-form">
                                <input type="text" name="search" placeholder="Search courses..." 
                                       class="filter-input" value="<?php echo htmlspecialchars($search_query); ?>">
                                <select name="category" class="filter-select" onchange="this.form.submit()">
                                    <option value="">All Categories</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                        <?php echo ($category_filter === $cat['category']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat['category']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="filter-btn">🔍 Search</button>
                                <?php if ($search_query || $category_filter): ?>
                                    <a href="?category=" class="filter-clear">✕ Clear</a>
                                <?php endif; ?>
                            </form>
                        </div>

                        <!-- Courses Grid -->
                        <div class="continue-courses-grid">
                            <?php if (count($courses) > 0): ?>
                                <?php foreach ($courses as $course): ?>
                                <div class="course-card-circular">
                                    <div class="circular-progress" data-progress="<?php echo htmlspecialchars($course['progress_percentage'] ?? 0); ?>">
                                        <svg viewBox="0 0 100 100" class="progress-ring">
                                            <defs>
                                                <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                                    <stop offset="0%" style="stop-color:#6B5B95;stop-opacity:1" />
                                                    <stop offset="100%" style="stop-color:#009B95;stop-opacity:1" />
                                                </linearGradient>
                                            </defs>
                                            <circle cx="50" cy="50" r="45" class="progress-bg"></circle>
                                            <circle cx="50" cy="50" r="45" class="progress-fill"></circle>
                                        </svg>
                                        <div class="progress-text">
                                            <span class="progress-percent"><?php echo round($course['progress_percentage'] ?? 0); ?>%</span>
                                        </div>
                                    </div>
                                    <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                                    <p class="course-category">📂 <?php echo htmlspecialchars($course['category'] ?? 'General'); ?></p>
                                    <p class="course-rating">⭐ <?php echo htmlspecialchars($course['rating'] ?? 0); ?></p>
                                    <div class="course-actions">
                                        <button class="continue-btn" onclick="window.location.href='../../pages/trainee/course-modules.php?course_id=<?php echo htmlspecialchars($course['course_id']); ?>'">Continue</button>
                                        <button class="bookmark-btn" data-course-id="<?php echo htmlspecialchars($course['course_id']); ?>">🔖</button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="no-results">
                                    <p>No courses found. Try adjusting your filters!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- Weekly Chart -->
                    <section class="card premium-card">
                        <div class="card-header">
                            <h2>📊 Weekly Activity</h2>
                        </div>
                        <div class="chart-container">
                            <canvas id="weeklyChart"></canvas>
                        </div>
                    </section>
                </div>

                <!-- Right Column -->
                <div class="right-column premium-right">
                    <!-- FEATURE 5: Quick Links/Bookmarks -->
                    <section class="card premium-card">
                        <div class="card-header">
                            <h2>🔖 Quick Links</h2>
                        </div>
                        <?php if (count($bookmarks) > 0): ?>
                            <div class="bookmarks-list">
                                <?php foreach ($bookmarks as $bookmark): ?>
                                <div class="bookmark-item">
                                    <div class="bookmark-info">
                                        <h4><?php echo htmlspecialchars($bookmark['course_name']); ?></h4>
                                        <p class="bookmark-category">📂 <?php echo htmlspecialchars($bookmark['category'] ?? 'General'); ?></p>
                                    </div>
                                    <button class="open-btn" onclick="goToCourse(<?php echo htmlspecialchars($bookmark['course_id']); ?>)">→</button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <p>📚 No bookmarks yet. Click the bookmark icon on courses to save them!</p>
                            </div>
                        <?php endif; ?>
                    </section>

                    <!-- FEATURE 3: Leaderboard -->
                    <section class="card premium-card">
                        <div class="card-header">
                            <h2>🏆 Leaderboard</h2>
                        </div>
                        <div class="leaderboard-wrapper">
                            <div class="user-rank-box">
                                <p class="rank-label">Your Rank</p>
                                <h3 class="user-rank">#<?php echo htmlspecialchars($user_rank); ?></h3>
                                <p class="rank-points"><?php echo htmlspecialchars($stats['user_points'] ?? 0); ?> points</p>
                            </div>
                            <div class="leaderboard">
                                <?php 
                                $position = 1; 
                                $current_rank = 1;
                                $prev_points = null;
                                foreach ($leaderboard as $index => $leader): 
                                    // Handle ties - if same points as previous, keep same rank
                                    if ($prev_points !== null && $leader['total_points'] < $prev_points) {
                                        $current_rank = $position;
                                    }
                                    $prev_points = $leader['total_points'];
                                ?>
                                <div class="leaderboard-item <?php echo ($leader['user_id'] == $user_id) ? 'is-user' : ''; ?>">
                                    <div class="rank-badge">
                                        <?php 
                                        if ($current_rank == 1) echo '🥇';
                                        elseif ($current_rank == 2) echo '🥈';
                                        elseif ($current_rank == 3) echo '🥉';
                                        else echo '#' . $current_rank;
                                        ?>
                                    </div>
                                    <div class="leader-info">
                                        <p class="leader-name"><?php echo htmlspecialchars($leader['first_name']); ?></p>
                                        <p class="leader-courses"><?php echo htmlspecialchars($leader['courses_count']); ?> courses</p>
                                    </div>
                                    <p class="leader-points"><?php echo htmlspecialchars($leader['total_points']); ?> pts</p>
                                </div>
                                <?php $position++; endforeach; ?>
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <!-- FEATURE 4: Certificates - FULL WIDTH CENTER -->
            <section class="card premium-card full-width-section">
                <div class="card-header">
                    <h2>🎓 Certificates</h2>
                </div>
                <?php if (count($certificates) > 0): ?>
                    <div class="certificates-list-grid">
                        <?php foreach ($certificates as $cert): ?>
                        <div class="certificate-item">
                            <div class="cert-icon">🎖️</div>
                            <div class="cert-info">
                                <h4><?php echo htmlspecialchars($cert['course_name']); ?></h4>
                                <p class="cert-date">Issued: <?php echo date('M d, Y', strtotime($cert['issued_date'])); ?></p>
                                <p class="cert-number">ID: <?php echo htmlspecialchars($cert['certificate_number']); ?></p>
                            </div>
                            <button class="download-btn" onclick="downloadCertificate(<?php echo htmlspecialchars($cert['cert_id']); ?>)">📥</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>📜 Complete courses to earn certificates!</p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- FEATURE: Achievements - FULL WIDTH CENTER -->
            <section class="card premium-card full-width-section">
                <div class="card-header">
                    <h2>🏅 Achievements</h2>
                </div>
                <div class="achievements-list-grid">
                    <?php if (count($achievements) > 0): ?>
                        <?php foreach ($achievements as $ach): ?>
                        <div class="achievement-item">
                            <div class="achievement-icon-large"><?php echo htmlspecialchars($ach['achievement_icon']); ?></div>
                            <div class="achievement-details">
                                <h4><?php echo htmlspecialchars($ach['achievement_name']); ?></h4>
                                <p><?php echo htmlspecialchars($ach['description']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>🎯 Keep learning to earn achievements!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <!-- Recommended Courses (UPDATED WITH THUMBNAILS) -->
            <section class="card full-width premium-card">
                <div class="card-header">
                    <h2>💡 Recommended For You <?php if (!empty($user_interests)): ?><span style="font-size: 14px; color: #6b7280; font-weight: normal;">Based on your interests</span><?php endif; ?></h2>
                    <a href="../../pages/trainee/all-courses.php" class="view-all">See all →</a>
                </div>
                <div class="recommended-grid">
                    <?php foreach ($recommended as $rec): 
                        // Check if already enrolled
                        $stmt = $pdo->prepare("SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ?");
                        $stmt->execute([$user_id, $rec['course_id']]);
                        $is_enrolled = $stmt->rowCount() > 0;
                    ?>
                    <div class="recommended-card">
                        <div class="card-tag"><?php echo ucfirst($rec['difficulty']); ?></div>
                        
                        <!-- UPDATED: Use thumbnail_url with fallback gradient -->
                        <div class="card-image" style="background-image: url('<?php echo htmlspecialchars($rec['thumbnail_url'] ?? ''); ?>'); background-size: cover; background-position: center; background-color: #667eea;">
                            <?php if (empty($rec['thumbnail_url'])): ?>
                                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 100%; height: 100%;"></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer">
                            <h4><?php echo htmlspecialchars($rec['course_name']); ?></h4>
                            <p class="instructor"><?php echo htmlspecialchars($rec['instructor_name']); ?></p>
                            <div class="rating">⭐ <?php echo htmlspecialchars($rec['rating']); ?> (<?php echo htmlspecialchars($rec['total_ratings']); ?>)</div>
                            
                            <?php if ($is_enrolled): ?>
                                <button class="enroll-btn enrolled-btn" disabled style="background: linear-gradient(135deg, #10B981, #059669);">
                                    ✓ Enrolled
                                </button>
                            <?php else: ?>
                                <button class="enroll-btn" data-course-id="<?php echo htmlspecialchars($rec['course_id']); ?>">Enroll Now</button>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </div>

    <script>
        // Pass PHP data to JavaScript for Weekly Activity Chart
        const weeklyActivityData = <?php echo json_encode($weekly_data); ?>;
    </script>
    <script src="../../assets/js/dashboard.js"></script>
    <script>
        // Mark single notification as read
        function markAsRead(notificationId) {
            fetch('../../handlers/mark-notification-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    notification_id: notificationId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to update notification count
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Mark all notifications as read
        function markAllAsRead() {
            fetch('../../handlers/mark-notification-read.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    mark_all: true
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Reload page to update notification count
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>
</body>
</html>
                                
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'trainee') {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$first_name = $_SESSION['first_name'];
$search_query = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$sort_by = $_GET['sort'] ?? 'rating';

$courses = [];
$categories = [];

try {
    $pdo = getDBConnection();
    
    // Get all categories
    $stmt = $pdo->prepare("
        SELECT DISTINCT category FROM courses
        WHERE category IS NOT NULL AND category != ''
        ORDER BY category
    ");
    $stmt->execute();
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Build query for all available courses (NOT enrolled) with instructor name
    $query = "
        SELECT c.*, CONCAT(u.first_name, ' ', u.last_name) as instructor_name
        FROM courses c
        LEFT JOIN users u ON c.instructor_id = u.user_id
        WHERE c.course_id NOT IN (
            SELECT course_id FROM course_enrollments WHERE user_id = ?
        )
    ";
    
    $params = [$user_id];
    
    // Add search filter
    if ($search_query) {
        $query .= " AND (c.course_name LIKE ? OR c.description LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
    }
    
    // Add category filter
    if ($category_filter) {
        $query .= " AND c.category = ?";
        $params[] = $category_filter;
    }
    
    // Add sorting
    if ($sort_by === 'rating') {
        $query .= " ORDER BY c.rating DESC";
    } elseif ($sort_by === 'new') {
        $query .= " ORDER BY c.created_at DESC";
    } elseif ($sort_by === 'popular') {
        $query .= " ORDER BY c.total_ratings DESC";
    } else {
        $query .= " ORDER BY c.rating DESC";
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("All courses error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Courses - TrainAI</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .all-courses-container {
            padding: 20px;
        }
        
        .courses-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .filter-section {
            background: var(--bg-card);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }
        
        .filter-controls {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-controls input,
        .filter-controls select {
            padding: 10px 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
            background: var(--bg-secondary);
            color: var(--text-primary);
            transition: all var(--transition-base);
        }
        
        .filter-controls input:focus,
        .filter-controls select:focus {
            outline: none;
            border-color: var(--accent-primary);
            box-shadow: 0 0 0 3px rgba(107, 91, 149, 0.1);
        }
        
        .btn-filter {
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all var(--transition-base);
        }
        
        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .btn-clear {
            padding: 10px 20px;
            background: var(--text-muted);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all var(--transition-base);
            font-weight: 500;
        }
        
        .btn-clear:hover {
            background: var(--text-secondary);
            transform: translateY(-2px);
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .course-card-full {
            background: var(--bg-card);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: all var(--transition-base);
        }
        
        .course-card-full:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-primary);
        }
        
        .course-image {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, rgba(107, 91, 149, 0.1) 0%, rgba(0, 155, 149, 0.1) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid var(--border-color);
        }
        
        .course-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .course-image .default-course-icon {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(107, 91, 149, 0.08) 0%, rgba(0, 155, 149, 0.08) 100%);
        }
        
        .course-image .default-course-icon svg {
            width: 90px;
            height: 90px;
            opacity: 0.7;
        }
        
        .course-content {
            padding: 20px;
        }
        
        .course-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 12px;
            color: var(--text-primary);
            line-height: 1.4;
            font-family: 'Poppins', sans-serif;
        }
        
        .course-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 13px;
            color: var(--text-secondary);
            flex-wrap: wrap;
            gap: 12px;
        }
        
        .course-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .course-description {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 15px;
            line-height: 1.6;
            height: 40px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        
        .course-footer {
            display: flex;
            justify-content: center;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid var(--border-color);
        }
        
        .course-rating {
            font-size: 14px;
            color: var(--accent-orange);
            font-weight: 600;
        }
        
        .btn-enroll {
            padding: 12px 30px;
            background: linear-gradient(135deg, var(--accent-primary) 0%, var(--accent-secondary) 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all var(--transition-base);
            width: 100%;
            font-size: 14px;
        }
        
        .btn-enroll:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: var(--bg-card);
            border-radius: 12px;
            color: var(--text-secondary);
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
        }
        
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: var(--text-primary);
            font-family: 'Poppins', sans-serif;
        }
        
        .stats-info {
            background: linear-gradient(135deg, rgba(107, 91, 149, 0.15) 0%, rgba(0, 155, 149, 0.15) 100%);
            color: var(--text-primary);
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 30px;
            text-align: center;
            border: 1px solid var(--border-color);
        }
        
        .stats-info h2 {
            margin: 0 0 10px 0;
            font-size: 28px;
            font-family: 'Poppins', sans-serif;
            color: var(--text-primary);
        }
        
        .stats-info p {
            margin: 0;
            font-size: 14px;
            color: var(--text-secondary);
        }
    </style>
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
            <a href="../../dashboard/trainee/" class="nav-item">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="../../pages/trainee/my-courses.php" class="nav-item">
                <span class="nav-icon">📚</span>
                <span class="nav-text">My Courses</span>
            </a>
            <a href="../../pages/trainee/all-courses.php" class="nav-item active">
                <span class="nav-icon">🔍</span>
                <span class="nav-text">Explore Courses</span>
            </a>
            <a href="../../pages/trainee/achievements.php" class="nav-item">
                <span class="nav-icon">🏆</span>
                <span class="nav-text">Achievements</span>
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

    <div class="main-content">
        <!-- Top Navigation -->
        <div class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle">☰</button>
                <h2>🔍 Explore All Courses</h2>
            </div>
        </div>

        <div class="dashboard-container all-courses-container">
            <!-- Stats Banner -->
            <div class="stats-info">
                <h2>📚 <?php echo count($courses); ?> Courses Available</h2>
                <p>Find and enroll in courses to boost your skills!</p>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <form method="GET" class="filter-controls">
                    <!-- Search Input -->
                    <input 
                        type="text" 
                        name="search" 
                        placeholder="🔍 Search courses..." 
                        value="<?php echo htmlspecialchars($search_query); ?>"
                        style="flex: 1; min-width: 200px;"
                    >
                    
                    <!-- Category Filter -->
                    <select name="category">
                        <option value="">📂 All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                                <?php echo $category_filter === $cat['category'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <!-- Sort Filter -->
                    <select name="sort">
                        <option value="rating" <?php echo $sort_by === 'rating' ? 'selected' : ''; ?>>⭐ Top Rated</option>
                        <option value="new" <?php echo $sort_by === 'new' ? 'selected' : ''; ?>>✨ Newest</option>
                        <option value="popular" <?php echo $sort_by === 'popular' ? 'selected' : ''; ?>>🔥 Most Popular</option>
                    </select>
                    
                    <!-- Buttons -->
                    <button type="submit" class="btn-filter">🔍 Search</button>
                    <?php if ($search_query || $category_filter || $sort_by !== 'rating'): ?>
                        <a href="../../pages/trainee/all-courses.php" class="btn-clear">✕ Clear Filters</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Courses Grid -->
            <?php if (count($courses) > 0): ?>
                <div class="courses-grid">
                    <?php foreach ($courses as $course): ?>
                        <div class="course-card-full">
                            <!-- Course Image -->
                            <div class="course-image">
                                <?php if ($course['thumbnail_url'] && $course['thumbnail_url'] !== ''): ?>
                                    <img src="<?php echo htmlspecialchars($course['thumbnail_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($course['course_name']); ?>"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="default-course-icon" style="display: none;">
                                        <svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <!-- Book pages background -->
                                            <rect x="25" y="20" width="70" height="80" rx="4" fill="#F5F1E8" opacity="0.9"/>
                                            <!-- Book cover -->
                                            <rect x="25" y="20" width="70" height="80" rx="4" fill="url(#gradient1)" opacity="0.15"/>
                                            <!-- Book spine shadow -->
                                            <rect x="25" y="20" width="8" height="80" rx="4 0 0 4" fill="#6B5B95" opacity="0.2"/>
                                            <!-- Text lines -->
                                            <line x1="40" y1="40" x2="85" y2="40" stroke="#6B5B95" stroke-width="2" opacity="0.3" stroke-linecap="round"/>
                                            <line x1="40" y1="50" x2="85" y2="50" stroke="#6B5B95" stroke-width="2" opacity="0.3" stroke-linecap="round"/>
                                            <line x1="40" y1="60" x2="70" y2="60" stroke="#6B5B95" stroke-width="2" opacity="0.3" stroke-linecap="round"/>
                                            <!-- Decorative elements -->
                                            <circle cx="50" cy="75" r="3" fill="#009B95" opacity="0.4"/>
                                            <circle cx="65" cy="75" r="3" fill="#009B95" opacity="0.4"/>
                                            <circle cx="80" cy="75" r="3" fill="#009B95" opacity="0.4"/>
                                            <!-- Gradient definition -->
                                            <defs>
                                                <linearGradient id="gradient1" x1="0%" y1="0%" x2="100%" y2="100%">
                                                    <stop offset="0%" style="stop-color:#6B5B95;stop-opacity:0.3" />
                                                    <stop offset="100%" style="stop-color:#009B95;stop-opacity:0.2" />
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                    </div>
                                <?php else: ?>
                                    <div class="default-course-icon">
                                        <svg viewBox="0 0 120 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <!-- Book pages background -->
                                            <rect x="25" y="20" width="70" height="80" rx="4" fill="#F5F1E8" opacity="0.9"/>
                                            <!-- Book cover -->
                                            <rect x="25" y="20" width="70" height="80" rx="4" fill="url(#gradient2)" opacity="0.15"/>
                                            <!-- Book spine shadow -->
                                            <rect x="25" y="20" width="8" height="80" rx="4 0 0 4" fill="#6B5B95" opacity="0.2"/>
                                            <!-- Text lines -->
                                            <line x1="40" y1="40" x2="85" y2="40" stroke="#6B5B95" stroke-width="2" opacity="0.3" stroke-linecap="round"/>
                                            <line x1="40" y1="50" x2="85" y2="50" stroke="#6B5B95" stroke-width="2" opacity="0.3" stroke-linecap="round"/>
                                            <line x1="40" y1="60" x2="70" y2="60" stroke="#6B5B95" stroke-width="2" opacity="0.3" stroke-linecap="round"/>
                                            <!-- Decorative elements -->
                                            <circle cx="50" cy="75" r="3" fill="#009B95" opacity="0.4"/>
                                            <circle cx="65" cy="75" r="3" fill="#009B95" opacity="0.4"/>
                                            <circle cx="80" cy="75" r="3" fill="#009B95" opacity="0.4"/>
                                            <!-- Gradient definition -->
                                            <defs>
                                                <linearGradient id="gradient2" x1="0%" y1="0%" x2="100%" y2="100%">
                                                    <stop offset="0%" style="stop-color:#6B5B95;stop-opacity:0.3" />
                                                    <stop offset="100%" style="stop-color:#009B95;stop-opacity:0.2" />
                                                </linearGradient>
                                            </defs>
                                        </svg>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Course Content -->
                            <div class="course-content">
                                <!-- Course Title -->
                                <div class="course-title">
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </div>
                                
                                <!-- Course Meta Info -->
                                <div class="course-meta">
                                    <span>👤 <?php echo htmlspecialchars($course['instructor_name'] ?? 'N/A'); ?></span>
                                    <span>📂 <?php echo htmlspecialchars($course['category'] ?? 'General'); ?></span>
                                </div>
                                <div class="course-meta">
                                    <span>⏱️ <?php echo $course['duration_hours']; ?>h</span>
                                    <span>⭐ <?php echo $course['rating']; ?> (<?php echo $course['total_ratings']; ?>)</span>
                                </div>
                                
                                <!-- Course Description -->
                                <div class="course-description">
                                    <?php echo htmlspecialchars($course['description'] ?? 'No description provided'); ?>
                                </div>
                                
                                <!-- Course Footer -->
                                <div class="course-footer">
                                    <button class="btn-enroll" onclick="enrollCourse(<?php echo $course['course_id']; ?>, '<?php echo htmlspecialchars($course['course_name']); ?>')">
                                        Enroll Now →
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div style="font-size: 60px; margin-bottom: 20px;">📭</div>
                    <h3>No Courses Found</h3>
                    <p>Try adjusting your search or filters</p>
                    <a href="../../pages/trainee/all-courses.php" class="btn-clear" style="margin-top: 20px; display: inline-block;">Clear Filters</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Enroll in course function
        function enrollCourse(courseId, courseName) {
            if (confirm('Do you want to enroll in ' + courseName + '?')) {
                fetch('../../handlers/enroll-handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ course_id: courseId })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ ' + data.message);
                        location.reload();
                    } else {
                        alert('❌ ' + (data.message || 'Error enrolling in course'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('❌ Error: ' + error);
                });
            }
        }
    </script>

    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>

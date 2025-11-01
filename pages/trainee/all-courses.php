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
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .filter-controls input:focus,
        .filter-controls select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        
        .btn-filter {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
        }
        
        .btn-filter:hover {
            background: #5568d3;
        }
        
        .btn-clear {
            padding: 10px 20px;
            background: #999;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-clear:hover {
            background: #777;
        }
        
        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .course-card-full {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .course-card-full:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .course-image {
            width: 100%;
            height: 180px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 60px;
            position: relative;
            overflow: hidden;
        }
        
        .course-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .course-image .emoji-fallback {
            position: absolute;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .course-content {
            padding: 20px;
        }
        
        .course-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
            line-height: 1.3;
        }
        
        .course-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .course-description {
            font-size: 13px;
            color: #777;
            margin-bottom: 15px;
            line-height: 1.5;
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
            border-top: 1px solid #eee;
        }
        
        .course-rating {
            font-size: 14px;
            color: #f39c12;
            font-weight: bold;
        }
        
        .btn-enroll {
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
            width: 100%;
            font-size: 14px;
        }
        
        .btn-enroll:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            color: #666;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .stats-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .stats-info h2 {
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        
        .stats-info p {
            margin: 0;
            font-size: 14px;
            opacity: 0.9;
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
                                    <div class="emoji-fallback" style="display: none;">📚</div>
                                <?php else: ?>
                                    <div class="emoji-fallback">📚</div>
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

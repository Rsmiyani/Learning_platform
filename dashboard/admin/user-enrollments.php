<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_GET['user_id'] ?? null;

if (!$user_id) {
    header('Location: index.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get user info
    $stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = "User not found";
        header('Location: index.php');
        exit;
    }
    
    // Get all enrollments
    $stmt = $pdo->prepare("
        SELECT ce.*, c.course_name, c.course_code,
               CONCAT(u.first_name, ' ', u.last_name) as instructor_name
        FROM course_enrollments ce
        JOIN courses c ON ce.course_id = c.course_id
        LEFT JOIN users u ON c.instructor_id = u.user_id
        WHERE ce.user_id = ?
        ORDER BY ce.enrolled_at DESC
    ");
    $stmt->execute([$user_id]);
    $enrollments = $stmt->fetchAll();
    
} catch (PDOException $e) {
    error_log("Enrollment fetch error: " . $e->getMessage());
    $enrollments = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Enrollments - Admin</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .back-btn {
            background: #6b7280;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        
        .back-btn:hover {
            background: #4b5563;
        }
        
        .user-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .user-info h2 {
            margin: 0 0 10px 0;
        }
        
        .enrollment-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .enrollment-info h3 {
            margin: 0 0 5px 0;
            color: #1f2937;
        }
        
        .enrollment-info p {
            margin: 5px 0;
            color: #6b7280;
            font-size: 14px;
        }
        
        .unenroll-btn {
            background: #dc2626;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .unenroll-btn:hover {
            background: #991b1b;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-top: 5px;
        }
        
        .status-active {
            background: #d1fae5;
            color: #059669;
        }
        
        .status-completed {
            background: #dbeafe;
            color: #2563eb;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="../../dashboard/admin/" class="sidebar-logo">
                <span class="logo-icon">👑</span>
                <span class="logo-text">TrainAI Admin</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="../../dashboard/admin/" class="nav-item">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="../../dashboard/admin/enrollments.php" class="nav-item active">
                <span class="nav-icon">📝</span>
                <span class="nav-text">Enrollments</span>
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
        <div class="topbar">
            <div class="topbar-left">
                <h2>📝 Manage Enrollments</h2>
            </div>
        </div>

        <div class="dashboard-container">
            <a href="index.php" class="back-btn">← Back to Dashboard</a>
            
            <div class="user-info">
                <h2><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h2>
                <p><?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>Total Enrollments:</strong> <?php echo count($enrollments); ?></p>
            </div>

            <?php if (count($enrollments) > 0): ?>
                <?php foreach ($enrollments as $enrollment): ?>
                    <div class="enrollment-card">
                        <div class="enrollment-info">
                            <h3><?php echo htmlspecialchars($enrollment['course_name']); ?></h3>
                            <p><strong>Code:</strong> <?php echo htmlspecialchars($enrollment['course_code']); ?></p>
                            <p><strong>Instructor:</strong> <?php echo htmlspecialchars($enrollment['instructor_name'] ?? 'N/A'); ?></p>
                            <p><strong>Enrolled:</strong> <?php echo date('M d, Y', strtotime($enrollment['enrolled_at'])); ?></p>
                            <p><strong>Progress:</strong> <?php echo $enrollment['progress_percentage']; ?>%</p>
                            <span class="status-badge status-<?php echo $enrollment['status']; ?>">
                                <?php echo ucfirst($enrollment['status']); ?>
                            </span>
                        </div>
                        <button class="unenroll-btn" onclick="unenrollUser(<?php echo $enrollment['enrollment_id']; ?>, '<?php echo htmlspecialchars($enrollment['course_name']); ?>')">
                            🗑️ Unenroll
                        </button>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; padding: 60px; background: white; border-radius: 12px;">
                    <h3 style="color: #6b7280;">No enrollments found</h3>
                    <p style="color: #9ca3af;">This user is not enrolled in any courses.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function unenrollUser(enrollmentId, courseName) {
            if (confirm(`Are you sure you want to unenroll this user from "${courseName}"?\n\nThis action cannot be undone.`)) {
                window.location.href = `unenroll-user.php?enrollment_id=${enrollmentId}&user_id=<?php echo $user_id; ?>`;
            }
        }
    </script>
</body>
</html>

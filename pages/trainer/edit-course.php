<?php
require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'trainer') {
    header('Location: ../../login.php');
    exit;
}

$trainer_id = $_SESSION['user_id'];
$course_id = $_GET['id'] ?? null;
$error = $success = '';

try {
    $pdo = getDBConnection();
    
    // Get course
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ? AND instructor_id = ?");
    $stmt->execute([$course_id, $trainer_id]);
    $course = $stmt->fetch();
    
    if (!$course) {
        die("Course not found!");
    }
    
    // Handle update
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $course_name = $_POST['course_name'] ?? '';
        $description = $_POST['description'] ?? '';
        $category = $_POST['category'] ?? '';
        $difficulty = $_POST['difficulty'] ?? '';
        $duration_hours = $_POST['duration_hours'] ?? 0;
        $thumbnail_url = $_POST['thumbnail_url'] ?? '';
        
        $stmt = $pdo->prepare("
            UPDATE courses 
            SET course_name = ?, description = ?, category = ?, difficulty = ?, duration_hours = ?, thumbnail_url = ?
            WHERE course_id = ? AND instructor_id = ?
        ");
        
        if ($stmt->execute([$course_name, $description, $category, $difficulty, $duration_hours, $thumbnail_url, $course_id, $trainer_id])) {
            $success = "✅ Course updated successfully!";
            $course = array_merge($course, $_POST);
        } else {
            $error = "Error updating course";
        }
    }
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course - TrainAI Trainer</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="../../dashboard/trainer/" class="sidebar-logo">
                <span class="logo-icon">👨‍🏫</span>
                <span class="logo-text">TrainAI</span>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <a href="../../dashboard/trainer/" class="nav-item">
                <span class="nav-icon">🏠</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="../../pages/trainer/my-courses.php" class="nav-item">
                <span class="nav-icon">📚</span>
                <span class="nav-text">My Courses</span>
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
                <button class="menu-toggle">☰</button>
                <h2>Edit Course</h2>
            </div>
        </div>

        <div class="dashboard-container">
            <section class="card premium-card" style="max-width: 600px; margin: 0 auto;">
                <div class="card-header">
                    <h2>✏️ Edit Course</h2>
                </div>

                <?php if ($error): ?>
                    <div style="background: #FEE; color: #C00; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div style="background: #EFE; color: #080; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" style="padding: 20px;">
                    <div class="form-group">
                        <label>📚 Course Name</label>
                        <input type="text" name="course_name" value="<?php echo htmlspecialchars($course['course_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>📝 Description</label>
                        <textarea name="description" rows="4"><?php echo htmlspecialchars($course['description']); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>📂 Category</label>
                        <select name="category">
                            <option value="Programming" <?php echo $course['category'] == 'Programming' ? 'selected' : ''; ?>>Programming</option>
                            <option value="Web Development" <?php echo $course['category'] == 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                            <option value="Data Science" <?php echo $course['category'] == 'Data Science' ? 'selected' : ''; ?>>Data Science</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>⭐ Difficulty Level</label>
                        <select name="difficulty">
                            <option value="Beginner" <?php echo $course['difficulty'] == 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                            <option value="Intermediate" <?php echo $course['difficulty'] == 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="Advanced" <?php echo $course['difficulty'] == 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>⏱️ Duration (hours)</label>
                        <input type="number" name="duration_hours" value="<?php echo $course['duration_hours']; ?>" required>
                    </div>

                    <div class="form-group">
                        <label>🖼️ Thumbnail URL</label>
                        <input type="url" name="thumbnail_url" value="<?php echo htmlspecialchars($course['thumbnail_url']); ?>">
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; padding: 12px; margin-top: 20px;">
                        Update Course
                    </button>
                </form>
            </section>
        </div>
    </div>

    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>

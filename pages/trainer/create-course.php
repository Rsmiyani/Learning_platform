<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'trainer') {
    header('Location: ../../login.php');
    exit;
}

$trainer_id = $_SESSION['user_id'];
$error = $success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $course_name = trim($_POST['course_name'] ?? '');
    $course_code = trim($_POST['course_code'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $difficulty = trim($_POST['difficulty'] ?? 'Beginner');
    $duration_hours = intval($_POST['duration_hours'] ?? 0);
    
    // ========== HANDLE FILE UPLOAD ==========
    $thumbnail_url = '';
    if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['size'] > 0) {
        $file = $_FILES['thumbnail'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if ($file['size'] > $max_size) {
            $error = "❌ File size too large! Max 2MB.";
        } elseif (!in_array($file['type'], $allowed_types)) {
            $error = "❌ Invalid file type! Use JPG, PNG, GIF, or WebP.";
        } else {
            // Create uploads directory if not exists
            $upload_dir = '../../uploads/courses/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate unique filename
            $filename = uniqid() . '_' . basename($file['name']);
            $filepath = $upload_dir . $filename;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $thumbnail_url = $filepath;
            } else {
                $error = "❌ Failed to upload image!";
            }
        }
    }
    // ========================================
    
    // Validation
    if (!$error && !$course_name) {
        $error = "❌ Course name is required!";
    } elseif (!$error && !$course_code) {
        $error = "❌ Course code is required!";
    } elseif (!$error && $duration_hours <= 0) {
        $error = "❌ Duration must be greater than 0!";
    } else {
        try {
            $pdo = getDBConnection();
            
            // Check if course code already exists
            $stmt = $pdo->prepare("SELECT course_id FROM courses WHERE course_code = ?");
            $stmt->execute([$course_code]);
            
            if ($stmt->rowCount() > 0) {
                $error = "❌ Course code already exists! Use a unique code.";
            } else {
                // Insert course
                $stmt = $pdo->prepare("
                    INSERT INTO courses 
                    (course_name, course_code, description, instructor_id, category, difficulty, duration_hours, thumbnail_url, rating, total_ratings, is_recommended)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 0, FALSE)
                ");
                
                $result = $stmt->execute([
                    $course_name,
                    $course_code,
                    $description,
                    $trainer_id,
                    $category,
                    $difficulty,
                    $duration_hours,
                    $thumbnail_url
                ]);
                
                if ($result) {
                    $success = "✅ Course created successfully! Redirecting...";
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = '../../pages/trainer/my-courses.php';
                        }, 2000);
                    </script>";
                } else {
                    $error = "❌ Error creating course. Please try again.";
                }
            }
            
        } catch (PDOException $e) {
            error_log("Create course error: " . $e->getMessage());
            $error = "❌ Database Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Course - TrainAI Trainer</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .create-course-container {
            max-width: 700px;
            margin: 20px auto;
        }
        
        .form-section {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-section h2 {
            color: #333;
            margin-top: 0;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: Arial, sans-serif;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 5px rgba(102, 126, 234, 0.3);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-help {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
        
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        
        .file-input-wrapper input[type=file] {
            display: none;
        }
        
        .file-input-label {
            display: block;
            padding: 12px;
            background: #f5f5f5;
            border: 2px dashed #667eea;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .file-input-label:hover {
            background: #eef;
        }
        
        .file-name {
            color: #667eea;
            font-weight: bold;
            margin-top: 5px;
        }
        
        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-submit:active {
            transform: translateY(0);
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        
        .alert-error {
            background: #FEE;
            color: #C00;
            border-left-color: #C00;
        }
        
        .alert-success {
            background: #EFE;
            color: #080;
            border-left-color: #080;
        }
    </style>
</head>
<body>
    <!-- Sidebar Navigation -->
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
            <a href="../../pages/trainer/create-course.php" class="nav-item active">
                <span class="nav-icon">➕</span>
                <span class="nav-text">Create Course</span>
            </a>
            <a href="../../pages/trainer/students.php" class="nav-item">
                <span class="nav-icon">👥</span>
                <span class="nav-text">Students</span>
            </a>
            <a href="../../pages/trainer/analytics.php" class="nav-item">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Analytics</span>
            </a>
            <a href="../../pages/trainer/settings.php" class="nav-item">
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
                <h2>➕ Create New Course</h2>
            </div>
        </div>

        <div class="dashboard-container">
            <div class="create-course-container">
                <div class="form-section">
                    <h2>📝 Course Information</h2>

                    <?php if ($error): ?>
                        <div class="alert alert-error">
                            <strong>Error!</strong><br>
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <strong>Success!</strong><br>
                            <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>

                    <!-- FORM WITH FILE UPLOAD -->
                    <form method="POST" enctype="multipart/form-data">
                        
                        <!-- Course Name -->
                        <div class="form-group">
                            <label for="course_name">📚 Course Name *</label>
                            <input 
                                type="text" 
                                id="course_name"
                                name="course_name" 
                                placeholder="e.g., Python Programming Basics" 
                                required
                                value="<?php echo htmlspecialchars($_POST['course_name'] ?? ''); ?>"
                            >
                            <div class="form-help">Enter a descriptive course name</div>
                        </div>

                        <!-- Course Code -->
                        <div class="form-group">
                            <label for="course_code">🔖 Course Code *</label>
                            <input 
                                type="text" 
                                id="course_code"
                                name="course_code" 
                                placeholder="e.g., PY101" 
                                required
                                value="<?php echo htmlspecialchars($_POST['course_code'] ?? ''); ?>"
                            >
                            <div class="form-help">Unique code (e.g., PY101, WEB201, etc.)</div>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description">📝 Description</label>
                            <textarea 
                                id="description"
                                name="description" 
                                placeholder="Describe your course..."
                            ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <div class="form-help">What will students learn in this course?</div>
                        </div>

                        <!-- Category -->
                        <div class="form-group">
                            <label for="category">📂 Category *</label>
                            <select id="category" name="category" required>
                                <option value="">Select a category</option>
                                <option value="Programming" <?php echo ($_POST['category'] ?? '') === 'Programming' ? 'selected' : ''; ?>>Programming</option>
                                <option value="Web Development" <?php echo ($_POST['category'] ?? '') === 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                                <option value="Data Science" <?php echo ($_POST['category'] ?? '') === 'Data Science' ? 'selected' : ''; ?>>Data Science</option>
                                <option value="Cloud" <?php echo ($_POST['category'] ?? '') === 'Cloud' ? 'selected' : ''; ?>>Cloud</option>
                                <option value="Mobile Development" <?php echo ($_POST['category'] ?? '') === 'Mobile Development' ? 'selected' : ''; ?>>Mobile Development</option>
                                <option value="Database" <?php echo ($_POST['category'] ?? '') === 'Database' ? 'selected' : ''; ?>>Database</option>
                                <option value="Design" <?php echo ($_POST['category'] ?? '') === 'Design' ? 'selected' : ''; ?>>Design</option>
                                <option value="Other" <?php echo ($_POST['category'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>

                        <!-- Difficulty Level -->
                        <div class="form-group">
                            <label for="difficulty">⭐ Difficulty Level *</label>
                            <select id="difficulty" name="difficulty" required>
                                <option value="Beginner" <?php echo ($_POST['difficulty'] ?? 'Beginner') === 'Beginner' ? 'selected' : ''; ?>>Beginner</option>
                                <option value="Intermediate" <?php echo ($_POST['difficulty'] ?? '') === 'Intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                                <option value="Advanced" <?php echo ($_POST['difficulty'] ?? '') === 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                            </select>
                        </div>

                        <!-- Duration -->
                        <div class="form-group">
                            <label for="duration_hours">⏱️ Duration (hours) *</label>
                            <input 
                                type="number" 
                                id="duration_hours"
                                name="duration_hours" 
                                placeholder="e.g., 24" 
                                min="1"
                                required
                                value="<?php echo htmlspecialchars($_POST['duration_hours'] ?? ''); ?>"
                            >
                            <div class="form-help">Total hours for this course</div>
                        </div>

                        <!-- FILE UPLOAD - THUMBNAIL IMAGE -->
                        <div class="form-group">
                            <label for="thumbnail">🖼️ Course Thumbnail Image</label>
                            <div class="file-input-wrapper">
                                <input 
                                    type="file" 
                                    id="thumbnail"
                                    name="thumbnail" 
                                    accept="image/jpeg,image/png,image/gif,image/webp"
                                    onchange="updateFileName(this)"
                                >
                                <label for="thumbnail" class="file-input-label">
                                    📁 Click to upload or drag and drop
                                    <div class="file-name" id="file-name"></div>
                                </label>
                            </div>
                            <div class="form-help">Upload JPG, PNG, GIF, or WebP (Max 2MB)</div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" class="btn-submit">
                            ✅ Create Course
                        </button>
                    </form>
                </div>

                <!-- Help Section -->
                <div class="form-section" style="margin-top: 20px; background: #f9f9f9;">
                    <h3>ℹ️ Tips for Creating a Course</h3>
                    <ul style="line-height: 1.8;">
                        <li><strong>Course Name:</strong> Make it descriptive and attractive</li>
                        <li><strong>Course Code:</strong> Must be unique (e.g., PY101, JAVA202)</li>
                        <li><strong>Category:</strong> Helps students find your course</li>
                        <li><strong>Difficulty:</strong> Set realistic level for students</li>
                        <li><strong>Duration:</strong> Accurate hours for proper planning</li>
                        <li><strong>Thumbnail:</strong> Professional image increases enrollment (JPG/PNG best)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Update filename when file is selected
        function updateFileName(input) {
            const fileName = input.files[0]?.name || '';
            document.getElementById('file-name').textContent = fileName ? '✅ ' + fileName : '';
        }
        
        // Drag and drop support
        const fileInput = document.getElementById('thumbnail');
        const fileLabel = document.querySelector('.file-input-label');
        
        fileLabel.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileLabel.style.background = '#eef';
        });
        
        fileLabel.addEventListener('dragleave', () => {
            fileLabel.style.background = '#f5f5f5';
        });
        
        fileLabel.addEventListener('drop', (e) => {
            e.preventDefault();
            fileInput.files = e.dataTransfer.files;
            updateFileName(fileInput);
            fileLabel.style.background = '#f5f5f5';
        });
    </script>

    <script src="../../assets/js/dashboard.js"></script>
</body>
</html>

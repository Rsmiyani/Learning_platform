<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if ($_SESSION['role'] !== 'trainer') {
    header('Location: ../../login.php');
    exit;
}

$trainer_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? null;
$success_message = '';
$error_message = '';

$pdo = getDBConnection();

// Verify course belongs to trainer
$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmt->execute([$course_id, $trainer_id]);
$course = $stmt->fetch();

if (!$course) {
    die("<div style='padding: 40px; text-align: center; background: #fee2e2; color: #dc2626; border-radius: 8px;'>⚠️ Access denied! This course doesn't belong to you.</div>");
}

// Handle Add Module - FIXED QUERY
if ($_POST && $_POST['action'] === 'add_module') {
    $module_title = $_POST['module_title'] ?? '';
    $module_description = $_POST['module_description'] ?? '';
    
    if (empty($module_title)) {
        $error_message = "Module title is required!";
    } else {
        try {
            // Get max module_order first
            $stmt = $pdo->prepare("SELECT COALESCE(MAX(module_order), 0) as max_order FROM course_modules WHERE course_id = ?");
            $stmt->execute([$course_id]);
            $result = $stmt->fetch();
            $next_order = ($result['max_order'] ?? 0) + 1;
            
            // Now insert the module
            $stmt = $pdo->prepare("
                INSERT INTO course_modules (course_id, module_title, module_description, module_order)
                VALUES (?, ?, ?, ?)
            ");
            if ($stmt->execute([$course_id, $module_title, $module_description, $next_order])) {
                $success_message = "✓ Module added successfully!";
                
                // Send notifications to all enrolled students
                $stmt = $pdo->prepare("SELECT user_id FROM course_enrollments WHERE course_id = ?");
                $stmt->execute([$course_id]);
                $enrolled_students = $stmt->fetchAll();
                
                foreach ($enrolled_students as $student) {
                    $stmt = $pdo->prepare("
                        INSERT INTO notifications (user_id, notification_type, title, message, created_at)
                        VALUES (?, 'module_added', ?, ?, NOW())
                    ");
                    $stmt->execute([
                        $student['user_id'],
                        'New Module Added! 📚',
                        "A new module '{$module_title}' has been added to {$course['course_name']}"
                    ]);
                }
            }
        } catch (Exception $e) {
            $error_message = "Error adding module: " . $e->getMessage();
        }
    }
}

// Handle Add Lesson - UPDATED WITH PDF SUPPORT
if ($_POST && $_POST['action'] === 'add_lesson') {
    $module_id = $_POST['module_id'] ?? '';
    $lesson_title = $_POST['lesson_title'] ?? '';
    $lesson_description = $_POST['lesson_description'] ?? '';
    $video_url = $_POST['video_url'] ?? '';
    $pdf_url = $_POST['pdf_url'] ?? '';
    
    // Determine content type
    $content_type = 'video';
    if (!empty($video_url) && !empty($pdf_url)) {
        $content_type = 'both';
    } elseif (!empty($pdf_url)) {
        $content_type = 'pdf';
    }
    
    if (empty($lesson_title) || empty($module_id)) {
        $error_message = "Lesson title and module are required!";
    } elseif (empty($video_url) && empty($pdf_url)) {
        $error_message = "Please provide at least a video URL or PDF URL!";
    } else {
        try {
            // Get max lesson_order first
            $stmt = $pdo->prepare("SELECT COALESCE(MAX(lesson_order), 0) as max_order FROM module_lessons WHERE module_id = ?");
            $stmt->execute([$module_id]);
            $result = $stmt->fetch();
            $next_order = ($result['max_order'] ?? 0) + 1;
            
            // Now insert the lesson with PDF support
            $stmt = $pdo->prepare("
                INSERT INTO module_lessons (module_id, lesson_title, lesson_description, video_url, pdf_url, content_type, lesson_order)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmt->execute([$module_id, $lesson_title, $lesson_description, $video_url, $pdf_url, $content_type, $next_order])) {
                $success_message = "✓ Lesson added successfully!";
            }
        } catch (Exception $e) {
            $error_message = "Error adding lesson: " . $e->getMessage();
        }
    }
}

// Get all modules
$stmt = $pdo->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY module_order");
$stmt->execute([$course_id]);
$modules = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Course Modules - <?php echo htmlspecialchars($course['course_name']); ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .module-container {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #1f2937;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn-submit {
            background: #4a9d9a;
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background: #2d7a77;
            transform: translateY(-2px);
        }
        
        .module-item {
            background: #f8fafc;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 4px solid #4a9d9a;
        }
        
        .module-title {
            font-size: 18px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
        }
        
        .lesson-item {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 6px;
            border-left: 3px solid #d0f0f0;
            font-size: 14px;
        }
        
        .success {
            background: #dcfce7;
            color: #16a34a;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #16a34a;
        }
        
        .error {
            background: #fee2e2;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
        }
        
        .back-btn {
            background: #e5e7eb;
            color: #1f2937;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: #d1d5db;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="../../dashboard/trainer/" class="sidebar-logo">
                <span class="logo-icon">👨‍🏫</span>
                <span class="logo-text">
                    <strong>TrainAI</strong>
                    <small>Trainer</small>
                </span>
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
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h2>📚 Manage Course Modules</h2>
            </div>
        </div>

        <div class="dashboard-container">
            <div class="module-container">
                <a href="../../pages/trainer/my-courses.php" class="back-btn">← Back to Courses</a>

                <h2 style="margin-bottom: 24px; color: #1f2937;"><?php echo htmlspecialchars($course['course_name']); ?></h2>

                <?php if (!empty($success_message)): ?>
                    <div class="success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <!-- Add Module Form -->
                <h3 style="margin-bottom: 20px; font-size: 18px; color: #1f2937; margin-top: 30px;">➕ Add New Module</h3>
                <form method="POST" style="background: #f0f2f5; padding: 20px; border-radius: 8px; margin-bottom: 40px;">
                    <input type="hidden" name="action" value="add_module">
                    
                    <div class="form-group">
                        <label>Module Title *</label>
                        <input type="text" name="module_title" placeholder="e.g., Introduction to Python" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Module Description</label>
                        <textarea name="module_description" placeholder="Describe what students will learn in this module..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">Add Module</button>
                </form>

                <!-- Modules & Lessons List -->
                <h3 style="margin-bottom: 20px; font-size: 18px; color: #1f2937;">📋 Course Modules</h3>
                
                <?php if (count($modules) > 0): ?>
                    <?php foreach ($modules as $module): ?>
                        <?php 
                        $stmt = $pdo->prepare("SELECT * FROM module_lessons WHERE module_id = ? ORDER BY lesson_order");
                        $stmt->execute([$module['module_id']]);
                        $lessons = $stmt->fetchAll();
                        ?>
                        
                        <div class="module-item">
                            <div class="module-title">Module <?php echo $module['module_order']; ?>: <?php echo htmlspecialchars($module['module_title']); ?></div>
                            <p style="color: #6b7280; font-size: 14px; margin-bottom: 15px;"><?php echo htmlspecialchars($module['module_description']); ?></p>
                            
                            <div style="background: white; padding: 15px; border-radius: 6px; margin-bottom: 15px;">
                                <p style="font-weight: 600; margin-bottom: 10px; color: #1f2937;">🎥 Videos/Lessons (<?php echo count($lessons); ?>)</p>
                                <?php if (count($lessons) > 0): ?>
                                    <?php foreach ($lessons as $lesson): ?>
                                        <div class="lesson-item">
                                            <strong><?php echo htmlspecialchars($lesson['lesson_title']); ?></strong>
                                            <?php if ($lesson['lesson_description']): ?>
                                                <br><small style="color: #6b7280;"><?php echo htmlspecialchars($lesson['lesson_description']); ?></small>
                                            <?php endif; ?>
                                            <br>
                                            <?php if (!empty($lesson['video_url'])): ?>
                                                <small style="color: #4a9d9a;">📹 <a href="<?php echo htmlspecialchars($lesson['video_url']); ?>" target="_blank">Watch Video</a></small>
                                            <?php endif; ?>
                                            <?php if (!empty($lesson['pdf_url'])): ?>
                                                <?php if (!empty($lesson['video_url'])): ?> | <?php endif; ?>
                                                <small style="color: #dc2626;">📄 <a href="<?php echo htmlspecialchars($lesson['pdf_url']); ?>" target="_blank">View PDF</a></small>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p style="color: #9ca3af; font-size: 13px; font-style: italic;">No lessons added yet</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #6b7280; text-align: center; padding: 30px; background: #f8fafc; border-radius: 8px;">📭 No modules added yet. Create your first module above!</p>
                <?php endif; ?>

                <!-- Add Lesson Form -->
                <h3 style="margin-bottom: 20px; font-size: 18px; color: #1f2937; margin-top: 40px;">➕ Add Video/Lesson/PDF</h3>
                <form method="POST" style="background: #f0f2f5; padding: 20px; border-radius: 8px;">
                    <input type="hidden" name="action" value="add_lesson">
                    
                    <div class="form-group">
                        <label>Select Module *</label>
                        <select name="module_id" required>
                            <option value="">Choose a module...</option>
                            <?php foreach ($modules as $module): ?>
                                <option value="<?php echo $module['module_id']; ?>">
                                    Module <?php echo $module['module_order']; ?>: <?php echo htmlspecialchars($module['module_title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Lesson Title *</label>
                        <input type="text" name="lesson_title" placeholder="e.g., Variables and Data Types" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Lesson Description</label>
                        <textarea name="lesson_description" placeholder="What will students learn in this lesson?"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>📹 Video URL (YouTube, Vimeo, etc.)</label>
                        <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=... or https://vimeo.com/...">
                        <small style="color: #6b7280; font-size: 12px;">Optional: Provide a video link for this lesson</small>
                    </div>
                    
                    <div class="form-group">
                        <label>📄 PDF URL (Google Drive, Dropbox, etc.)</label>
                        <input type="url" name="pdf_url" placeholder="https://drive.google.com/file/d/... or https://www.dropbox.com/...">
                        <small style="color: #6b7280; font-size: 12px;">Optional: Provide a PDF link for this lesson</small>
                    </div>
                    
                    <div style="background: #dbeafe; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-size: 13px; color: #1e40af;">
                        💡 <strong>Tip:</strong> You can add either a video URL, PDF URL, or both! At least one is required.
                    </div>
                    
                    <button type="submit" class="btn-submit">Add Lesson</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if ($_SESSION['role'] !== 'trainee') {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$pdo = getDBConnection();
$course_id = $_GET['course_id'] ?? null;

if (!$course_id) {
    header('Location: ./my-courses.php');
    exit;
}

// Get course details
$stmt = $pdo->prepare("SELECT c.* FROM courses c WHERE c.course_id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    header('Location: ./my-courses.php');
    exit;
}

// Verify user is enrolled in this course
$stmt = $pdo->prepare("SELECT ce.* FROM course_enrollments ce WHERE ce.user_id = ? AND ce.course_id = ?");
$stmt->execute([$user_id, $course_id]);
$enrollment = $stmt->fetch();

if (!$enrollment) {
    header('Location: ./my-courses.php');
    exit;
}

// Get all modules for this course
$stmt = $pdo->prepare("SELECT * FROM course_modules WHERE course_id = ? ORDER BY module_order ASC");
$stmt->execute([$course_id]);
$modules = $stmt->fetchAll();

// Get lessons for each module
$modulesWithLessons = [];
foreach ($modules as $module) {
    $stmt = $pdo->prepare("SELECT * FROM module_lessons WHERE module_id = ? ORDER BY lesson_order ASC");
    $stmt->execute([$module['module_id']]);
    $lessons = $stmt->fetchAll();
    $module['lessons'] = $lessons;
    $modulesWithLessons[] = $module;
}

// Check if exam exists
$exam_stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM exam_questions WHERE course_id = ?");
$exam_stmt->execute([$course_id]);
$has_exam = $exam_stmt->fetch()['cnt'] > 0;

// Check if exam already taken
$result_stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM exam_results WHERE user_id = ? AND course_id = ?");
$result_stmt->execute([$user_id, $course_id]);
$exam_taken = $result_stmt->fetch()['cnt'] > 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Modules - <?php echo htmlspecialchars($course['course_name']); ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            overflow-y: auto;
            overflow-x: hidden;
            height: 100vh;
            background: #f5f5f5;
            scroll-behavior: smooth;
        }

        /* Custom Scrollbar */
        body::-webkit-scrollbar {
            width: 10px;
        }

        body::-webkit-scrollbar-track {
            background: #e0e0e0;
            border-radius: 10px;
        }

        body::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #009B95 0%, #008080 100%);
            border-radius: 10px;
            border: 2px solid #f5f5f5;
        }

        body::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #008080 0%, #006666 100%);
        }

        .course-modules-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
            min-height: 100vh;
        }

        .course-header {
            margin-bottom: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #009B95 0%, #008080 100%);
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .course-header h1 {
            margin: 0 0 10px 0;
            font-size: 28px;
        }

        .course-header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #009B95;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: #008080;
            transform: translateX(-5px);
        }

        .modules-section {
            display: grid;
            gap: 20px;
        }

        .module-item {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .module-item:hover {
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            transform: translateY(-2px);
        }

        .module-header {
            background: linear-gradient(135deg, #009B95 0%, #008080 100%);
            color: white;
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            user-select: none;
            transition: all 0.3s ease;
        }

        .module-header:hover {
            background: linear-gradient(135deg, #008080 0%, #006666 100%);
        }

        .module-title {
            font-size: 18px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .module-toggle {
            font-size: 20px;
            transition: transform 0.3s ease;
        }

        .module-toggle.active {
            transform: rotate(180deg);
        }

        .module-content {
            display: none;
            padding: 20px;
            background: #fafafa;
        }

        .module-content.active {
            display: block;
        }

        .lesson-item {
            background: white;
            padding: 18px;
            margin-bottom: 15px;
            border-left: 4px solid #009B95;
            border-radius: 4px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .lesson-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateX(5px);
        }

        .lesson-item:last-child {
            margin-bottom: 0;
        }

        .lesson-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            gap: 15px;
        }

        .lesson-title {
            color: #333;
            font-weight: 600;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }

        .lesson-badge {
            background: #009B95;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            white-space: nowrap;
        }

        .video-link-section {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e0e0e0;
        }

        .video-label {
            color: #009B95;
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 10px;
            display: block;
        }

        .video-link-container {
            display: flex;
            align-items: center;
            gap: 12px;
            background: #f0f8f7;
            padding: 12px 15px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .video-link-container:hover {
            background: #e0f2f0;
            transform: translateX(3px);
        }

        .video-icon {
            color: #009B95;
            font-size: 20px;
            flex-shrink: 0;
        }

        .video-url {
            color: #009B95;
            text-decoration: none;
            font-weight: 500;
            flex: 1;
            word-break: break-all;
            font-size: 13px;
            transition: color 0.3s ease;
        }

        .video-url:hover {
            color: #008080;
            text-decoration: underline;
        }

        .watch-btn {
            background: #009B95;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .watch-btn:hover {
            background: #008080;
            transform: scale(1.05);
        }

        .empty-lesson {
            padding: 30px 20px;
            text-align: center;
            color: #999;
            background: white;
            border-radius: 4px;
            border-left: 4px solid #ddd;
        }

        .no-modules {
            background: white;
            padding: 50px 20px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .no-modules h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 20px;
        }

        .no-modules p {
            margin: 0;
            color: #666;
        }

        .exam-section {
            background: white;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin-top: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, #fafafa 0%, #f0f0f0 100%);
        }

        .exam-section h3 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 22px;
        }

        .exam-section p {
            margin: 0 0 25px 0;
            color: #666;
            font-size: 15px;
        }

        .take-exam-btn, .view-result-btn {
            display: inline-block;
            padding: 14px 40px;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .take-exam-btn {
            background: linear-gradient(135deg, #009B95 0%, #008080 100%);
        }

        .take-exam-btn:hover {
            background: linear-gradient(135deg, #008080 0%, #006666 100%);
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,155,149,0.3);
        }

        .view-result-btn {
            background: #6c757d;
        }

        .view-result-btn:hover {
            background: #5a6268;
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .lesson-header {
                flex-direction: column;
            }

            .video-link-container {
                flex-wrap: wrap;
            }

            .course-header h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="course-modules-container">
        <a href="./my-courses.php" class="back-link">← Back to My Courses</a>

        <div class="course-header">
            <h1>📚 <?php echo htmlspecialchars($course['course_name']); ?></h1>
            <p>Modules & Lessons</p>
        </div>

        <?php if (!empty($modulesWithLessons)): ?>
            <div class="modules-section">
                <?php foreach ($modulesWithLessons as $index => $module): ?>
                    <div class="module-item">
                        <div class="module-header" onclick="toggleModule(this)">
                            <div class="module-title">
                                <span>📖</span>
                                <span><?php echo !empty($module['module_title']) ? htmlspecialchars($module['module_title']) : 'Module'; ?></span>
                            </div>
                            <span class="module-toggle" id="toggle-<?php echo htmlspecialchars((string)$index); ?>">▼</span>
                        </div>
                        <div class="module-content" id="content-<?php echo htmlspecialchars((string)$index); ?>">
                            <?php if (!empty($module['lessons'])): ?>
                                <?php foreach ($module['lessons'] as $lesson): ?>
                                    <div class="lesson-item">
                                        <div class="lesson-header">
                                            <div class="lesson-title">
                                                <span>📝</span>
                                                <span><?php echo !empty($lesson['lesson_title']) ? htmlspecialchars($lesson['lesson_title']) : 'Lesson'; ?></span>
                                            </div>
                                            <span class="lesson-badge">Lesson <?php echo !empty($lesson['lesson_order']) ? htmlspecialchars((string)$lesson['lesson_order']) : '0'; ?></span>
                                        </div>

                                        <?php if (!empty($lesson['video_url'])): ?>
                                            <div class="video-link-section">
                                                <span class="video-label">📹 Video Link:</span>
                                                <div class="video-link-container">
                                                    <span class="video-icon">▶️</span>
                                                    <a href="<?php echo htmlspecialchars($lesson['video_url']); ?>" class="video-url" target="_blank" title="<?php echo htmlspecialchars($lesson['video_url']); ?>">
                                                        <?php echo htmlspecialchars(substr($lesson['video_url'], 0, 50)); ?>...
                                                    </a>
                                                    <button class="watch-btn" 
                                                            onclick="trackVideoWatch(<?php echo $lesson['lesson_id']; ?>, <?php echo $course_id; ?>, '<?php echo htmlspecialchars($lesson['video_url']); ?>')">
                                                        ▶️ Watch
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($lesson['pdf_url'])): ?>
                                            <div class="video-link-section">
                                                <span class="video-label">📄 PDF Document:</span>
                                                <div class="video-link-container">
                                                    <span class="video-icon">📑</span>
                                                    <a href="<?php echo htmlspecialchars($lesson['pdf_url']); ?>" class="video-url" target="_blank" title="<?php echo htmlspecialchars($lesson['pdf_url']); ?>">
                                                        <?php echo htmlspecialchars(substr($lesson['pdf_url'], 0, 50)); ?>...
                                                    </a>
                                                    <button class="watch-btn" style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);"
                                                            onclick="window.open('<?php echo htmlspecialchars($lesson['pdf_url']); ?>', '_blank')">
                                                        📄 View PDF
                                                    </button>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-lesson">
                                    <p>📚 No lessons in this module yet.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if ($has_exam): ?>
                <div class="exam-section">
                    <h3>🎯 Final Exam</h3>
                    <p>You have completed all course modules. Ready to test your knowledge?</p>

                    <?php if ($exam_taken): ?>
                        <a href="exam-result.php?course_id=<?php echo htmlspecialchars((string)$course_id); ?>" class="view-result-btn">
                            📋 View Your Exam Result
                        </a>
                    <?php else: ?>
                        <a href="take-exam.php?course_id=<?php echo htmlspecialchars((string)$course_id); ?>" class="take-exam-btn">
                            📝 Take the Final Exam
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="no-modules">
                <h3>📚 No Modules Available</h3>
                <p>No modules have been created for this course yet.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function toggleModule(header) {
            const index = header.nextElementSibling.id.split('-')[1];
            const content = document.getElementById('content-' + index);
            const toggle = document.getElementById('toggle-' + index);

            content.classList.toggle('active');
            toggle.classList.toggle('active');
        }

        // Track video watch time
        let videoStartTime = null;
        let currentLessonId = null;
        let currentCourseId = null;

        function trackVideoWatch(lessonId, courseId, videoUrl) {
            // Store current lesson info
            currentLessonId = lessonId;
            currentCourseId = courseId;
            
            // Record start time
            videoStartTime = Date.now();
            
            // Open video in new window
            const videoWindow = window.open(videoUrl, '_blank');
            
            // Estimate watch duration (assume user watches for at least 5 minutes)
            // In a real app, you'd track actual video playback time
            setTimeout(() => {
                if (videoStartTime) {
                    const watchDuration = (Date.now() - videoStartTime) / 1000; // Convert to seconds
                    
                    // Only track if watched for more than 30 seconds
                    if (watchDuration > 30) {
                        sendWatchTime(lessonId, courseId, watchDuration);
                    }
                }
            }, 300000); // Check after 5 minutes
        }

        function sendWatchTime(lessonId, courseId, duration) {
            fetch('../../handlers/track-video-watch.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    lesson_id: lessonId,
                    course_id: courseId,
                    watch_duration: duration
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('✅ Watch time tracked:', data.hours, 'hours');
                }
            })
            .catch(error => console.error('Error tracking watch time:', error));
        }

        // Track when user closes the page (estimate total session time)
        window.addEventListener('beforeunload', function() {
            if (videoStartTime && currentLessonId) {
                const sessionDuration = (Date.now() - videoStartTime) / 1000;
                if (sessionDuration > 30) {
                    // Use sendBeacon for reliable tracking on page unload
                    const data = JSON.stringify({
                        lesson_id: currentLessonId,
                        course_id: currentCourseId,
                        watch_duration: sessionDuration
                    });
                    navigator.sendBeacon('../../handlers/track-video-watch.php', data);
                }
            }
        });

        // Auto-expand first module on page load
        document.addEventListener('DOMContentLoaded', function() {
            const firstModuleHeader = document.querySelector('.module-header');
            if (firstModuleHeader) {
                firstModuleHeader.click();
            }
        });
    </script>
</body>
</html>

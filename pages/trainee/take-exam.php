<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
require_once '../../config/email.php';
initSession();

if ($_SESSION['role'] !== 'trainee') {
    header('Location: ../../login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$course_id = $_GET['course_id'] ?? null;

$pdo = getDBConnection();

// Get course
$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) {
    die("Course not found!");
}

// Check if trainee is enrolled
$stmt = $pdo->prepare("SELECT * FROM course_enrollments WHERE user_id = ? AND course_id = ?");
$stmt->execute([$user_id, $course_id]);
if (!$stmt->fetch()) {
    die("You are not enrolled in this course!");
}

// Handle exam submission
if ($_POST && $_POST['action'] === 'submit_exam') {
    $total_correct = 0;
    $total_questions = 0;
    
    // Get all questions
    $stmt = $pdo->prepare("SELECT * FROM exam_questions WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $questions = $stmt->fetchAll();
    
    $total_questions = count($questions);
    
    // Check each answer
    foreach ($questions as $question) {
        $user_answer = $_POST['question_' . $question['question_id']] ?? null;
        $is_correct = false;
        
        if ($question['question_type'] === 'multiple_choice') {
            $stmt = $pdo->prepare("SELECT is_correct FROM question_options WHERE option_id = ? AND question_id = ?");
            $stmt->execute([$user_answer, $question['question_id']]);
            $option = $stmt->fetch();
            $is_correct = $option && $option['is_correct'] ? true : false;
            
        } elseif ($question['question_type'] === 'true_false') {
            $stmt = $pdo->prepare("SELECT is_correct FROM question_options WHERE option_order = ? AND question_id = ?");
            $stmt->execute([$user_answer, $question['question_id']]);
            $option = $stmt->fetch();
            $is_correct = $option && $option['is_correct'] ? true : false;
        }
        
        if ($is_correct) {
            $total_correct++;
        }
        
        // Store response
        $stmt = $pdo->prepare("
            INSERT INTO student_exam_responses 
            (user_id, course_id, question_id, selected_option_id, is_correct)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$user_id, $course_id, $question['question_id'], $user_answer, $is_correct ? 1 : 0]);
    }
    
    // Calculate percentage
    $score_percentage = ($total_correct / $total_questions) * 100;
    $passed = $score_percentage >= 75 ? 1 : 0;
    
    // Store result
    $stmt = $pdo->prepare("
        INSERT INTO exam_results 
        (user_id, course_id, total_questions, correct_answers, score_percentage, passed)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$user_id, $course_id, $total_questions, $total_correct, $score_percentage, $passed]);
    
    // Get course duration for study logs
    $stmt = $pdo->prepare("SELECT duration_hours, course_name FROM courses WHERE course_id = ?");
    $stmt->execute([$course_id]);
    $course_info = $stmt->fetch();
    $course_duration = $course_info['duration_hours'] ?? 0;
    $course_name = $course_info['course_name'] ?? 'Course';
    
    // If passed, issue certificate and award points
    if ($passed) {
        try {
            // Generate unique certificate code
            $certificate_code = 'CERT-' . strtoupper(uniqid()) . '-' . date('YmdHis');
            $certificate_number = 'CERT-' . str_pad($user_id, 4, '0', STR_PAD_LEFT) . '-' . str_pad($course_id, 4, '0', STR_PAD_LEFT);
            
            // Check if already has certificate for this course
            $stmt = $pdo->prepare("SELECT * FROM user_certificates WHERE user_id = ? AND course_id = ?");
            $stmt->execute([$user_id, $course_id]);
            $existing = $stmt->fetch();
            
            if (!$existing) {
                // Insert certificate with CODE and NUMBER
                $stmt = $pdo->prepare("
                    INSERT INTO user_certificates 
                    (user_id, course_id, certificate_code, certificate_number, issued_date)
                    VALUES (?, ?, ?, ?, NOW())
                ");
                $stmt->execute([$user_id, $course_id, $certificate_code, $certificate_number]);
                
                // Award points for course completion (100 points)
                $stmt = $pdo->prepare("
                    INSERT INTO user_points (user_id, total_points, level)
                    VALUES (?, 100, 1)
                    ON DUPLICATE KEY UPDATE total_points = total_points + 100
                ");
                $stmt->execute([$user_id]);
                
                // Get new total points
                $stmt = $pdo->prepare("SELECT total_points FROM user_points WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $new_total_points = $stmt->fetch()['total_points'];
                
                // Update user level based on new total points
                updateUserLevel($pdo, $user_id);
                
                // Check and award achievement if milestone reached
                awardAchievementIfEligible($pdo, $user_id, $new_total_points);
                
                // Update enrollment status to completed
                $stmt = $pdo->prepare("
                    UPDATE course_enrollments 
                    SET status = 'completed', progress_percentage = 100
                    WHERE user_id = ? AND course_id = ?
                ");
                $stmt->execute([$user_id, $course_id]);
                
                // ★ NEW: Log study hours based on course duration
                if ($course_duration > 0) {
                    $stmt = $pdo->prepare("
                        INSERT INTO study_logs 
                        (user_id, study_date, hours_studied, courses_studied, activities)
                        VALUES (?, CURDATE(), ?, ?, ?)
                        ON DUPLICATE KEY UPDATE 
                            hours_studied = hours_studied + VALUES(hours_studied),
                            courses_studied = CONCAT(courses_studied, ', ', VALUES(courses_studied)),
                            activities = CONCAT(activities, ', ', VALUES(activities))
                    ");
                    $stmt->execute([
                        $user_id, 
                        $course_duration, 
                        $course_name,
                        'Completed course and passed exam'
                    ]);
                }
                
                // Send certificate email
                if (!$existing) {
                    // Get user details for email
                    $stmt = $pdo->prepare("SELECT first_name, last_name, email FROM users WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $user_info = $stmt->fetch();
                    
                    if ($user_info) {
                        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
                        $host = $_SERVER['HTTP_HOST'];
                        $certificate_link = $protocol . '://' . $host . '/Learning-Platform - Copy/pages/trainee/certificates.php';
                        
                        $mailer = getMailer();
                        $email_sent = $mailer->sendCertificateEmail(
                            $user_info['email'],
                            $user_info['first_name'],
                            $course_name,
                            $certificate_code,
                            $certificate_link
                        );
                        
                        if ($email_sent) {
                            error_log("Certificate email sent successfully to: " . $user_info['email']);
                        } else {
                            error_log("Failed to send certificate email to: " . $user_info['email']);
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Certificate insertion error: " . $e->getMessage());
        }
    }
    
    // Send exam result email regardless of pass/fail
    try {
        $stmt = $pdo->prepare("SELECT first_name, email FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $user_info = $stmt->fetch();
        
        if ($user_info) {
            $mailer = getMailer();
            $email_sent = $mailer->sendExamResultEmail(
                $user_info['email'],
                $user_info['first_name'],
                $course_name,
                round($score_percentage, 2),
                $passed
            );
            
            if ($email_sent) {
                error_log("Exam result email sent successfully to: " . $user_info['email']);
            } else {
                error_log("Failed to send exam result email to: " . $user_info['email']);
            }
        }
    } catch (Exception $e) {
        error_log("Error sending exam result email: " . $e->getMessage());
    }
    
    header('Location: exam-result.php?course_id=' . $course_id);
    exit;
}

// Get all questions
$stmt = $pdo->prepare("SELECT * FROM exam_questions WHERE course_id = ? ORDER BY question_order");
$stmt->execute([$course_id]);
$questions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam - <?php echo htmlspecialchars($course['course_name']); ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .exam-wrapper {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .question-container {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8fafc;
            border-radius: 8px;
            border-left: 4px solid #4a9d9a;
        }
        
        .question-text {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 15px;
            font-size: 16px;
        }
        
        .option-label {
            display: flex;
            align-items: center;
            margin-bottom: 12px;
            padding: 12px;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .option-label:hover {
            background: #f0f0f0;
        }
        
        .option-label input {
            margin-right: 12px;
            cursor: pointer;
        }
        
        .btn-submit {
            background: #4a9d9a;
            color: white;
            padding: 14px 32px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-submit:hover {
            background: #2d7a77;
            transform: translateY(-2px);
        }
        
        .exam-info {
            background: #d0f0f0;
            color: #4a9d9a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <a href="../../dashboard/trainee/" class="sidebar-logo">
                <span class="logo-icon">👨‍🎓</span>
                <span class="logo-text">
                    <strong>TrainAI</strong>
                    <small>Trainee</small>
                </span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h2>📝 Exam: <?php echo htmlspecialchars($course['course_name']); ?></h2>
            </div>
        </div>

        <div class="dashboard-container">
            <div class="exam-wrapper">
                <div class="exam-info">
                    ℹ️ Pass Score: 75% | Total Questions: <?php echo count($questions); ?>
                </div>

                <form method="POST">
                    <input type="hidden" name="action" value="submit_exam">
                    
                    <?php if (count($questions) > 0): ?>
                        <?php foreach ($questions as $index => $question): ?>
                            <div class="question-container">
                                <div class="question-text">Q<?php echo $index + 1; ?>: <?php echo htmlspecialchars($question['question_text']); ?></div>
                                
                                <?php if ($question['question_type'] === 'multiple_choice'): ?>
                                    <?php 
                                    $stmt = $pdo->prepare("SELECT * FROM question_options WHERE question_id = ? ORDER BY option_order");
                                    $stmt->execute([$question['question_id']]);
                                    $options = $stmt->fetchAll();
                                    ?>
                                    <?php foreach ($options as $option): ?>
                                        <label class="option-label">
                                            <input type="radio" name="question_<?php echo $question['question_id']; ?>" value="<?php echo $option['option_id']; ?>" required>
                                            <?php echo htmlspecialchars($option['option_text']); ?>
                                        </label>
                                    <?php endforeach; ?>
                                    
                                <?php elseif ($question['question_type'] === 'true_false'): ?>
                                    <label class="option-label">
                                        <input type="radio" name="question_<?php echo $question['question_id']; ?>" value="1" required>
                                        True
                                    </label>
                                    <label class="option-label">
                                        <input type="radio" name="question_<?php echo $question['question_id']; ?>" value="2" required>
                                        False
                                    </label>
                                    
                                <?php elseif ($question['question_type'] === 'short_answer'): ?>
                                    <textarea name="question_<?php echo $question['question_id']; ?>" placeholder="Enter your answer..." required style="width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 6px; min-height: 100px; font-family: inherit;"></textarea>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                        
                        <button type="submit" class="btn-submit">Submit Exam</button>
                    <?php else: ?>
                        <p style="text-align: center; color: #6b7280; padding: 30px; background: #f8fafc; border-radius: 8px;">No questions available for this exam yet.</p>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

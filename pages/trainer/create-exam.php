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

$pdo = getDBConnection();

// Verify course belongs to trainer
$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ? AND instructor_id = ?");
$stmt->execute([$course_id, $trainer_id]);
$course = $stmt->fetch();

if (!$course) {
    die("<div style='padding: 40px; text-align: center; background: #fee2e2; color: #dc2626; border-radius: 8px;'>⚠️ Access denied!</div>");
}

$success_message = '';
$error_message = '';

// Add Question - FIXED QUERY
if ($_POST && $_POST['action'] === 'add_question') {
    $question_text = $_POST['question_text'] ?? '';
    $question_type = $_POST['question_type'] ?? 'multiple_choice';
    
    if (empty($question_text)) {
        $error_message = "Question text is required!";
    } else {
        try {
            // Step 1: Get max question_order first
            $stmt = $pdo->prepare("SELECT COALESCE(MAX(question_order), 0) as max_order FROM exam_questions WHERE course_id = ?");
            $stmt->execute([$course_id]);
            $result = $stmt->fetch();
            $next_order = ($result['max_order'] ?? 0) + 1;
            
            // Step 2: Insert the question
            $stmt = $pdo->prepare("
                INSERT INTO exam_questions (course_id, question_text, question_type, question_order)
                VALUES (?, ?, ?, ?)
            ");
            if ($stmt->execute([$course_id, $question_text, $question_type, $next_order])) {
                $new_question_id = $pdo->lastInsertId();
                
                // Add options if multiple choice
                if ($question_type === 'multiple_choice') {
                    for ($i = 1; $i <= 4; $i++) {
                        $option_text = $_POST['option_' . $i] ?? '';
                        if (!empty($option_text)) {
                            $is_correct = isset($_POST['correct_option']) && $_POST['correct_option'] == $i ? 1 : 0;
                            $stmt = $pdo->prepare("INSERT INTO question_options (question_id, option_text, is_correct, option_order) VALUES (?, ?, ?, ?)");
                            $stmt->execute([$new_question_id, $option_text, $is_correct, $i]);
                        }
                    }
                } elseif ($question_type === 'true_false') {
                    $correct = isset($_POST['correct_option']) ? $_POST['correct_option'] : 1;
                    $stmt = $pdo->prepare("INSERT INTO question_options (question_id, option_text, is_correct, option_order) VALUES (?, ?, ?, ?), (?, ?, ?, ?)");
                    $stmt->execute([$new_question_id, 'True', $correct == 1 ? 1 : 0, 1, $new_question_id, 'False', $correct == 2 ? 1 : 0, 2]);
                }
                
                $success_message = "✓ Question added successfully!";
                
                // Check if this is the first question (new exam created)
                $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM exam_questions WHERE course_id = ?");
                $stmt->execute([$course_id]);
                $question_count = $stmt->fetch()['cnt'];
                
                // If this is the first question, notify students about new exam
                if ($question_count == 1) {
                    $stmt = $pdo->prepare("SELECT user_id FROM course_enrollments WHERE course_id = ?");
                    $stmt->execute([$course_id]);
                    $enrolled_students = $stmt->fetchAll();
                    
                    foreach ($enrolled_students as $student) {
                        $stmt = $pdo->prepare("
                            INSERT INTO notifications (user_id, notification_type, title, message, created_at)
                            VALUES (?, 'exam_added', ?, ?, NOW())
                        ");
                        $stmt->execute([
                            $student['user_id'],
                            'New Exam Available! 📝',
                            "An exam has been created for {$course['course_name']}. Test your knowledge now!"
                        ]);
                    }
                }
            }
        } catch (Exception $e) {
            $error_message = "Error adding question: " . $e->getMessage();
        }
    }
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
    <title>Create Exam - <?php echo htmlspecialchars($course['course_name']); ?></title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        .exam-container {
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
        
        .btn-submit {
            background: #4a9d9a;
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .btn-submit:hover {
            background: #2d7a77;
        }
        
        .question-item {
            background: #f8fafc;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 4px solid #4a9d9a;
        }
        
        .option-item {
            background: white;
            padding: 10px 15px;
            margin: 8px 0;
            border-radius: 6px;
            border-left: 3px solid #d0f0f0;
            font-size: 14px;
        }
        
        .option-correct {
            border-left-color: #10b981;
            background: #f0fdf4;
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
        </nav>
    </div>

    <div class="main-content">
        <div class="topbar">
            <div class="topbar-left">
                <h2>❓ Create Exam</h2>
            </div>
        </div>

        <div class="dashboard-container">
            <div class="exam-container">
                <a href="../../pages/trainer/my-courses.php" class="back-btn">← Back to Courses</a>

                <h2 style="margin-bottom: 24px; color: #1f2937;">Create Exam for <?php echo htmlspecialchars($course['course_name']); ?></h2>

                <?php if (!empty($success_message)): ?>
                    <div class="success"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <?php if (!empty($error_message)): ?>
                    <div class="error"><?php echo $error_message; ?></div>
                <?php endif; ?>

                <!-- Add Question Form -->
                <form method="POST" style="background: #f0f2f5; padding: 20px; border-radius: 8px; margin-bottom: 40px;">
                    <input type="hidden" name="action" value="add_question">
                    
                    <div class="form-group">
                        <label>Question Type</label>
                        <select name="question_type" id="question_type" onchange="updateOptions()">
                            <option value="multiple_choice">Multiple Choice</option>
                            <option value="true_false">True/False</option>
                            <option value="short_answer">Short Answer</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Question *</label>
                        <textarea name="question_text" placeholder="Enter your question here..." required></textarea>
                    </div>
                    
                    <div id="options_div">
                        <label style="font-weight: 700; margin-bottom: 15px; display: block;">Options (Mark correct answer)</label>
                        
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                        <div style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center;">
                            <input type="text" name="option_<?php echo $i; ?>" placeholder="Option <?php echo $i; ?>" style="flex: 1;">
                            <label style="margin: 0; white-space: nowrap;">
                                <input type="radio" name="correct_option" value="<?php echo $i; ?>"> Correct
                            </label>
                        </div>
                        <?php endfor; ?>
                    </div>
                    
                    <button type="submit" class="btn-submit">Add Question</button>
                </form>

                <!-- Questions List -->
                <h3 style="margin-bottom: 20px; font-size: 18px; color: #1f2937;">📝 Exam Questions (<?php echo count($questions); ?>)</h3>
                
                <?php if (count($questions) > 0): ?>
                    <?php foreach ($questions as $question): ?>
                        <?php 
                        $stmt = $pdo->prepare("SELECT * FROM question_options WHERE question_id = ? ORDER BY option_order");
                        $stmt->execute([$question['question_id']]);
                        $options = $stmt->fetchAll();
                        ?>
                        
                        <div class="question-item">
                            <p style="font-weight: 600; margin-bottom: 12px; color: #1f2937;">Q<?php echo $question['question_order']; ?>: <?php echo htmlspecialchars($question['question_text']); ?></p>
                            <small style="color: #6b7280; background: #e5e7eb; padding: 4px 8px; border-radius: 4px;"><?php echo strtoupper(str_replace('_', ' ', $question['question_type'])); ?></small>
                            
                            <?php if (count($options) > 0): ?>
                                <?php foreach ($options as $option): ?>
                                    <div class="option-item <?php echo $option['is_correct'] ? 'option-correct' : ''; ?>">
                                        <?php echo $option['is_correct'] ? '✓ ' : '○ '; ?>
                                        <?php echo htmlspecialchars($option['option_text']); ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #6b7280; text-align: center; padding: 30px; background: #f8fafc; border-radius: 8px;">No questions added yet. Create your first question above!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function updateOptions() {
            const type = document.getElementById('question_type').value;
            const optionsDiv = document.getElementById('options_div');
            
            if (type === 'true_false') {
                optionsDiv.innerHTML = `
                    <label style="font-weight: 700; margin-bottom: 15px; display: block;">Correct Answer</label>
                    <div style="margin-bottom: 15px;">
                        <label style="margin-right: 20px;">
                            <input type="radio" name="correct_option" value="1" checked> True
                        </label>
                        <label>
                            <input type="radio" name="correct_option" value="2"> False
                        </label>
                    </div>
                `;
            } else if (type === 'short_answer') {
                optionsDiv.innerHTML = '';
            } else {
                optionsDiv.innerHTML = `
                    <label style="font-weight: 700; margin-bottom: 15px; display: block;">Options (Mark correct answer)</label>
                    <div style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="option_1" placeholder="Option 1" style="flex: 1;">
                        <label style="margin: 0; white-space: nowrap;">
                            <input type="radio" name="correct_option" value="1"> Correct
                        </label>
                    </div>
                    <div style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="option_2" placeholder="Option 2" style="flex: 1;">
                        <label style="margin: 0; white-space: nowrap;">
                            <input type="radio" name="correct_option" value="2"> Correct
                        </label>
                    </div>
                    <div style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="option_3" placeholder="Option 3" style="flex: 1;">
                        <label style="margin: 0; white-space: nowrap;">
                            <input type="radio" name="correct_option" value="3"> Correct
                        </label>
                    </div>
                    <div style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center;">
                        <input type="text" name="option_4" placeholder="Option 4" style="flex: 1;">
                        <label style="margin: 0; white-space: nowrap;">
                            <input type="radio" name="correct_option" value="4"> Correct
                        </label>
                    </div>
                `;
            }
        }
    </script>
</body>
</html>

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
    die("Course ID is required");
}

// Get latest exam result
$stmt = $pdo->prepare(" SELECT * FROM exam_results WHERE user_id = ? AND course_id = ? ORDER BY attempted_at DESC LIMIT 1 ");
$stmt->execute([$user_id, $course_id]);
$result = $stmt->fetch();

if (!$result) {
    die("No exam result found!");
}

// Get course
$stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

// Get certificate if passed
$certificate = null;
if ($result['passed']) {
    $stmt = $pdo->prepare(" SELECT * FROM user_certificates WHERE user_id = ? AND course_id = ? LIMIT 1 ");
    $stmt->execute([$user_id, $course_id]);
    $certificate = $stmt->fetch();
}

// ★ NEW: Check if user has already rated this course
$existing_rating = null;
$stmt = $pdo->prepare("
    SELECT rating_id, rating_value FROM course_ratings
    WHERE user_id = ? AND course_id = ?
");
$stmt->execute([$user_id, $course_id]);
$existing_rating = $stmt->fetch();

$status_class = $result['passed'] ? 'success' : 'fail';
$status_icon = $result['passed'] ? '✓' : '✗';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results - TrainAI</title>
    <link rel="stylesheet" href="../../assets/css/dashboard.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            min-height: 100vh;
            background: #f5f5f5 !important;
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

        .exam-result-wrapper {
            display: flex !important;
            justify-content: center !important;
            align-items: flex-start !important;
            min-height: 100vh;
            width: 100%;
            padding: 40px 20px;
            box-sizing: border-box;
        }

        .result-card {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            max-width: 700px;
            width: 100%;
            text-align: center;
            margin: 0 auto !important;
        }

        .result-status.success {
            border-top: 5px solid #28a745;
        }

        .result-status.fail {
            border-top: 5px solid #dc3545;
        }

        .status-icon {
            font-size: 60px;
            margin: 20px 0;
        }

        .status-icon.success {
            color: #28a745;
        }

        .status-icon.fail {
            color: #dc3545;
        }

        .result-header h2 {
            font-size: 24px;
            margin: 15px 0;
            color: #333;
        }

        .result-score {
            font-size: 48px;
            font-weight: bold;
            color: #009B95;
            margin: 20px 0;
        }

        .result-message {
            font-size: 18px;
            color: #666;
            margin: 15px 0;
        }

        .result-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            text-align: left;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: bold;
            color: #555;
        }

        .detail-value {
            color: #333;
        }

        .certificate-section {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: #155724;
        }

        .certificate-section h3 {
            margin: 0 0 10px 0;
        }

        .certificate-btn {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 10px;
        }

        /* ★ NEW: RATING SECTION STYLES */
        .rating-section {
            background: #fff3cd;
            border: 2px solid #ffc107;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            color: #856404;
        }

        .rating-section h3 {
            margin: 0 0 15px 0;
        }

        .rating-btn {
            background: #ffc107;
            color: #333;
            padding: 10px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .rating-btn:hover {
            background: #ffb300;
            transform: scale(1.05);
        }

        .already-rated {
            background: #d4edda;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            color: #155724;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
            justify-content: center;
        }

        .btn-primary, .btn-secondary {
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #009B95;
            color: white;
        }

        .btn-primary:hover {
            background: #008080;
        }

        .btn-secondary {
            background: #e8e4d9;
            color: #333;
        }

        .btn-secondary:hover {
            background: #d9d3c3;
        }

        /* ★ NEW: RATING MODAL STYLES */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            padding: 40px;
            border-radius: 8px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            text-align: center;
        }

        .modal-header h2 {
            margin: 0 0 10px 0;
            color: #333;
        }

        .modal-header p {
            color: #666;
            margin-bottom: 20px;
        }

        .star-rating {
            display: flex;
            gap: 15px;
            justify-content: center;
            font-size: 50px;
            margin: 30px 0;
        }

        .star {
            cursor: pointer;
            transition: all 0.2s ease;
            color: #ccc;
        }

        .star:hover, .star.active {
            color: #FFD700;
            transform: scale(1.2);
        }

        .rating-label {
            text-align: center;
            margin: 15px 0;
            font-weight: bold;
            color: #009B95;
            font-size: 18px;
        }

        .review-textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Arial;
            font-size: 14px;
            min-height: 80px;
            box-sizing: border-box;
            margin: 15px 0;
            resize: vertical;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }

        .modal-btn {
            padding: 10px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .modal-btn-skip {
            background: #e8e4d9;
            color: #333;
        }

        .modal-btn-skip:hover {
            background: #d9d3c3;
        }

        .modal-btn-submit {
            background: #009B95;
            color: white;
        }

        .modal-btn-submit:hover {
            background: #008080;
        }
    </style>
</head>
<body>
    <div class="exam-result-wrapper">
        <div class="result-card result-status <?php echo $status_class; ?>">
            <!-- STATUS ICON -->
            <div class="status-icon <?php echo $status_class; ?>">
                <?php echo $status_icon; ?>
            </div>

            <!-- RESULT HEADER -->
            <div class="result-header">
                <h2><?php echo $result['passed'] ? 'Congratulations! 🎉' : 'Result'; ?></h2>
                <p><?php echo htmlspecialchars($course['course_name']); ?></p>
            </div>

            <!-- SCORE -->
            <div class="result-score">
                <?php echo number_format($result['score_percentage'], 1); ?>%
            </div>

            <!-- MESSAGE -->
            <div class="result-message">
                <?php 
                if ($result['passed']) {
                    echo "You have passed the final exam!";
                } else {
                    echo "You need at least 75% to pass and earn a certificate. Try again!";
                }
                ?>
            </div>

            <!-- RESULT DETAILS -->
            <div class="result-details">
                <div class="detail-row">
                    <span class="detail-label">Course:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($course['course_name']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Score:</span>
                    <span class="detail-value"><?php echo number_format($result['score_percentage'], 1); ?>%</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date:</span>
                    <span class="detail-value"><?php echo date('M d, Y', strtotime($result['attempted_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Status:</span>
                    <span class="detail-value"><?php echo $result['passed'] ? '✓ Passed' : '✗ Failed'; ?></span>
                </div>
            </div>

            <!-- CERTIFICATE SECTION -->
            <?php if ($result['passed'] && $certificate): ?>
                <div class="certificate-section">
                    <h3>✅ Your certificate has been issued successfully!</h3>
                    <p>🎉 You earned <strong>100 points</strong> for completing this course!</p>
                    <p>You can view and download your certificate from the Certificates page.</p>
                    <a href="certificates.php" class="certificate-btn">View Certificate</a>
                </div>
            <?php elseif ($result['passed'] && !$certificate): ?>
                <div class="certificate-section">
                    <h3>✅ Certificate Issued</h3>
                    <p>🎉 You earned <strong>100 points</strong> for completing this course!</p>
                    <p>Your certificate is being generated. Check back soon!</p>
                </div>
            <?php endif; ?>

            <!-- ★ NEW: RATING SECTION -->
            <?php if ($result['passed']): ?>
                <?php if (!$existing_rating): ?>
                    <div class="rating-section">
                        <h3>⭐ Rate This Course</h3>
                        <p>Help us improve! Please rate your experience with this course.</p>
                        <button class="rating-btn" onclick="openRatingModal()">
                            ⭐ Give Rating
                        </button>
                    </div>
                <?php else: ?>
                    <div class="already-rated">
                        <strong>✓ You already rated this course</strong>
                        <p>Your rating: <?php 
                            for ($i = 0; $i < 5; $i++) {
                                echo ($i < (int)$existing_rating['rating_value']) ? '★' : '☆';
                            }
                            echo ' ' . $existing_rating['rating_value'] . '/5';
                        ?></p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- BUTTONS -->
            <div class="button-group">
                <a href="my-courses.php" class="btn-secondary">← Back to Courses</a>
                <a href="../../dashboard/trainee/" class="btn-primary">Go to Dashboard</a>
            </div>
        </div>
    </div>

    <!-- ★ NEW: RATING MODAL -->
    <div id="ratingModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Rate This Course ⭐</h2>
                <p><?php echo htmlspecialchars($course['course_name']); ?></p>
            </div>

            <!-- STAR RATING -->
            <div class="star-rating" id="starRating">
                <span class="star" data-rating="1" onclick="setRating(1)">☆</span>
                <span class="star" data-rating="2" onclick="setRating(2)">☆</span>
                <span class="star" data-rating="3" onclick="setRating(3)">☆</span>
                <span class="star" data-rating="4" onclick="setRating(4)">☆</span>
                <span class="star" data-rating="5" onclick="setRating(5)">☆</span>
            </div>
            <div class="rating-label" id="ratingLabel">Click to rate</div>

            <!-- REVIEW TEXT -->
            <textarea id="reviewText" class="review-textarea" placeholder="Write your review (optional)..."></textarea>

            <!-- BUTTONS -->
            <div class="modal-buttons">
                <button class="modal-btn modal-btn-skip" onclick="skipRating()">Skip for Now</button>
                <button class="modal-btn modal-btn-submit" onclick="submitRating()">Submit Rating</button>
            </div>
        </div>
    </div>

    <!-- ★ NEW: RATING SCRIPT -->
    <script>
        let currentRating = 0;
        const courseId = <?php echo $course_id; ?>;

        function openRatingModal() {
            currentRating = 0;
            document.getElementById('ratingModal').classList.add('active');
            document.getElementById('reviewText').value = '';
            document.getElementById('ratingLabel').textContent = 'Click to rate';
            document.querySelectorAll('#starRating .star').forEach(s => s.classList.remove('active'));
        }

        function setRating(rating) {
            currentRating = rating;
            document.querySelectorAll('#starRating .star').forEach((star, index) => {
                if (index < rating) {
                    star.textContent = '★';
                    star.classList.add('active');
                } else {
                    star.textContent = '☆';
                    star.classList.remove('active');
                }
            });
            document.getElementById('ratingLabel').textContent = `You rated ${rating}/5 stars`;
        }

        function submitRating() {
    if (!currentRating) {
        alert('Please select a rating!');
        return;
    }

    fetch('../../handlers/rating-handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            course_id: courseId,
            rating: currentRating,
            review: document.getElementById('reviewText').value
        })
    })
    .then(response => {
        console.log('Status:', response.status);  // ✅ ADD THIS
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);  // ✅ ADD THIS
        if (data.success) {
            alert('Thank you for your rating! ⭐');
            location.reload();
        } else {
            alert(data.message || 'Error submitting rating');
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);  // ✅ ADD THIS
        alert('Failed to submit rating');
    });
}


        function skipRating() {
            document.getElementById('ratingModal').classList.remove('active');
        }

        // Close modal when clicking outside
        document.getElementById('ratingModal').addEventListener('click', function(event) {
            if (event.target === this) {
                skipRating();
            }
        });
    </script>
</body>
</html>

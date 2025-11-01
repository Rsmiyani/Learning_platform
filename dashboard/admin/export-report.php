<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$format = $_GET['format'] ?? 'csv';

try {
    $pdo = getDBConnection();
    
    // Get all data
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'trainee'");
    $total_students = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'trainer'");
    $total_trainers = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM courses");
    $total_courses = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM course_enrollments");
    $total_enrollments = $stmt->fetch()['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM user_certificates");
    $total_certificates = $stmt->fetch()['total'];
    
    // Top Courses
    $stmt = $pdo->prepare("
        SELECT c.course_name, c.course_code, COUNT(ce.enrollment_id) as enrollment_count
        FROM courses c
        LEFT JOIN course_enrollments ce ON c.course_id = ce.course_id
        GROUP BY c.course_id
        ORDER BY enrollment_count DESC
        LIMIT 10
    ");
    $stmt->execute();
    $top_courses = $stmt->fetchAll();
    
    // Top Students
    $stmt = $pdo->prepare("
        SELECT CONCAT(u.first_name, ' ', u.last_name) as name, 
               u.email,
               COALESCE(up.total_points, 0) as points,
               COALESCE(up.level, 1) as level
        FROM users u
        LEFT JOIN user_points up ON u.user_id = up.user_id
        WHERE u.role = 'trainee'
        ORDER BY points DESC
        LIMIT 10
    ");
    $stmt->execute();
    $top_students = $stmt->fetchAll();
    
    if ($format === 'csv') {
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="TrainAI_Report_' . date('Y-m-d') . '.csv"');
        
        // Create output stream
        $output = fopen('php://output', 'w');
        
        // Add BOM for Excel UTF-8 support
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Platform Overview
        fputcsv($output, ['TrainAI Platform Report']);
        fputcsv($output, ['Generated on: ' . date('F d, Y H:i:s')]);
        fputcsv($output, []);
        
        fputcsv($output, ['PLATFORM OVERVIEW']);
        fputcsv($output, ['Metric', 'Value']);
        fputcsv($output, ['Total Students', $total_students]);
        fputcsv($output, ['Total Trainers', $total_trainers]);
        fputcsv($output, ['Total Courses', $total_courses]);
        fputcsv($output, ['Total Enrollments', $total_enrollments]);
        fputcsv($output, ['Total Certificates', $total_certificates]);
        fputcsv($output, []);
        
        // Top Courses
        fputcsv($output, ['TOP 10 COURSES BY ENROLLMENT']);
        fputcsv($output, ['Course Name', 'Course Code', 'Enrollments']);
        foreach ($top_courses as $course) {
            fputcsv($output, [
                $course['course_name'],
                $course['course_code'],
                $course['enrollment_count']
            ]);
        }
        fputcsv($output, []);
        
        // Top Students
        fputcsv($output, ['TOP 10 STUDENTS BY POINTS']);
        fputcsv($output, ['Student Name', 'Email', 'Points', 'Level']);
        foreach ($top_students as $student) {
            fputcsv($output, [
                $student['name'],
                $student['email'],
                $student['points'],
                'Level ' . $student['level']
            ]);
        }
        
        fclose($output);
        exit;
        
    } elseif ($format === 'excel') {
        // For Excel, we'll use HTML table format that Excel can import
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="TrainAI_Report_' . date('Y-m-d') . '.xls"');
        
        echo '<html xmlns:x="urn:schemas-microsoft-com:office:excel">';
        echo '<head><meta charset="UTF-8"></head>';
        echo '<body>';
        
        echo '<h1>TrainAI Platform Report</h1>';
        echo '<p>Generated on: ' . date('F d, Y H:i:s') . '</p>';
        
        echo '<h2>Platform Overview</h2>';
        echo '<table border="1">';
        echo '<tr><th>Metric</th><th>Value</th></tr>';
        echo '<tr><td>Total Students</td><td>' . $total_students . '</td></tr>';
        echo '<tr><td>Total Trainers</td><td>' . $total_trainers . '</td></tr>';
        echo '<tr><td>Total Courses</td><td>' . $total_courses . '</td></tr>';
        echo '<tr><td>Total Enrollments</td><td>' . $total_enrollments . '</td></tr>';
        echo '<tr><td>Total Certificates</td><td>' . $total_certificates . '</td></tr>';
        echo '</table>';
        
        echo '<h2>Top 10 Courses by Enrollment</h2>';
        echo '<table border="1">';
        echo '<tr><th>Course Name</th><th>Course Code</th><th>Enrollments</th></tr>';
        foreach ($top_courses as $course) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($course['course_name']) . '</td>';
            echo '<td>' . htmlspecialchars($course['course_code']) . '</td>';
            echo '<td>' . $course['enrollment_count'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        echo '<h2>Top 10 Students by Points</h2>';
        echo '<table border="1">';
        echo '<tr><th>Student Name</th><th>Email</th><th>Points</th><th>Level</th></tr>';
        foreach ($top_students as $student) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($student['name']) . '</td>';
            echo '<td>' . htmlspecialchars($student['email']) . '</td>';
            echo '<td>' . $student['points'] . '</td>';
            echo '<td>Level ' . $student['level'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';
        
        echo '</body></html>';
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Export error: " . $e->getMessage());
    die('Error generating report');
}
?>

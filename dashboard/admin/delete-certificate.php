<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/database.php';
initSession();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$cert_id = $_GET['cert_id'] ?? null;
$user_id = $_GET['user_id'] ?? null;

if (!$cert_id || !$user_id) {
    $_SESSION['error'] = "Invalid parameters";
    header('Location: index.php');
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get certificate info before deleting
    $stmt = $pdo->prepare("
        SELECT uc.*, c.course_name, u.first_name, u.last_name
        FROM user_certificates uc
        JOIN courses c ON uc.course_id = c.course_id
        JOIN users u ON uc.user_id = u.user_id
        WHERE uc.certificate_id = ?
    ");
    $stmt->execute([$cert_id]);
    $certificate = $stmt->fetch();
    
    if (!$certificate) {
        $_SESSION['error'] = "Certificate not found";
        header('Location: user-certificates.php?user_id=' . $user_id);
        exit;
    }
    
    // Delete the certificate
    $stmt = $pdo->prepare("DELETE FROM user_certificates WHERE certificate_id = ?");
    $stmt->execute([$cert_id]);
    
    $_SESSION['success'] = "Successfully deleted certificate for {$certificate['course_name']} from {$certificate['first_name']} {$certificate['last_name']}";
    
} catch (PDOException $e) {
    error_log("Certificate deletion error: " . $e->getMessage());
    $_SESSION['error'] = "Error deleting certificate: " . $e->getMessage();
}

header('Location: user-certificates.php?user_id=' . $user_id);
exit;
?>

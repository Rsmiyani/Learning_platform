<?php
require_once 'config/database.php';
initSession();

// Destroy session
session_unset();
session_destroy();

// Delete remember me cookie if exists
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, "/");
}

// Redirect to login
header('Location: login.php');
exit;
?>

<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

if (isset($_SESSION['admin_id'])) {
    try {
        // Log the logout activity
        $stmt = $pdo->prepare("INSERT INTO admin_activity_log (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['admin_id'], 'logout', 'Admin logged out', $_SERVER['REMOTE_ADDR']]);
    } catch (PDOException $e) {
        error_log($e->getMessage());
    }
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to login page
header('Location: login.php');
exit(); 
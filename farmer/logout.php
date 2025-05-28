<?php
require_once '../includes/functions.php';

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Redirect to home page with message
$_SESSION['message'] = "You have been successfully logged out.";
$_SESSION['message_type'] = "success";
header("Location: ../index.php");
exit(); 
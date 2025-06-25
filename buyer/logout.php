<?php
require_once '../includes/session.php';

// Verify that the user is a buyer
if (!isset($_SESSION['buyer_type']) || $_SESSION['buyer_type'] !== 'buyer') {
    header('Location: ../index.php');
    exit();
}

// Clear all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to home page with success message
$_SESSION['success_message'] = 'You have been successfully logged out.';
header('Location: ../index.php');
exit();
?> 
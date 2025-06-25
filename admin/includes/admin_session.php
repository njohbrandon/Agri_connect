<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id']) || empty($_SESSION['admin_id'])) {
    // Store the requested URL for redirect after login
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    
    // Redirect to login page
    header("Location: /farmers_market_place/admin/login.php");
    exit();
}

// Set a default timezone if not set
if (!ini_get('date.timezone')) {
    date_default_timezone_set('UTC');
}

// Function to check admin permissions (can be expanded later)
function checkAdminPermission($permission) {
    // For now, return true as we haven't implemented detailed permissions
    return true;
}
?> 
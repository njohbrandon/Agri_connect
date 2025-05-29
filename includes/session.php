<?php
// Set secure session parameters before starting the session
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.gc_maxlifetime', 1800); // 30 minutes
ini_set('session.cookie_samesite', 'Strict');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['farmer_id']) && !empty($_SESSION['farmer_id']);
}

/**
 * Require login for protected pages
 * Redirects to login page if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /farmer/login.php');
        exit();
    }
}

/**
 * Set login session data
 * @param array $farmer
 */
function setLoginSession($farmer) {
    $_SESSION['farmer_id'] = $farmer['id'];
    $_SESSION['farmer_name'] = $farmer['name'];
    $_SESSION['farmer_email'] = $farmer['email'];
    $_SESSION['last_activity'] = time();
}

/**
 * Clear all session data
 */
function clearSession() {
    session_unset();
    session_destroy();
    setcookie(session_name(), '', time() - 3600, '/');
}

// Session timeout check (30 minutes)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    clearSession();
    header('Location: /farmer/login.php?msg=session_expired');
    exit();
}

// Update last activity time
if (isset($_SESSION['farmer_id'])) {
    $_SESSION['last_activity'] = time();
}
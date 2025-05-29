<?php
require_once '../includes/session.php';

// Clear all session data
clearSession();

// Redirect to login page with logout message
header('Location: login.php?msg=logged_out');
exit(); 
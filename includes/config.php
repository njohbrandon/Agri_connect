<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'agri_connect');
define('DB_USER', 'root');
define('DB_PASS', '');

// Establish database connection
try {
    // Debug: Print connection attempt
    error_log("Attempting to connect to database: " . DB_NAME);
    
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    // Debug: Test connection with a simple query
    $test = $pdo->query("SELECT COUNT(*) as count FROM farmers");
    $result = $test->fetch();
    error_log("Database connection successful. Found " . $result['count'] . " farmers.");
    
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}

// Site configuration
define('SITE_URL', 'http://localhost/farmers_market_place');
define('UPLOAD_PATH', __DIR__ . '/../uploads');

// Ensure upload directories exist
$upload_dirs = [
    UPLOAD_PATH,
    UPLOAD_PATH . '/products',
    UPLOAD_PATH . '/farmers',
    UPLOAD_PATH . '/buyers',
    UPLOAD_PATH . '/admin'
];

foreach ($upload_dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
} 
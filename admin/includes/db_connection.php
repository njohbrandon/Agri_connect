<?php
// Database configuration
$db_host = 'localhost';
$db_name = 'agri_connect';
$db_user = 'root';
$db_pass = '';

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );

    // Debug: Test the connection and check table structure
    $tables_query = "SHOW TABLES";
    $tables_result = $pdo->query($tables_query);
    $tables = $tables_result->fetchAll(PDO::FETCH_COLUMN);
    error_log("Available tables in agri_connect: " . implode(", ", $tables));

    // Check farmers table structure
    if (in_array('farmers', $tables)) {
        $columns_query = "SHOW COLUMNS FROM farmers";
        $columns_result = $pdo->query($columns_query);
        $columns = $columns_result->fetchAll(PDO::FETCH_COLUMN);
        error_log("Farmers table columns: " . implode(", ", $columns));
    }

} catch (PDOException $e) {
    // Log error (to a file in a real application)
    error_log("Database Connection Error: " . $e->getMessage());
    
    // Show user-friendly error
    die("Sorry, there was a problem connecting to the database. Please try again later.");
}
?> 
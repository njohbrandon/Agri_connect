<?php
require_once 'config/database.php';

// Set content type to plain text for better readability in browser
header('Content-Type: text/plain');

try {
    // Check if table exists
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM information_schema.tables 
        WHERE table_schema = 'agri_connect' 
        AND table_name = 'login_attempts'
    ");
    $stmt->execute();
    $tableExists = $stmt->fetchColumn() > 0;

    if ($tableExists) {
        echo "Success: The 'login_attempts' table exists in your database.";
        
        // Check table structure
        $stmt = $pdo->prepare("DESCRIBE login_attempts");
        $stmt->execute();
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n\nTable structure:\n";
        foreach ($columns as $column) {
            echo "\n- {$column['Field']}: {$column['Type']}";
        }
    } else {
        echo "Error: The 'login_attempts' table does not exist in your database.";
        
        echo "\n\nWould you like to create the table? Here's the SQL you need to run in phpMyAdmin:\n\n";
        echo "CREATE TABLE login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    attempt_time DATETIME NOT NULL,
    INDEX idx_ip_time (ip_address, attempt_time)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 
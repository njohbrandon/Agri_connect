<?php
require_once dirname(__FILE__) . '/../../includes/config.php';

try {
    // Read and execute the SQL file
    $sql = file_get_contents(__DIR__ . '/create_tables.sql');
    $pdo->exec($sql);
    
    echo "Database tables created successfully!\n";
    echo "Default admin credentials:\n";
    echo "Username: admin\n";
    echo "Password: password\n";
    echo "\nPlease change these credentials immediately after first login.";
    
} catch (PDOException $e) {
    die("Error setting up database: " . $e->getMessage());
} 
<?php
// First, connect without selecting a database
try {
    $pdo = new PDO(
        "mysql:host=localhost",
        "root",
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
    
    echo "Connected to MySQL server successfully.<br>";
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function executeSQLFile($pdo, $filename) {
    echo "Executing SQL file: $filename<br>";
    $sql = file_get_contents($filename);
    
    try {
        $pdo->exec($sql);
        echo "Successfully executed $filename<br>";
        return true;
    } catch (PDOException $e) {
        echo "Error executing $filename: " . $e->getMessage() . "<br>";
        return false;
    }
}

// Execute SQL files in order
$files = [
    __DIR__ . '/create_database.sql',
    __DIR__ . '/create_tables.sql',
    __DIR__ . '/create_farmers_table.sql'
];

$success = true;
foreach ($files as $file) {
    if (!executeSQLFile($pdo, $file)) {
        $success = false;
        break;
    }
}

if ($success) {
    echo "<br>Installation completed successfully!<br>";
    echo "<a href='../users/farmers.php'>Go to Farmers Management</a>";
} else {
    echo "<br>Installation failed. Please check the error messages above.";
} 
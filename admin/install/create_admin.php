<?php
// Connect to the database
$pdo = new PDO(
    "mysql:host=localhost;dbname=agri_connect;charset=utf8mb4",
    "root",
    "",
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]
);

// Create admins table if it doesn't exist
$pdo->exec("
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    profile_image VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Check if admin user exists
$stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = ?");
$stmt->execute(['admin']);
$adminExists = $stmt->fetchColumn() > 0;

if (!$adminExists) {
    // Create default admin user
    // Password is 'password'
    $hashedPassword = password_hash('password', PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("
        INSERT INTO admins (username, email, password, role, status) 
        VALUES (?, ?, ?, 'super_admin', 'active')
    ");
    
    $stmt->execute([
        'admin',
        'admin@agrimarket.com',
        $hashedPassword
    ]);
    
    echo "Admin user created successfully!<br>";
    echo "Username: admin<br>";
    echo "Password: password<br>";
} else {
    echo "Admin user already exists.<br>";
}

// Create admin_activity_log table if it doesn't exist
$pdo->exec("
CREATE TABLE IF NOT EXISTS admin_activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_id INT,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50),
    entity_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES admins(id)
)");

echo "<br>Setup completed successfully!<br>";
echo "<a href='../auth/login.php'>Go to Admin Login</a>"; 
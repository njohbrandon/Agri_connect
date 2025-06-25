-- Create farmers table
CREATE TABLE IF NOT EXISTS farmers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    profile_image VARCHAR(255),
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    verification_token VARCHAR(100),
    email_verified BOOLEAN DEFAULT FALSE,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert test data
INSERT INTO farmers (name, email, phone, password, address, status, email_verified) VALUES
('John Farmer', 'john@example.com', '1234567890', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '123 Farm Road, Rural County', 'active', 1),
('Mary Smith', 'mary@example.com', '0987654321', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '456 Agriculture Lane, Farmville', 'active', 1),
('Bob Wilson', 'bob@example.com', '5555555555', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '789 Harvest Street, Croptown', 'active', 1); 
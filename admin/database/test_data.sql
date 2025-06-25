-- Insert test farmer
INSERT INTO farmers (name, email, phone, password, location, status)
VALUES (
    'John Doe',
    'john.doe@example.com',
    '+1234567890',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'Sample Location',
    'active'
);

-- Get the inserted farmer's ID
SET @farmer_id = LAST_INSERT_ID();

-- Insert test products for the farmer
INSERT INTO products (farmer_id, name, description, price, quantity, status)
VALUES
    (@farmer_id, 'Fresh Tomatoes', 'Locally grown organic tomatoes', 2.99, 100, 'active'),
    (@farmer_id, 'Sweet Corn', 'Fresh sweet corn from the farm', 0.50, 200, 'active'),
    (@farmer_id, 'Green Beans', 'Hand-picked green beans', 1.99, 50, 'active');

-- Insert another test farmer
INSERT INTO farmers (name, email, phone, password, location, status)
VALUES (
    'Jane Smith',
    'jane.smith@example.com',
    '+1987654321',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: password
    'Another Location',
    'active'
);

-- Get the second farmer's ID
SET @farmer_id = LAST_INSERT_ID();

-- Insert test products for the second farmer
INSERT INTO products (farmer_id, name, description, price, quantity, status)
VALUES
    (@farmer_id, 'Fresh Eggs', 'Farm fresh eggs', 3.99, 60, 'active'),
    (@farmer_id, 'Organic Milk', 'Fresh organic milk', 4.99, 30, 'active'); 
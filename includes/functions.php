<?php
// Session is handled in session.php

/**
 * Sanitize user input
 * @param string $data
 * @return string
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Upload image file
 * @param array $file
 * @param string $destination
 * @return string|false
 */
function uploadImage($file, $destination) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    if (!in_array($file['type'], $allowedTypes)) {
        return false;
    }

    if ($file['size'] > $maxSize) {
        return false;
    }

    $fileName = uniqid() . '_' . basename($file['name']);
    $targetPath = $destination . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return $fileName;
    }

    return false;
}

/**
 * Format price
 * @param float $price
 * @return string
 */
function formatPrice($price) {
    return number_format($price, 2);
}

/**
 * Get farmer details by ID
 * @param int $farmerId
 * @return array|false
 */
function getFarmerDetails($farmerId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, name, email, phone, address FROM farmers WHERE id = ?");
    $stmt->bind_param("i", $farmerId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Get product details by ID
 * @param int $productId
 * @return array|false
 */
function getProductDetails($productId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT p.*, f.name as farmer_name, f.phone as farmer_phone 
                           FROM products p 
                           JOIN farmers f ON p.farmer_id = f.id 
                           WHERE p.id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return false;
}

/**
 * Get featured products
 * @param int $limit
 * @return array
 */
function getFeaturedProducts($limit = 6) {
    global $conn;
    
    $sql = "SELECT p.*, f.name as farmer_name 
            FROM products p 
            JOIN farmers f ON p.farmer_id = f.id 
            WHERE p.status = 'active' 
            ORDER BY p.created_at DESC 
            LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

/**
 * Search products
 * @param string $query
 * @param array $filters
 * @return array
 */
function searchProducts($query, $filters = []) {
    global $conn;
    
    $sql = "SELECT p.*, f.name as farmer_name 
            FROM products p 
            JOIN farmers f ON p.farmer_id = f.id 
            WHERE p.status = 'active' 
            AND (p.name LIKE ? OR p.description LIKE ?)";
    
    $searchTerm = "%$query%";
    $params = [$searchTerm, $searchTerm];
    $types = "ss";
    
    if (!empty($filters['category'])) {
        $sql .= " AND p.category = ?";
        $params[] = $filters['category'];
        $types .= "s";
    }
    
    if (!empty($filters['min_price'])) {
        $sql .= " AND p.price >= ?";
        $params[] = $filters['min_price'];
        $types .= "d";
    }
    
    if (!empty($filters['max_price'])) {
        $sql .= " AND p.price <= ?";
        $params[] = $filters['max_price'];
        $types .= "d";
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}
?> 
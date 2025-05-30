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
 * Format price in XAF
 * @param float $price
 * @return string
 */
function formatPrice($price) {
    return number_format($price, 0, '.', ',') . ' XAF';
}

/**
 * Generate WhatsApp link with message
 * @param string $phone
 * @param string $message
 * @return string
 */
function getWhatsAppLink($phone, $message = '') {
    // Remove any non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // If number starts with 0, replace with 237
    if (substr($phone, 0, 1) === '0') {
        $phone = '237' . substr($phone, 1);
    }
    // If number doesn't have country code, add it
    elseif (substr($phone, 0, 3) !== '237') {
        $phone = '237' . $phone;
    }
    
    $url = 'https://wa.me/' . $phone;
    if (!empty($message)) {
        $url .= '?text=' . urlencode($message);
    }
    
    return $url;
}

/**
 * Format phone number for display
 * @param string $phone
 * @return string
 */
function formatPhoneNumber($phone) {
    // Remove any non-digit characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // If number starts with 0, replace with +237
    if (substr($phone, 0, 1) === '0') {
        $phone = '+237' . substr($phone, 1);
    }
    // If number doesn't have country code, add it
    elseif (substr($phone, 0, 3) !== '237') {
        $phone = '+237' . $phone;
    } elseif (substr($phone, 0, 3) === '237') {
        $phone = '+' . $phone;
    }
    
    return $phone;
}

/**
 * Get farmer details by ID
 * @param int $farmerId
 * @return array|false
 */
function getFarmerDetails($farmerId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT id, name, email, phone, address FROM farmers WHERE id = ?");
    $stmt->execute([$farmerId]);
    $farmer = $stmt->fetch();
    
    if ($farmer) {
        // Format phone number for display
        $farmer['phone'] = formatPhoneNumber($farmer['phone']);
        return $farmer;
    }
    
    return false;
}

/**
 * Get product details by ID
 * @param int $productId
 * @return array|false
 */
function getProductDetails($productId) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT p.*, f.name as farmer_name, f.phone as farmer_phone, f.address as farmer_location 
                          FROM products p 
                          JOIN farmers f ON p.farmer_id = f.id 
                          WHERE p.id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if ($product) {
        // Format phone number and generate WhatsApp link
        $product['farmer_phone'] = formatPhoneNumber($product['farmer_phone']);
        $product['whatsapp_link'] = getWhatsAppLink($product['farmer_phone'], 
            "Hi, I'm interested in your product: " . $product['name']);
        return $product;
    }
    
    return false;
}

/**
 * Get featured products
 * @param int $limit
 * @return array
 */
function getFeaturedProducts($limit = 6) {
    global $pdo;
    
    $sql = "SELECT p.*, f.name as farmer_name, f.phone as farmer_phone, f.address as farmer_location 
            FROM products p 
            JOIN farmers f ON p.farmer_id = f.id 
            WHERE p.status = 'active' 
            ORDER BY p.created_at DESC 
            LIMIT ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$limit]);
    $products = $stmt->fetchAll();
    
    foreach ($products as &$product) {
        $product['farmer_phone'] = formatPhoneNumber($product['farmer_phone']);
        $product['whatsapp_link'] = getWhatsAppLink($product['farmer_phone'], 
            "Hi, I'm interested in your product: " . $product['name']);
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
    global $pdo;
    
    $sql = "SELECT p.*, f.name as farmer_name, f.phone as farmer_phone, f.address as farmer_location 
            FROM products p 
            JOIN farmers f ON p.farmer_id = f.id 
            WHERE p.status = 'active' 
            AND (p.name LIKE ? OR p.description LIKE ?)";
    
    $searchTerm = "%$query%";
    $params = [$searchTerm, $searchTerm];
    
    if (!empty($filters['category'])) {
        $sql .= " AND p.category = ?";
        $params[] = $filters['category'];
    }
    
    if (!empty($filters['min_price'])) {
        $sql .= " AND p.price >= ?";
        $params[] = $filters['min_price'];
    }
    
    if (!empty($filters['max_price'])) {
        $sql .= " AND p.price <= ?";
        $params[] = $filters['max_price'];
    }
    
    $sql .= " ORDER BY p.created_at DESC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
    foreach ($products as &$product) {
        $product['farmer_phone'] = formatPhoneNumber($product['farmer_phone']);
        $product['whatsapp_link'] = getWhatsAppLink($product['farmer_phone'], 
            "Hi, I'm interested in your product: " . $product['name']);
    }
    
    return $products;
}
?> 
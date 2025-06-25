<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Get product IDs from query string
$ids = isset($_GET['ids']) ? explode(',', $_GET['ids']) : [];

// Validate IDs
$ids = array_filter($ids, function($id) {
    return filter_var($id, FILTER_VALIDATE_INT) !== false;
});

if (empty($ids)) {
    http_response_code(400);
    echo json_encode(['error' => 'No valid product IDs provided']);
    exit;
}

try {
    // Prepare placeholders for the IN clause
    $placeholders = str_repeat('?,', count($ids) - 1) . '?';
    
    // Get products with farmer information
    $sql = "SELECT p.*, f.name as farmer_name, f.phone, f.address as location 
            FROM products p 
            JOIN farmers f ON p.farmer_id = f.id 
            WHERE p.id IN ($placeholders) AND p.status = 'active'";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    $products = $stmt->fetchAll();

    // Format products for response
    $formatted_products = array_map(function($product) {
        return [
            'id' => $product['id'],
            'name' => $product['name'],
            'description' => $product['description'],
            'category' => $product['category'],
            'price' => $product['price'],
            'unit' => $product['unit'],
            'quantity' => $product['quantity'],
            'image' => !empty($product['image']) ? 'uploads/products/' . $product['image'] : null,
            'farmer_name' => $product['farmer_name'],
            'location' => $product['location']
        ];
    }, $products);

    echo json_encode($formatted_products);

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while fetching products']);
} 
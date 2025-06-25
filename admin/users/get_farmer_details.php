<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Check if farmer ID is provided
if (!isset($_GET['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Farmer ID is required']);
    exit();
}

try {
    // Get farmer details
    $stmt = $pdo->prepare("
        SELECT f.*,
               COUNT(DISTINCT p.id) as product_count,
               COUNT(DISTINCT pi.id) as inquiry_count,
               AVG(br.rating) as avg_rating
        FROM farmers f
        LEFT JOIN products p ON f.id = p.farmer_id
        LEFT JOIN product_inquiries pi ON p.id = pi.product_id
        LEFT JOIN buyer_reviews br ON f.id = br.farmer_id
        WHERE f.id = ?
        GROUP BY f.id
    ");
    $stmt->execute([$_GET['id']]);
    $farmer = $stmt->fetch();

    if (!$farmer) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Farmer not found']);
        exit();
    }

    // Get farmer's products
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category = c.name
        WHERE p.farmer_id = ?
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$_GET['id']]);
    $farmer['products'] = $stmt->fetchAll();

    // Format the response
    $response = [
        'success' => true,
        'farmer' => [
            'id' => $farmer['id'],
            'name' => $farmer['name'],
            'email' => $farmer['email'],
            'phone' => $farmer['phone'],
            'address' => $farmer['address'],
            'created_at' => $farmer['created_at'],
            'product_count' => $farmer['product_count'],
            'inquiry_count' => $farmer['inquiry_count'],
            'avg_rating' => $farmer['avg_rating'],
            'products' => array_map(function($product) {
                return [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'category' => $product['category_name'],
                    'price' => number_format($product['price'], 2),
                    'status' => $product['status'],
                    'image' => !empty($product['image']) ? 
                        '../../uploads/products/' . $product['image'] : 
                        '../../assets/img/default-product.jpg'
                ];
            }, $farmer['products'])
        ]
    ];

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (PDOException $e) {
    error_log($e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Database error']);
    exit();
} 
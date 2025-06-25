<?php
require_once '../includes/admin_session.php';
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Farmer ID is required');
    }

    $farmerId = intval($_GET['id']);
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    
    $query = "SELECT 
                p.id,
                p.name,
                p.description,
                p.price,
                p.quantity,
                p.image,
                p.status,
                p.created_at
            FROM products p
            WHERE p.farmer_id = ?
            ORDER BY p.created_at DESC
            LIMIT ?";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$farmerId, $limit]);
    
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format the products data
    foreach ($products as &$product) {
        $product['price'] = number_format($product['price'], 2);
        $product['created_at'] = date('M d, Y', strtotime($product['created_at']));
        
        // Ensure image path is complete
        if ($product['image'] && !filter_var($product['image'], FILTER_VALIDATE_URL)) {
            $product['image'] = '../' . ltrim($product['image'], '/');
        }
    }
    
    echo json_encode($products);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 
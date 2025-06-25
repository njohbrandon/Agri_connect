<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Check if buyer is logged in
if (!isBuyerLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Please log in to save products']);
    exit;
}

// Get product ID from POST data
$product_id = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
if (!$product_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid product ID']);
    exit;
}

try {
    // Check if product exists and is active
    $stmt = $pdo->prepare('SELECT id FROM products WHERE id = ? AND status = "active"');
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Product not found']);
        exit;
    }

    // Check if product is already saved
    $stmt = $pdo->prepare('SELECT id FROM saved_products WHERE buyer_id = ? AND product_id = ?');
    $stmt->execute([$_SESSION['buyer_id'], $product_id]);
    if ($stmt->fetch()) {
        // Remove from saved products if already saved
        $stmt = $pdo->prepare('DELETE FROM saved_products WHERE buyer_id = ? AND product_id = ?');
        $stmt->execute([$_SESSION['buyer_id'], $product_id]);
        echo json_encode(['message' => 'Product removed from saved items', 'action' => 'removed']);
    } else {
        // Save the product
        $stmt = $pdo->prepare('INSERT INTO saved_products (buyer_id, product_id) VALUES (?, ?)');
        $stmt->execute([$_SESSION['buyer_id'], $product_id]);
        echo json_encode(['message' => 'Product saved successfully', 'action' => 'saved']);
    }

} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'An error occurred while saving the product']);
} 
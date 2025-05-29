<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Require login for this page
requireLogin();

// Get product ID from URL
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$product_id) {
    $_SESSION['error_message'] = 'Invalid product ID.';
    header('Location: products.php');
    exit();
}

try {
    // Start transaction
    $pdo->beginTransaction();

    // Get product details to check ownership and get image filename
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND farmer_id = ?');
    $stmt->execute([$product_id, $_SESSION['farmer_id']]);
    $product = $stmt->fetch();

    if (!$product) {
        throw new Exception('Product not found or you do not have permission to delete it.');
    }

    // Delete product image if exists
    if (!empty($product['image'])) {
        $image_path = '../uploads/products/' . $product['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // Delete product from database
    $stmt = $pdo->prepare('DELETE FROM products WHERE id = ? AND farmer_id = ?');
    $stmt->execute([$product_id, $_SESSION['farmer_id']]);

    // Commit transaction
    $pdo->commit();

    $_SESSION['success_message'] = 'Product deleted successfully.';

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    error_log($e->getMessage());
    $_SESSION['error_message'] = 'An error occurred while deleting the product.';
}

header('Location: products.php');
exit(); 
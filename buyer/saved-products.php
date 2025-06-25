<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Ensure user is logged in as buyer
if (!isset($_SESSION['buyer_id']) || $_SESSION['buyer_type'] !== 'buyer') {
    $_SESSION['error_message'] = 'Please login to access your saved products.';
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle remove from wishlist
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_product'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $product_id = filter_var($_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);
        
        if ($product_id) {
            try {
                $stmt = $pdo->prepare('DELETE FROM saved_products WHERE buyer_id = ? AND product_id = ?');
                $stmt->execute([$_SESSION['buyer_id'], $product_id]);
                $success = 'Product removed from wishlist.';
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Failed to remove product from wishlist.';
            }
        }
    }
}

try {
    // Get saved products with farmer information
    $stmt = $pdo->prepare('
        SELECT p.*, f.name as farmer_name, f.phone as farmer_phone, 
               sp.created_at as saved_at
        FROM saved_products sp
        JOIN products p ON sp.product_id = p.id
        JOIN farmers f ON p.farmer_id = f.id
        WHERE sp.buyer_id = ?
        ORDER BY sp.created_at DESC
    ');
    $stmt->execute([$_SESSION['buyer_id']]);
    $saved_products = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while loading your saved products.';
}

$page_title = 'Saved Products - Agri-Connect';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .wishlist-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .wishlist-header {
            background: linear-gradient(45deg, #198754, #20c997);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .product-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.2s;
            height: 100%;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product-details {
            padding: 1.5rem;
        }
        .price-tag {
            background: #198754;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: bold;
        }
        .farmer-info {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .whatsapp-btn {
            background: #25D366;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        .whatsapp-btn:hover {
            background: #128C7E;
            color: white;
            transform: scale(1.05);
        }
        .empty-wishlist {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .empty-wishlist i {
            font-size: 4rem;
            color: #198754;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>

    <div class="wishlist-container">
        <div class="wishlist-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">My Saved Products</h1>
                    <p class="mb-0">You have <?php echo count($saved_products); ?> saved products</p>
                </div>
                <a href="../products.php" class="btn btn-outline-light">
                    <i class="bi bi-plus-circle"></i> Browse More Products
                </a>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($saved_products)): ?>
            <div class="empty-wishlist">
                <i class="bi bi-heart"></i>
                <h2 class="h4">Your wishlist is empty</h2>
                <p class="text-muted mb-4">Start saving products you're interested in!</p>
                <a href="../products.php" class="btn btn-success">
                    <i class="bi bi-shop"></i> Browse Products
                </a>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($saved_products as $product): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="product-card">
                            <img src="<?php echo $product['image'] ? '../uploads/products/' . htmlspecialchars($product['image']) : '../assets/images/default-product.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-image">
                            <div class="product-details">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h2 class="h5 mb-0"><?php echo htmlspecialchars($product['name']); ?></h2>
                                    <span class="price-tag">₱<?php echo number_format($product['price'], 2); ?>/<?php echo htmlspecialchars($product['unit']); ?></span>
                                </div>
                                
                                <p class="text-muted mb-3"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                                
                                <div class="farmer-info mb-3">
                                    <div><i class="bi bi-person"></i> <?php echo htmlspecialchars($product['farmer_name']); ?></div>
                                    <div><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($product['farmer_phone']); ?></div>
                                    <div><i class="bi bi-clock"></i> Saved <?php echo timeAgo($product['saved_at']); ?></div>
                                </div>

                                <div class="d-flex gap-2">
                                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $product['farmer_phone']); ?>?text=Hi, I'm interested in your product: <?php echo urlencode($product['name']); ?>" 
                                       target="_blank" class="whatsapp-btn flex-grow-1">
                                        <i class="bi bi-whatsapp"></i> Contact Farmer
                                    </a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Remove this product from wishlist?');">
                                        <?php csrfField(); ?>
                                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                        <button type="submit" name="remove_product" class="btn btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
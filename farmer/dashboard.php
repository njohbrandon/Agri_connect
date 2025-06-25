<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Require login for this page
requireLogin();

// Get farmer's information
try {
    $stmt = $pdo->prepare('SELECT * FROM farmers WHERE id = ?');
    $stmt->execute([$_SESSION['farmer_id']]);
    $farmer = $stmt->fetch();

    // Get farmer's products count
    $stmt = $pdo->prepare('SELECT COUNT(*) as product_count FROM products WHERE farmer_id = ?');
    $stmt->execute([$_SESSION['farmer_id']]);
    $product_count = $stmt->fetch()['product_count'];

    // Get recent products
    $stmt = $pdo->prepare('SELECT * FROM products WHERE farmer_id = ? ORDER BY created_at DESC LIMIT 5');
    $stmt->execute([$_SESSION['farmer_id']]);
    $recent_products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while fetching your information.';
}

$page_title = 'Farmer Dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Agri-Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .dashboard-card {
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .stat-card {
            background: linear-gradient(45deg, #198754, #20c997);
            color: white;
        }
        .product-item {
            border-left: 4px solid #198754;
        }
        .quick-action {
            text-decoration: none;
            color: inherit;
        }
        .quick-action:hover {
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-5">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <i class="bi bi-person-circle display-4 text-success"></i>
                            <h5 class="mt-2"><?php echo htmlspecialchars($farmer['name']); ?></h5>
                            <p class="text-muted mb-0"><?php echo htmlspecialchars($farmer['email']); ?></p>
                        </div>
                        <hr>
                        <nav class="nav flex-column">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                            <a class="nav-link" href="products.php">
                                <i class="bi bi-box"></i> My Products
                                <span class="badge bg-success rounded-pill float-end"><?php echo $product_count; ?></span>
                            </a>
                            <a class="nav-link" href="add_product.php">
                                <i class="bi bi-plus-circle"></i> Add Product
                            </a>
                            <a class="nav-link" href="profile.php">
                                <i class="bi bi-person"></i> Profile
                            </a>
                            <a class="nav-link text-danger" href="logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Welcome Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h2 class="card-title h4">Welcome back, <?php echo htmlspecialchars($farmer['name']); ?>!</h2>
                        <p class="card-text text-muted">
                            Here's an overview of your account and recent activity.
                        </p>
                    </div>
                </div>

                <!-- Stats Row -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-box text-success display-6"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h6 class="card-title mb-1">Total Products</h6>
                                        <h2 class="mb-0"><?php echo $product_count; ?></h2>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Add more stat cards here as needed -->
                </div>

                <!-- Recent Products -->
                <div class="card shadow-sm">
                    <div class="card-header bg-transparent">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Recent Products</h5>
                            <a href="products.php" class="btn btn-sm btn-outline-success">
                                View All
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recent_products)): ?>
                            <p class="text-muted text-center mb-0">
                                You haven't added any products yet.
                                <a href="add_product.php">Add your first product</a>
                            </p>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recent_products as $product): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo !empty($product['image']) ? '../uploads/products/' . $product['image'] : '../assets/images/default-product.jpg'; ?>"
                                                             class="rounded" width="40" height="40" alt="">
                                                        <div class="ms-2">
                                                            <h6 class="mb-0"><?php echo htmlspecialchars($product['name']); ?></h6>
                                                            <small class="text-muted">
                                                                Added: <?php echo date('M j, Y', strtotime($product['created_at'])); ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo formatPrice($product['price']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $product['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                        <?php echo ucfirst($product['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                                           class="btn btn-outline-secondary">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button type="button" class="btn btn-outline-danger" 
                                                                onclick="confirmDelete(<?php echo $product['id']; ?>)">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(productId) {
            if (confirm('Are you sure you want to delete this product?')) {
                window.location.href = `delete_product.php?id=${productId}`;
            }
        }
    </script>
</body>
</html> 
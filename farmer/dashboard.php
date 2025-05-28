<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// Require login
requireLogin();

// Get farmer details
$farmer = getFarmerDetails($_SESSION['farmer_id']);

// Get farmer's products count
$stmt = $conn->prepare("SELECT 
    COUNT(*) as total_products,
    COUNT(CASE WHEN status = 'active' THEN 1 END) as active_products
    FROM products 
    WHERE farmer_id = ?");
$stmt->bind_param("i", $_SESSION['farmer_id']);
$stmt->execute();
$product_stats = $stmt->get_result()->fetch_assoc();

// Get recent products
$stmt = $conn->prepare("SELECT * FROM products 
    WHERE farmer_id = ? 
    ORDER BY created_at DESC 
    LIMIT 5");
$stmt->bind_param("i", $_SESSION['farmer_id']);
$stmt->execute();
$recent_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmer Dashboard - Agri-Connect</title>
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

    <div class="container py-4">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-8">
                <h2>Welcome back, <?php echo htmlspecialchars($farmer['name']); ?>!</h2>
                <p class="text-muted">Manage your products and profile from your dashboard</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="add_product.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Product
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="dashboard-card stat-card p-4">
                    <h3 class="h5 mb-3">Total Products</h3>
                    <h2 class="display-5 mb-0"><?php echo $product_stats['total_products']; ?></h2>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="dashboard-card stat-card p-4">
                    <h3 class="h5 mb-3">Active Products</h3>
                    <h2 class="display-5 mb-0"><?php echo $product_stats['active_products']; ?></h2>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="dashboard-card stat-card p-4">
                    <h3 class="h5 mb-3">Profile Views</h3>
                    <h2 class="display-5 mb-0">0</h2>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Quick Actions -->
            <div class="col-md-4 mb-4">
                <div class="dashboard-card p-4">
                    <h3 class="h4 mb-4">Quick Actions</h3>
                    <div class="d-grid gap-3">
                        <a href="add_product.php" class="quick-action d-flex align-items-center p-3 bg-light rounded">
                            <i class="bi bi-plus-circle fs-4 me-3"></i>
                            <div>
                                <h4 class="h6 mb-1">Add New Product</h4>
                                <small class="text-muted">List a new product for sale</small>
                            </div>
                        </a>
                        <a href="products.php" class="quick-action d-flex align-items-center p-3 bg-light rounded">
                            <i class="bi bi-grid fs-4 me-3"></i>
                            <div>
                                <h4 class="h6 mb-1">Manage Products</h4>
                                <small class="text-muted">Edit or update your products</small>
                            </div>
                        </a>
                        <a href="profile.php" class="quick-action d-flex align-items-center p-3 bg-light rounded">
                            <i class="bi bi-person fs-4 me-3"></i>
                            <div>
                                <h4 class="h6 mb-1">Update Profile</h4>
                                <small class="text-muted">Edit your profile information</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Profile Overview -->
            <div class="col-md-4 mb-4">
                <div class="dashboard-card p-4">
                    <h3 class="h4 mb-4">Profile Overview</h3>
                    <div class="mb-3">
                        <label class="text-muted mb-1">Name</label>
                        <p class="mb-3"><?php echo htmlspecialchars($farmer['name']); ?></p>
                        
                        <label class="text-muted mb-1">Email</label>
                        <p class="mb-3"><?php echo htmlspecialchars($farmer['email']); ?></p>
                        
                        <label class="text-muted mb-1">Phone</label>
                        <p class="mb-3"><?php echo htmlspecialchars($farmer['phone']); ?></p>
                        
                        <label class="text-muted mb-1">Address</label>
                        <p class="mb-3"><?php echo htmlspecialchars($farmer['address']); ?></p>
                    </div>
                    <a href="profile.php" class="btn btn-outline-primary btn-sm">
                        Edit Profile
                    </a>
                </div>
            </div>

            <!-- Recent Products -->
            <div class="col-md-4 mb-4">
                <div class="dashboard-card p-4">
                    <h3 class="h4 mb-4">Recent Products</h3>
                    <?php if (empty($recent_products)): ?>
                        <p class="text-muted">No products added yet.</p>
                    <?php else: ?>
                        <div class="d-grid gap-3">
                            <?php foreach ($recent_products as $product): ?>
                                <div class="product-item p-3 bg-light rounded">
                                    <h4 class="h6 mb-1"><?php echo htmlspecialchars($product['name']); ?></h4>
                                    <p class="text-muted small mb-2">
                                        Price: $<?php echo formatPrice($product['price']); ?>/<?php echo htmlspecialchars($product['unit']); ?>
                                    </p>
                                    <span class="badge bg-<?php echo $product['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <a href="products.php" class="btn btn-link btn-sm mt-3">View All Products</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html> 
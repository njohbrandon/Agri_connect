<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Require login for this page
requireLogin();

// Get search parameters
$search = $_GET['search'] ?? '';
$status = $_GET['status'] ?? 'all';
$sort = $_GET['sort'] ?? 'newest';

// Prepare the base query
$query = 'SELECT * FROM products WHERE farmer_id = ?';
$params = [$_SESSION['farmer_id']];

// Add search condition if provided
if (!empty($search)) {
    $query .= ' AND (name LIKE ? OR description LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Add status condition if not 'all'
if ($status !== 'all') {
    $query .= ' AND status = ?';
    $params[] = $status;
}

// Add sorting
switch ($sort) {
    case 'name_asc':
        $query .= ' ORDER BY name ASC';
        break;
    case 'name_desc':
        $query .= ' ORDER BY name DESC';
        break;
    case 'price_asc':
        $query .= ' ORDER BY price ASC';
        break;
    case 'price_desc':
        $query .= ' ORDER BY price DESC';
        break;
    case 'oldest':
        $query .= ' ORDER BY created_at ASC';
        break;
    default: // newest
        $query .= ' ORDER BY created_at DESC';
}

try {
    // Get total count for status tabs
    $countStmt = $pdo->prepare('SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = "inactive" THEN 1 ELSE 0 END) as inactive
        FROM products 
        WHERE farmer_id = ?');
    $countStmt->execute([$_SESSION['farmer_id']]);
    $counts = $countStmt->fetch();

    // Get products
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while fetching your products.';
}

$page_title = 'My Products';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Agri-Connect</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">My Products</h1>
            <a href="add_product.php" class="btn btn-success">
                <i class="bi bi-plus-circle"></i> Add New Product
            </a>
        </div>

        <!-- Filters and Search -->
        <div class="card shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" class="form-control" name="search" 
                                   placeholder="Search products..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="status">
                            <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>All Status</option>
                            <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                            <option value="inactive" <?php echo $status === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" name="sort">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                            <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>Name (A-Z)</option>
                            <option value="name_desc" <?php echo $sort === 'name_desc' ? 'selected' : ''; ?>>Name (Z-A)</option>
                            <option value="price_asc" <?php echo $sort === 'price_asc' ? 'selected' : ''; ?>>Price (Low-High)</option>
                            <option value="price_desc" <?php echo $sort === 'price_desc' ? 'selected' : ''; ?>>Price (High-Low)</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Status Tabs -->
        <ul class="nav nav-tabs mb-4">
            <li class="nav-item">
                <a class="nav-link <?php echo $status === 'all' ? 'active' : ''; ?>" 
                   href="?status=all">
                    All <span class="badge bg-secondary"><?php echo $counts['total']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status === 'active' ? 'active' : ''; ?>" 
                   href="?status=active">
                    Active <span class="badge bg-success"><?php echo $counts['active']; ?></span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $status === 'inactive' ? 'active' : ''; ?>" 
                   href="?status=inactive">
                    Inactive <span class="badge bg-secondary"><?php echo $counts['inactive']; ?></span>
                </a>
            </li>
        </ul>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <div class="text-center py-5">
                <i class="bi bi-box display-1 text-muted"></i>
                <p class="lead mt-3">No products found</p>
                <?php if (!empty($search) || $status !== 'all'): ?>
                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                    <a href="products.php" class="btn btn-outline-primary">Clear Filters</a>
                <?php else: ?>
                    <p class="text-muted">Start by adding your first product</p>
                    <a href="add_product.php" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Add New Product
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($products as $product): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <img src="<?php echo !empty($product['image']) ? '../uploads/products/' . $product['image'] : '../assets/images/default-product.jpg'; ?>"
                                 class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 style="height: 200px; object-fit: cover;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0">
                                        <?php echo htmlspecialchars($product['name']); ?>
                                    </h5>
                                    <span class="badge bg-<?php echo $product['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($product['status']); ?>
                                    </span>
                                </div>
                                <p class="card-text text-muted small mb-2">
                                    Added: <?php echo date('M j, Y', strtotime($product['created_at'])); ?>
                                </p>
                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0"><?php echo formatPrice($product['price']); ?></h6>
                                    <div>
                                        <a href="<?php echo getWhatsAppLink($farmer['phone'], 'Hi, I\'m interested in your product: ' . $product['name']); ?>" 
                                           class="btn btn-success btn-sm" target="_blank">
                                            <i class="bi bi-whatsapp"></i> WhatsApp
                                        </a>
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-danger btn-sm"
                                           onclick="return confirm('Are you sure you want to delete this product?');">
                                            <i class="bi bi-trash"></i> Delete
                                        </a>
                                    </div>
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
    <script>
        function confirmDelete(productId) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                window.location.href = `delete_product.php?id=${productId}`;
            }
        }
    </script>
</body>
</html> 
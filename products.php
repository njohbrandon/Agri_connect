<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Initialize filters
$search = filter_input(INPUT_GET, 'search', FILTER_SANITIZE_STRING);
$category = filter_input(INPUT_GET, 'category', FILTER_SANITIZE_STRING);
$min_price = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_FLOAT);
$max_price = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_FLOAT);
$location = filter_input(INPUT_GET, 'location', FILTER_SANITIZE_STRING);
$sort = filter_input(INPUT_GET, 'sort', FILTER_SANITIZE_STRING) ?? 'newest';

// Pagination
$page = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?? 1;
$per_page = 12; // Products per page
$offset = ($page - 1) * $per_page;

try {
    // Build query conditions
    $conditions = ['p.status IN ("active", "inactive")'];
    $params = [];

    if ($search) {
        $conditions[] = '(name LIKE ? OR description LIKE ?)';
        $params[] = "%$search%";
        $params[] = "%$search%";
    }

    if ($category) {
        $conditions[] = 'p.category = ?';
        $params[] = $category;
    }

    if ($min_price !== false && $min_price !== null) {
        $conditions[] = 'p.price >= ?';
        $params[] = $min_price;
    }

    if ($max_price !== false && $max_price !== null) {
        $conditions[] = 'p.price <= ?';
        $params[] = $max_price;
    }

    if ($location) {
        $conditions[] = 'EXISTS (SELECT 1 FROM farmers f2 WHERE f2.id = p.farmer_id AND f2.address LIKE ?)';
        $params[] = "%$location%";
    }

    // Build the final query
    $where_clause = implode(' AND ', $conditions);
    
    // Get total products count for pagination
    $count_sql = "SELECT COUNT(*) FROM products p WHERE $where_clause";
    $stmt = $pdo->prepare($count_sql);
    $stmt->execute($params);
    $total_products = $stmt->fetchColumn();
    $total_pages = ceil($total_products / $per_page);

    // Sort options
    $sort_options = [
        'newest' => 'created_at DESC',
        'price_low' => 'price ASC',
        'price_high' => 'price DESC',
        'name_asc' => 'name ASC'
    ];
    $order_by = $sort_options[$sort] ?? 'created_at DESC';

    // Get products
    $sql = "SELECT p.*, f.name as farmer_name, f.phone as farmer_phone, f.address as farmer_location 
            FROM products p 
            JOIN farmers f ON p.farmer_id = f.id 
            WHERE $where_clause 
            ORDER BY $order_by 
            LIMIT ? OFFSET ?";
    
    $params[] = $per_page;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Get all categories for filter
    $stmt = $pdo->query('SELECT DISTINCT name FROM categories ORDER BY name');
    $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Get price range
    $stmt = $pdo->query('SELECT MIN(price) as min, MAX(price) as max FROM products WHERE status = "active"');
    $price_range = $stmt->fetch();

    // Get all unique locations
    $stmt = $pdo->query('SELECT DISTINCT address FROM farmers ORDER BY address');
    $locations = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while fetching products.';
}

$page_title = 'Browse Products';
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
    <!-- Nouislider CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.5.0/nouislider.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    <!-- Custom JS -->
    <script src="assets/js/products.js"></script>
    <style>
        .filter-sidebar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-right: 1px solid rgba(0, 0, 0, 0.1);
            height: calc(100vh - 72px);
            position: sticky;
            top: 72px;
            overflow-y: auto;
        }
        .product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .product-image {
            height: 200px;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .price-slider {
            margin-top: 2rem;
            padding: 0 1rem;
        }
        .noUi-connect {
            background: #198754;
        }
        .filter-header {
            border-bottom: 2px solid #198754;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
            color: #198754;
        }
        .sort-dropdown .dropdown-item.active {
            background-color: #198754;
        }
        .pagination .page-item.active .page-link {
            background-color: #198754;
            border-color: #198754;
        }
        .pagination .page-link {
            color: #198754;
        }
        .search-bar {
            border-radius: 50px;
            padding-left: 1rem;
            padding-right: 1rem;
        }
        .search-icon {
            color: #198754;
        }
        @media (max-width: 768px) {
            .filter-sidebar {
                height: auto;
                position: relative;
                top: 0;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container-fluid py-5">
        <div class="row">
            <!-- Filter Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="filter-sidebar p-4">
                    <h4 class="filter-header mb-4">
                        <i class="bi bi-funnel-fill me-2"></i>Filters
                    </h4>

                    <form id="filterForm" method="GET" class="needs-validation" novalidate>
                        <!-- Search -->
                        <div class="mb-4">
                            <label class="form-label">Search Products</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0">
                                    <i class="bi bi-search search-icon"></i>
                                </span>
                                <input type="text" name="search" class="form-control search-bar border-start-0" 
                                       placeholder="Search products..." 
                                       value="<?php echo htmlspecialchars($search ?? ''); ?>">
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="mb-4">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" 
                                            <?php echo $category === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="mb-4">
                            <label class="form-label">Price Range (XAF)</label>
                            <div id="priceSlider" class="price-slider"></div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <input type="number" name="min_price" id="minPrice" 
                                           class="form-control form-control-sm" 
                                           value="<?php echo $min_price ?? $price_range['min']; ?>">
                                </div>
                                <div class="col-6">
                                    <input type="number" name="max_price" id="maxPrice" 
                                           class="form-control form-control-sm" 
                                           value="<?php echo $max_price ?? $price_range['max']; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="mb-4">
                            <label class="form-label">Location</label>
                            <select name="location" class="form-select">
                                <option value="">All Locations</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?php echo htmlspecialchars($loc); ?>" 
                                            <?php echo $location === $loc ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($loc); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Sort -->
                        <div class="mb-4">
                            <label class="form-label">Sort By</label>
                            <select name="sort" class="form-select">
                                <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>
                                    Newest First
                                </option>
                                <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>
                                    Price: Low to High
                                </option>
                                <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>
                                    Price: High to Low
                                </option>
                                <option value="name_asc" <?php echo $sort === 'name_asc' ? 'selected' : ''; ?>>
                                    Name: A to Z
                                </option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-funnel me-2"></i>Apply Filters
                        </button>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Results Summary and Actions -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="mb-0">
                        <?php echo $total_products; ?> Products Found
                        <?php if ($search): ?>
                            for "<?php echo htmlspecialchars($search); ?>"
                        <?php endif; ?>
                    </h4>
                    <div class="d-flex gap-2 align-items-center">
                        <button id="compare-products-btn" 
                                class="btn btn-outline-success" 
                                onclick="comparisonManager.openComparisonModal()" 
                                disabled>
                            <i class="bi bi-grid-3x3-gap"></i>
                            Compare
                            <span id="comparison-count" 
                                  class="badge bg-success rounded-pill ms-1" 
                                  style="display: none;">0</span>
                        </button>
                        <button class="btn btn-outline-success" 
                                data-bs-toggle="modal" 
                                data-bs-target="#favoritesModal">
                            <i class="bi bi-heart"></i>
                            Favorites
                            <span id="favorites-count" 
                                  class="badge bg-success rounded-pill ms-1" 
                                  style="display: none;">0</span>
                        </button>
                    </div>
                </div>

                <?php if (empty($products)): ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>No products found matching your criteria.
                    </div>
                <?php else: ?>
                    <div class="row g-4">
                        <?php foreach ($products as $product): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card product-card h-100">
                                    <span class="badge bg-success category-badge">
                                        <?php echo htmlspecialchars($product['category']); ?>
                                    </span>
                                    <!-- Product Actions -->
                                    <div class="position-absolute top-0 start-0 p-3 d-flex gap-2">
                                        <button class="btn btn-outline-success btn-sm rounded-circle"
                                                data-favorite-id="<?php echo $product['id']; ?>"
                                                onclick="favoritesManager.toggleFavorite(<?php echo $product['id']; ?>, '<?php echo addslashes($product['name']); ?>')">
                                            <i class="bi bi-heart"></i>
                                        </button>
                                        <button class="btn btn-outline-success btn-sm rounded-circle"
                                                data-compare-id="<?php echo $product['id']; ?>"
                                                onclick="comparisonManager.toggleComparison(<?php echo $product['id']; ?>, <?php echo htmlspecialchars(json_encode($product), ENT_QUOTES); ?>)">
                                            <i class="bi bi-square"></i>
                                        </button>
                                    </div>
                                    <img src="<?php echo !empty($product['image']) ? 'uploads/products/' . $product['image'] : 'assets/images/default-product.jpg'; ?>" 
                                         class="card-img-top product-image" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($product['name']); ?>
                                        </h5>
                                        <p class="card-text text-muted">
                                            <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 mb-0">
                                                <?php echo number_format($product['price'], 0, '.', ','); ?> XAF
                                            </span>
                                            <a href="product_details.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-outline-success btn-sm">
                                                <i class="bi bi-eye me-1"></i>View Details
                                            </a>
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-top-0">
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt me-1"></i>
                                            <?php echo htmlspecialchars($product['farmer_location']); ?>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav class="mt-5">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>

                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Favorites Modal -->
    <div class="modal fade" id="favoritesModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-heart-fill text-success me-2"></i>
                        Favorite Products
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="favorites-list">
                        <!-- Will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Nouislider JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/noUiSlider/15.5.0/nouislider.min.js"></script>

    <script>
        // Initialize price range slider
        const priceSlider = document.getElementById('priceSlider');
        const minPriceInput = document.getElementById('minPrice');
        const maxPriceInput = document.getElementById('maxPrice');

        if (priceSlider) {
            noUiSlider.create(priceSlider, {
                start: [
                    <?php echo $min_price ?? $price_range['min']; ?>, 
                    <?php echo $max_price ?? $price_range['max']; ?>
                ],
                connect: true,
                range: {
                    'min': <?php echo $price_range['min']; ?>,
                    'max': <?php echo $price_range['max']; ?>
                },
                step: 100
            });

            priceSlider.noUiSlider.on('update', function (values, handle) {
                const value = Math.round(values[handle]);
                if (handle === 0) {
                    minPriceInput.value = value;
                } else {
                    maxPriceInput.value = value;
                }
            });

            // Update slider when inputs change
            minPriceInput.addEventListener('change', function () {
                priceSlider.noUiSlider.set([this.value, null]);
            });

            maxPriceInput.addEventListener('change', function () {
                priceSlider.noUiSlider.set([null, this.value]);
            });
        }

        // Form validation
        (function () {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Initialize favorites modal
        const favoritesModal = document.getElementById('favoritesModal');
        if (favoritesModal) {
            favoritesModal.addEventListener('show.bs.modal', async () => {
                const favoritesList = document.getElementById('favorites-list');
                const favorites = JSON.parse(localStorage.getItem('favorites')) || [];
                
                if (favorites.length === 0) {
                    favoritesList.innerHTML = `
                        <div class="text-center py-4">
                            <i class="bi bi-heart text-muted display-4"></i>
                            <p class="mt-3">No favorite products yet.</p>
                        </div>
                    `;
                    return;
                }

                try {
                    const response = await fetch('api/get_products.php?ids=' + favorites.map(f => f.id).join(','));
                    const products = await response.json();

                    favoritesList.innerHTML = products.map(product => `
                        <div class="d-flex align-items-center border-bottom py-3">
                            <img src="${product.image || 'assets/images/default-product.jpg'}" 
                                 class="rounded" 
                                 style="width: 60px; height: 60px; object-fit: cover;">
                            <div class="ms-3 flex-grow-1">
                                <h6 class="mb-0">${product.name}</h6>
                                <small class="text-muted">${product.price} XAF/${product.unit}</small>
                            </div>
                            <div class="ms-3">
                                <a href="product_details.php?id=${product.id}" 
                                   class="btn btn-outline-success btn-sm">
                                    View
                                </a>
                                <button class="btn btn-outline-danger btn-sm ms-2"
                                        onclick="favoritesManager.toggleFavorite(${product.id}, '${product.name}')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    `).join('');
                } catch (error) {
                    console.error('Error fetching favorite products:', error);
                    favoritesList.innerHTML = `
                        <div class="alert alert-danger">
                            Error loading favorite products. Please try again.
                        </div>
                    `;
                }
            });
        }
    </script>
</body>
</html> 
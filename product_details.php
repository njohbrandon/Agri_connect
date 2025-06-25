<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$product_id) {
    header('Location: products.php');
    exit();
}

try {
    // Get product details with farmer information
    $stmt = $pdo->prepare('SELECT p.*, f.name as farmer_name, f.phone, f.email, f.address as location
                          FROM products p 
                          JOIN farmers f ON p.farmer_id = f.id 
                          WHERE p.id = ? AND p.status = "active"');
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: products.php');
        exit();
    }

    // Format phone number for display and WhatsApp
    $product['formatted_phone'] = formatPhoneNumber($product['phone']);
    $product['whatsapp_link'] = getWhatsAppLink($product['phone'], 
        "Hi, I'm interested in your product: " . $product['name']);

    // Get other products from the same farmer
    $stmt = $pdo->prepare('SELECT * FROM products 
                          WHERE farmer_id = ? AND id != ? AND status = "active" 
                          LIMIT 4');
    $stmt->execute([$product['farmer_id'], $product_id]);
    $related_products = $stmt->fetchAll();

    // Get similar products in the same category
    $stmt = $pdo->prepare('SELECT p.*, f.name as farmer_name 
                          FROM products p 
                          JOIN farmers f ON p.farmer_id = f.id 
                          WHERE p.category = ? AND p.id != ? AND p.status = "active" 
                          LIMIT 4');
    $stmt->execute([$product['category'], $product_id]);
    $similar_products = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while fetching product details.';
}

$page_title = htmlspecialchars($product['name']);
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
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .product-image {
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .farmer-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .contact-btn {
            border-radius: 50px;
            padding: 10px 25px;
            transition: all 0.3s ease;
        }
        .contact-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.2);
        }
        .product-meta {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
        }
        .related-product-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
        }
        .related-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        .related-product-image {
            height: 200px;
            object-fit: cover;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1;
        }
        .breadcrumb-item + .breadcrumb-item::before {
            color: #198754;
        }
        .description-text {
            line-height: 1.8;
            color: #4a5568;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.php" class="text-success">
                        <i class="bi bi-house-door"></i> Home
                    </a>
                </li>
                <li class="breadcrumb-item">
                    <a href="products.php" class="text-success">Products</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="products.php?category=<?php echo urlencode($product['category']); ?>" 
                       class="text-success">
                        <?php echo htmlspecialchars($product['category']); ?>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?php echo htmlspecialchars($product['name']); ?>
                </li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Product Image -->
            <div class="col-lg-6">
                <img src="<?php echo !empty($product['image']) ? 'uploads/products/' . $product['image'] : 'assets/images/default-product.jpg'; ?>" 
                     class="img-fluid product-image w-100" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>

            <!-- Product Info -->
            <div class="col-lg-6">
                <div class="ps-lg-4">
                    <span class="badge bg-success mb-3">
                        <?php echo htmlspecialchars($product['category']); ?>
                    </span>
                    <h1 class="display-5 fw-bold mb-4">
                        <?php echo htmlspecialchars($product['name']); ?>
                    </h1>

                    <div class="product-meta mb-4">
                        <div class="row g-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Price</small>
                                <span class="h3 mb-0">
                                    <?php echo number_format($product['price'], 0, '.', ','); ?> XAF
                                </span>
                                <small class="text-muted">per <?php echo htmlspecialchars($product['unit']); ?></small>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Available Quantity</small>
                                <span class="h3 mb-0">
                                    <?php echo number_format($product['quantity'], 0, '.', ','); ?>
                                </span>
                                <small class="text-muted"><?php echo htmlspecialchars($product['unit']); ?></small>
                            </div>
                        </div>
                    </div>

                    <div class="description-text mb-4">
                        <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                    </div>

                    <!-- Social Sharing -->
                    <div class="sharing-section bg-light rounded p-3 mb-4">
                        <h6 class="mb-3">
                            <i class="bi bi-share-fill me-2"></i>Share this Product
                        </h6>
                        <div class="d-flex gap-2">
                            <!-- WhatsApp -->
                            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode("Check out this product: " . $product['name'] . " on Agri-Connect\n" . getCurrentURL()); ?>" 
                               target="_blank"
                               class="btn btn-success">
                                <i class="bi bi-whatsapp"></i>
                            </a>
                            
                            <!-- Facebook -->
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(getCurrentURL()); ?>" 
                               target="_blank"
                               class="btn btn-primary">
                                <i class="bi bi-facebook"></i>
                            </a>
                            
                            <!-- Twitter/X -->
                            <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode("Check out " . $product['name'] . " on Agri-Connect"); ?>&url=<?php echo urlencode(getCurrentURL()); ?>" 
                               target="_blank"
                               class="btn btn-dark">
                                <i class="bi bi-twitter-x"></i>
                            </a>
                            
                            <!-- Email -->
                            <a href="mailto:?subject=<?php echo urlencode("Check out this product on Agri-Connect"); ?>&body=<?php echo urlencode("I found this interesting product:\n\n" . $product['name'] . "\n\nPrice: " . number_format($product['price'], 0, '.', ',') . " XAF\n\nCheck it out here: " . getCurrentURL()); ?>" 
                               class="btn btn-secondary">
                                <i class="bi bi-envelope"></i>
                            </a>
                            
                            <!-- Copy Link -->
                            <button type="button" 
                                    class="btn btn-outline-success" 
                                    onclick="copyProductLink()"
                                    id="copyLinkBtn">
                                <i class="bi bi-link-45deg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Farmer Card -->
                    <div class="farmer-card p-4 mb-4">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <div class="bg-success text-white rounded-circle p-3">
                                    <i class="bi bi-person-circle h3 mb-0"></i>
                                </div>
                            </div>
                            <div class="col">
                                <h5 class="mb-1">
                                    <?php echo htmlspecialchars($product['farmer_name']); ?>
                                </h5>
                                <p class="text-muted mb-0">
                                    <i class="bi bi-geo-alt me-1"></i>
                                    <?php echo htmlspecialchars($product['location']); ?>
                                </p>
                            </div>
                        </div>
                        <hr>
                        <div class="d-grid gap-2">
                            <a href="tel:<?php echo $product['formatted_phone']; ?>" 
                               class="btn btn-success contact-btn">
                                <i class="bi bi-telephone me-2"></i>
                                <?php echo $product['formatted_phone']; ?>
                            </a>
                            <a href="<?php echo $product['whatsapp_link']; ?>" 
                               class="btn btn-outline-success contact-btn" 
                               target="_blank">
                                <i class="bi bi-whatsapp me-2"></i>
                                Contact via WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
            <section class="related-products mt-5">
                <h3 class="mb-4">More from this Farmer</h3>
                <div class="row g-4">
                    <?php foreach ($related_products as $related): ?>
                        <div class="col-md-3">
                            <div class="card related-product-card h-100">
                                <span class="badge bg-success category-badge">
                                    <?php echo htmlspecialchars($related['category']); ?>
                                </span>
                                <img src="<?php echo !empty($related['image']) ? 'uploads/products/' . $related['image'] : 'assets/images/default-product.jpg'; ?>" 
                                     class="card-img-top related-product-image" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($related['name']); ?>
                                    </h5>
                                    <p class="card-text text-muted">
                                        <?php echo number_format($related['price'], 0, '.', ','); ?> XAF
                                        <small>per <?php echo htmlspecialchars($related['unit']); ?></small>
                                    </p>
                                    <a href="product_details.php?id=<?php echo $related['id']; ?>" 
                                       class="btn btn-outline-success btn-sm w-100">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <!-- Similar Products -->
        <?php if (!empty($similar_products)): ?>
            <section class="similar-products mt-5">
                <h3 class="mb-4">Similar Products</h3>
                <div class="row g-4">
                    <?php foreach ($similar_products as $similar): ?>
                        <div class="col-md-3">
                            <div class="card related-product-card h-100">
                                <span class="badge bg-success category-badge">
                                    <?php echo htmlspecialchars($similar['category']); ?>
                                </span>
                                <img src="<?php echo !empty($similar['image']) ? 'uploads/products/' . $similar['image'] : 'assets/images/default-product.jpg'; ?>" 
                                     class="card-img-top related-product-image" 
                                     alt="<?php echo htmlspecialchars($similar['name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title">
                                        <?php echo htmlspecialchars($similar['name']); ?>
                                    </h5>
                                    <p class="card-text">
                                        <small class="text-muted">by <?php echo htmlspecialchars($similar['farmer_name']); ?></small>
                                    </p>
                                    <p class="card-text text-muted">
                                        <?php echo number_format($similar['price'], 0, '.', ','); ?> XAF
                                        <small>per <?php echo htmlspecialchars($similar['unit']); ?></small>
                                    </p>
                                    <a href="product_details.php?id=<?php echo $similar['id']; ?>" 
                                       class="btn btn-outline-success btn-sm w-100">
                                        View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Function to copy product link
        function copyProductLink() {
            const currentURL = window.location.href;
            navigator.clipboard.writeText(currentURL).then(() => {
                // Change button icon temporarily
                const btn = document.getElementById('copyLinkBtn');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check-lg"></i>';
                btn.classList.add('btn-success');
                btn.classList.remove('btn-outline-success');
                
                // Show toast notification
                const toastHtml = `
                    <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <i class="bi bi-check-circle me-2"></i>
                                Link copied to clipboard!
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
                
                const toastContainer = document.getElementById('toast-container');
                if (!toastContainer) {
                    const container = document.createElement('div');
                    container.id = 'toast-container';
                    container.className = 'position-fixed bottom-0 end-0 p-3';
                    document.body.appendChild(container);
                }
                
                const toastElement = new DOMParser().parseFromString(toastHtml, 'text/html').body.firstChild;
                document.getElementById('toast-container').appendChild(toastElement);
                
                const toast = new bootstrap.Toast(toastElement, { delay: 3000 });
                toast.show();
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-outline-success');
                }, 2000);

                // Remove toast element after it's hidden
                toastElement.addEventListener('hidden.bs.toast', () => {
                    toastElement.remove();
                });
            }).catch(err => {
                console.error('Failed to copy text: ', err);
            });
        }
    </script>
</body>
</html> 
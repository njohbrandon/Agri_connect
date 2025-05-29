<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get featured products for carousel
$featured_products = getFeaturedProducts(6);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agri-Connect - Connecting Farmers and Buyers</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-flower1"></i> Agri-Connect
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Browse Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Contact</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="farmer/login.php">Farmer Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-success text-white px-3" href="farmer/register.php">
                            Register as Farmer
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6" data-aos="fade-right">
                    <h1 class="display-4 fw-bold mb-4">Fresh From Farm to Table</h1>
                    <p class="lead mb-4">Connect directly with local farmers and get fresh, quality produce delivered to your doorstep.</p>
                    <div class="d-grid gap-3 d-md-flex justify-content-md-start">
                        <a href="products.php" class="btn btn-success btn-lg px-4">
                            Browse Products
                        </a>
                        <a href="farmer/register.php" class="btn btn-outline-light btn-lg px-4">
                            Become a Seller
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Products Carousel -->
    <section class="product-carousel">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Featured Products</h2>
            <div id="productCarousel" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php 
                    $chunks = array_chunk($featured_products, 3);
                    foreach($chunks as $index => $chunk): 
                    ?>
                    <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                        <div class="row">
                            <?php foreach($chunk as $product): ?>
                            <div class="col-md-4">
                                <div class="card product-card">
                                    <img src="<?php echo !empty($product['image']) ? 'uploads/products/' . $product['image'] : 'assets/images/default-product.jpg'; ?>" 
                                         class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                        <p class="card-text text-muted">
                                            <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                                        </p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="h5 mb-0">$<?php echo formatPrice($product['price']); ?></span>
                                            <a href="product_details.php?id=<?php echo $product['id']; ?>" 
                                               class="btn btn-outline-success">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Why Choose Us</h2>
            <div class="row g-4">
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="feature-card">
                        <i class="bi bi-hand-thumbs-up feature-icon"></i>
                        <h3 class="h4 mb-3">Quality Assured</h3>
                        <p class="text-muted">All our products are verified for quality and freshness before delivery.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="feature-card">
                        <i class="bi bi-truck feature-icon"></i>
                        <h3 class="h4 mb-3">Direct Delivery</h3>
                        <p class="text-muted">Get fresh produce delivered directly from farms to your doorstep.</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="300">
                    <div class="feature-card">
                        <i class="bi bi-shield-check feature-icon"></i>
                        <h3 class="h4 mb-3">Secure Payments</h3>
                        <p class="text-muted">Your transactions are protected with secure payment methods.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-section">
        <div class="container">
            <div class="row">
                <div class="col-md-4" data-aos="fade-up">
                    <div class="stat-card">
                        <div class="stat-number" data-target="500">0</div>
                        <p class="text-muted mb-0">Happy Customers</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-card">
                        <div class="stat-number" data-target="100">0</div>
                        <p class="text-muted mb-0">Registered Farmers</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-card">
                        <div class="stat-number" data-target="1000">0</div>
                        <p class="text-muted mb-0">Products Listed</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="cta-section">
        <div class="container text-center">
            <h2 class="display-5 mb-4" data-aos="fade-up">Ready to Get Started?</h2>
            <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                Join our community of farmers and buyers today.
            </p>
            <a href="farmer/register.php" class="btn btn-light btn-lg" data-aos="fade-up" data-aos-delay="200">
                Register as Farmer
            </a>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Custom JS -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                document.querySelector('.navbar').classList.add('scrolled');
            } else {
                document.querySelector('.navbar').classList.remove('scrolled');
            }
        });

        // Animate statistics
        const stats = document.querySelectorAll('.stat-number');
        stats.forEach(stat => {
            const target = parseInt(stat.getAttribute('data-target'));
            let current = 0;
            const increment = target / 50; // Adjust speed
            const updateCount = () => {
                if (current < target) {
                    current += increment;
                    stat.textContent = Math.ceil(current);
                    setTimeout(updateCount, 30);
                } else {
                    stat.textContent = target;
                }
            };
            // Start animation when element is in viewport
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        updateCount();
                        observer.unobserve(entry.target);
                    }
                });
            });
            observer.observe(stat);
        });

        // Product carousel auto-slide
        const productCarousel = new bootstrap.Carousel(document.getElementById('productCarousel'), {
            interval: 5000,
            wrap: true
        });
    </script>
</body>
</html>

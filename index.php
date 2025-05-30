<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

// Get featured products for carousel
$featured_products = getFeaturedProducts(6);

// Define hero section images
$hero_images = [
    'assets/images/hero1.jpg' => 'Fresh Vegetables Direct from Farm',
    'assets/images/hero2.jpg' => 'Quality Organic Produce',
    'assets/images/hero3.jpg' => 'Support Local Farmers'
];
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
    <!-- Swiper CSS -->
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css">
    <!-- AOS Animation Library -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="home">
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section with Carousel -->
    <section class="hero-carousel">
        <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
            <div class="carousel-indicators">
                <?php $index = 0; foreach ($hero_images as $image => $caption): ?>
                    <button type="button" data-bs-target="#heroCarousel" 
                            data-bs-slide-to="<?php echo $index; ?>" 
                            <?php echo $index === 0 ? 'class="active" aria-current="true"' : ''; ?>
                            aria-label="Slide <?php echo $index + 1; ?>"></button>
                    <?php $index++; ?>
                <?php endforeach; ?>
            </div>
            <div class="carousel-inner">
                <?php $first = true; foreach ($hero_images as $image => $caption): ?>
                    <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                        <img src="<?php echo $image; ?>" class="d-block w-100" alt="<?php echo $caption; ?>">
                        <div class="carousel-caption">
                            <div class="container">
                                <div class="row align-items-center min-vh-100">
                                    <div class="col-md-7" data-aos="fade-right">
                                        <h1 class="display-4 fw-bold text-white mb-4"><?php echo $caption; ?></h1>
                                        <p class="lead text-white mb-4">Connect directly with local farmers and get fresh, quality produce delivered to your doorstep.</p>
                                        <div class="d-grid gap-3 d-md-flex justify-content-md-start">
                                            <a href="products.php" class="btn btn-success btn-lg px-5">
                                                <i class="bi bi-shop me-2"></i>Browse Products
                                            </a>
                                            <a href="farmer/register.php" class="btn btn-outline-light btn-lg px-5">
                                                <i class="bi bi-person-plus me-2"></i>Become a Seller
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $first = false; ?>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <!-- Featured Products Carousel -->
    <section class="product-carousel py-5">
        <div class="container">
            <h2 class="text-center mb-5" data-aos="fade-up">Featured Products</h2>
            <div class="swiper-container" data-aos="fade-up">
                <div class="swiper-wrapper">
                    <?php foreach($featured_products as $product): ?>
                        <div class="swiper-slide">
                            <div class="product-card">
                                <img src="<?php echo !empty($product['image']) ? 'uploads/products/' . $product['image'] : 'assets/images/default-product.jpg'; ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                                    <p class="card-text text-muted">
                                        <?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?>
                                    </p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h5 mb-0"><?php echo formatPrice($product['price']); ?></span>
                                        <a href="<?php echo $product['whatsapp_link']; ?>" class="btn btn-success btn-sm" target="_blank">
                                            <i class="bi bi-whatsapp me-1"></i>Contact Seller
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features-section py-5">
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
                        <p class="mb-0">Happy Customers</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-card">
                        <div class="stat-number" data-target="100">0</div>
                        <p class="mb-0">Registered Farmers</p>
                    </div>
                </div>
                <div class="col-md-4" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-card">
                        <div class="stat-number" data-target="1000">0</div>
                        <p class="mb-0">Products Listed</p>
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
            <a href="farmer/register.php" class="btn btn-light btn-lg px-5" data-aos="fade-up" data-aos-delay="200">
                <i class="bi bi-person-plus me-2"></i>Register as Farmer
            </a>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <!-- AOS Animation Library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <!-- Custom JS -->
    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Initialize Swiper
        const swiper = new Swiper('.swiper-container', {
            slidesPerView: 1,
            spaceBetween: 30,
            loop: true,
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                640: {
                    slidesPerView: 2,
                },
                1024: {
                    slidesPerView: 3,
                },
            },
        });

        // Initialize Hero Carousel
        document.addEventListener('DOMContentLoaded', function() {
            const heroCarousel = document.getElementById('heroCarousel');
            if (heroCarousel) {
                const carousel = new bootstrap.Carousel(heroCarousel, {
                    interval: 5000,
                    ride: true,
                    wrap: true,
                    touch: true
                });
            }
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
            const increment = target / 50;
            const updateCount = () => {
                if (current < target) {
                    current += increment;
                    stat.textContent = Math.ceil(current);
                    setTimeout(updateCount, 30);
                } else {
                    stat.textContent = target;
                }
            };
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
    </script>
</body>
</html>

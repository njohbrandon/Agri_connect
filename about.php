<?php
$page_title = 'About Us';
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
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="display-4 mb-4">About Agri-Connect</h1>
                <p class="lead mb-5">Connecting farmers and buyers directly, making fresh produce accessible to everyone.</p>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Our Mission</h3>
                        <p class="card-text">At Agri-Connect, our mission is to empower local farmers by providing them with a digital platform to reach buyers directly. We aim to create a sustainable agricultural ecosystem that benefits both farmers and consumers.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <h3 class="card-title mb-4">Our Vision</h3>
                        <p class="card-text">We envision a future where every farmer has access to fair market opportunities and every consumer has access to fresh, locally-sourced produce. Through technology, we're making this vision a reality.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12">
                <h2 class="text-center mb-4">Why Choose Agri-Connect?</h2>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="bi bi-people display-4 text-success mb-3"></i>
                    <h4>Direct Connection</h4>
                    <p class="text-muted">Connect directly with local farmers and eliminate middlemen, ensuring better prices for both parties.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="bi bi-shield-check display-4 text-success mb-3"></i>
                    <h4>Quality Assurance</h4>
                    <p class="text-muted">All our farmers are verified, ensuring you get high-quality, fresh produce every time.</p>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="text-center">
                    <i class="bi bi-graph-up display-4 text-success mb-3"></i>
                    <h4>Market Growth</h4>
                    <p class="text-muted">Help local farmers grow their business and reach a wider customer base.</p>
                </div>
            </div>
        </div>

        <div class="row align-items-center mb-5">
            <div class="col-md-6">
                <h2 class="mb-4">Our Story</h2>
                <p>Agri-Connect was founded with a simple yet powerful idea: to bridge the gap between local farmers and consumers. We recognized the challenges faced by farmers in reaching customers and the growing demand for fresh, locally-sourced produce.</p>
                <p>Today, we're proud to serve the farming community in Bamenda and surrounding regions, helping farmers prosper while providing consumers with access to fresh, quality produce.</p>
            </div>
            <div class="col-md-6">
                <img src="assets/images/about-image.jpg" alt="About Agri-Connect" class="img-fluid rounded shadow">
            </div>
        </div>

        <div class="text-center">
            <h2 class="mb-4">Join Our Community</h2>
            <p class="lead mb-4">Whether you're a farmer looking to expand your market or a buyer seeking fresh produce, we welcome you to join our growing community.</p>
            <a href="farmer/register.php" class="btn btn-success btn-lg me-3">
                <i class="bi bi-person-plus"></i> Register as Farmer
            </a>
            <a href="products.php" class="btn btn-outline-success btn-lg">
                <i class="bi bi-shop"></i> Browse Products
            </a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
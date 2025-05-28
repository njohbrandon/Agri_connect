<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="/index.php">
                <i class="bi bi-flower1"></i> Agri-Connect
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/products.php">
                            <i class="bi bi-shop"></i> Browse Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/about.php">
                            <i class="bi bi-info-circle"></i> About Us
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/contact.php">
                            <i class="bi bi-envelope"></i> Contact
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['farmer_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['farmer_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="/farmer/dashboard.php">
                                        <i class="bi bi-speedometer2"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/farmer/products.php">
                                        <i class="bi bi-box"></i> My Products
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="/farmer/profile.php">
                                        <i class="bi bi-person"></i> Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="/farmer/logout.php">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/farmer/login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-success text-white px-3" href="/farmer/register.php">
                                <i class="bi bi-person-plus"></i> Register as Farmer
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>

<style>
    /* Navbar Styles */
    .navbar {
        transition: all 0.3s;
        background-color: rgba(255, 255, 255, 0.95) !important;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        padding: 1rem 0;
    }
    
    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        color: #198754 !important;
    }
    
    .nav-link {
        font-weight: 500;
        padding: 0.5rem 1rem !important;
        transition: color 0.3s;
    }
    
    .nav-link:hover {
        color: #198754 !important;
    }
    
    .dropdown-menu {
        border: none;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }
    
    .dropdown-item {
        padding: 0.7rem 1.5rem;
        transition: all 0.3s;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #198754;
        transform: translateX(5px);
    }
    
    .btn-success {
        background-color: #198754;
        border-color: #198754;
        transition: all 0.3s;
    }
    
    .btn-success:hover {
        background-color: #146c43;
        border-color: #146c43;
        transform: translateY(-2px);
    }
    
    /* Add margin to body to prevent content from hiding under fixed navbar */
    body {
        margin-top: 76px;
    }
    
    /* Exception for index page hero section */
    body.home {
        margin-top: 0;
    }
</style>

<script>
    // Add 'home' class to body if on index page
    if (window.location.pathname === '/' || window.location.pathname === '/index.php') {
        document.body.classList.add('home');
    }
</script> 
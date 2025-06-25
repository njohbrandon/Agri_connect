<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get the current directory depth to handle relative paths
$current_path = $_SERVER['PHP_SELF'];
$root_path = '';
if (strpos($current_path, '/farmer/') !== false) {
    $root_path = '../';
}
?>
<header>
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="<?php echo $root_path; ?>index.php">
                <i class="bi bi-flower1"></i> Agri-Connect
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $root_path; ?>products.php">
                            <i class="bi bi-shop"></i> Browse Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $root_path; ?>about.php">
                            <i class="bi bi-info-circle"></i> About Us
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo $root_path; ?>contact.php">
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
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>farmer/dashboard.php">
                                        <i class="bi bi-speedometer2"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>farmer/products.php">
                                        <i class="bi bi-box"></i> My Products
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>farmer/profile.php">
                                        <i class="bi bi-person"></i> Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo $root_path; ?>farmer/logout.php">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php elseif (isset($_SESSION['buyer_id'])): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['buyer_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>buyer/dashboard.php">
                                        <i class="bi bi-speedometer2"></i> Dashboard
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>buyer/saved-products.php">
                                        <i class="bi bi-heart"></i> Saved Products
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>buyer/inquiries.php">
                                        <i class="bi bi-chat-dots"></i> My Inquiries
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>buyer/notifications.php">
                                        <i class="bi bi-bell"></i> Notifications
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>buyer/profile.php">
                                        <i class="bi bi-person"></i> Profile
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="<?php echo $root_path; ?>buyer/logout.php">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-box-arrow-in-right"></i> Login
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>farmer/login.php">
                                        <i class="bi bi-person-badge"></i> Login as Farmer
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>buyer/login.php">
                                        <i class="bi bi-person"></i> Login as Buyer
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle btn btn-success text-white px-3" href="#" id="registerDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-plus"></i> Register
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>farmer/register.php">
                                        <i class="bi bi-person-badge"></i> Register as Farmer
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?php echo $root_path; ?>buyer/register.php">
                                        <i class="bi bi-person"></i> Register as Buyer
                                    </a>
                                </li>
                            </ul>
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
        padding: 0.5rem;
        min-width: 200px;
    }
    
    .dropdown-item {
        padding: 0.7rem 1.5rem;
        transition: all 0.3s;
        border-radius: 7px;
        margin: 2px 0;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #198754;
        transform: translateX(5px);
    }
    
    .dropdown-item i {
        margin-right: 8px;
        width: 20px;
        text-align: center;
    }

    .nav-item.dropdown:hover .dropdown-menu {
        display: block;
    }

    .nav-link.dropdown-toggle.btn-success {
        border-radius: 10px;
    }

    .nav-link.dropdown-toggle.btn-success:hover,
    .nav-link.dropdown-toggle.btn-success:focus {
        background-color: #146c43;
        border-color: #146c43;
        transform: translateY(-2px);
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
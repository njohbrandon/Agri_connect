<?php
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!-- Modern Admin Sidebar -->
<div class="col-auto px-0 sidebar">
    <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-4 min-vh-100">
        <a href="/farmers_market_place/admin/index.php" class="d-flex align-items-center pb-3 mb-md-1 text-decoration-none text-white">
            <span class="fs-5 d-none d-sm-inline">Agri-Connect Admin</span>
        </a>
        <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100">
            <li class="nav-item w-100">
                <a href="/farmers_market_place/admin/index.php" class="nav-link <?php echo $current_page === 'index' || $current_page === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    <span class="d-none d-sm-inline">Dashboard</span>
                </a>
            </li>
            <li class="nav-item w-100">
                <a href="/farmers_market_place/admin/users/farmers.php" class="nav-link <?php echo $current_page === 'farmers' ? 'active' : ''; ?>">
                    <i class="fas fa-users me-2"></i>
                    <span class="d-none d-sm-inline">Farmers</span>
                </a>
            </li>
            <li class="nav-item w-100">
                <a href="/farmers_market_place/admin/users/buyers.php" class="nav-link <?php echo $current_page === 'buyers' ? 'active' : ''; ?>">
                    <i class="fas fa-shopping-cart me-2"></i>
                    <span class="d-none d-sm-inline">Buyers</span>
                </a>
            </li>
            <li class="nav-item w-100">
                <a href="/farmers_market_place/admin/products/index.php" class="nav-link <?php echo $current_page === 'products' ? 'active' : ''; ?>">
                    <i class="fas fa-box me-2"></i>
                    <span class="d-none d-sm-inline">Products</span>
                </a>
            </li>
            <li class="nav-item w-100">
                <a href="/farmers_market_place/admin/announcements/index.php" class="nav-link <?php echo $current_page === 'announcements' ? 'active' : ''; ?>">
                    <i class="fas fa-bullhorn me-2"></i>
                    <span class="d-none d-sm-inline">Announcements</span>
                </a>
            </li>
            <li class="nav-item w-100">
                <a href="/farmers_market_place/admin/settings/index.php" class="nav-link <?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog me-2"></i>
                    <span class="d-none d-sm-inline">Settings</span>
                </a>
            </li>
        </ul>
        <hr class="w-100">
        <div class="dropdown pb-4">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="sidebarUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-user-circle fs-4 me-2"></i>
                <span class="d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION['admin_username'] ?? 'Admin'); ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="sidebarUserDropdown">
                <li><a class="dropdown-item" href="/farmers_market_place/admin/profile.php">Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="/farmers_market_place/admin/auth/logout.php">Sign out</a></li>
            </ul>
        </div>
    </div>
</div> 
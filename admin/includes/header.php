<?php
// Ensure the user is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . (strpos($_SERVER['PHP_SELF'], 'admin/auth/') !== false ? '' : 'auth/') . 'login.php');
    exit();
}

// Get current page for active menu highlighting
$current_page = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Agri-Connect Admin</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2E7D32;
            --secondary-color: #66BB6A;
            --accent-color: #43A047;
        }
        
        .sidebar {
            background: var(--primary-color);
            min-height: 100vh;
            color: white;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 0.75rem 1.25rem;
            margin: 0.2rem 0;
            border-radius: 0.375rem;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar .nav-link.active {
            background: var(--secondary-color);
            color: white;
        }
        
        .main-content {
            background-color: #f8f9fa;
        }
        
        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }
    </style>
    <?php if (isset($extra_css)) echo $extra_css; ?>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-4 min-vh-100">
                    <a href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php" class="d-flex align-items-center pb-3 mb-md-1 text-decoration-none text-white">
                        <span class="fs-5 d-none d-sm-inline">Agri-Connect Admin</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100">
                        <li class="nav-item w-100">
                            <a href="<?php echo isset($base_path) ? $base_path : ''; ?>index.php" 
                               class="nav-link <?php echo $current_page === 'index' ? 'active' : ''; ?>">
                                <i class="bi bi-speedometer2 me-2"></i>
                                <span class="d-none d-sm-inline">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item w-100">
                            <a href="<?php echo isset($base_path) ? $base_path : ''; ?>users/farmers.php" 
                               class="nav-link <?php echo $current_page === 'farmers' ? 'active' : ''; ?>">
                                <i class="bi bi-person-badge me-2"></i>
                                <span class="d-none d-sm-inline">Farmers</span>
                            </a>
                        </li>
                        <li class="nav-item w-100">
                            <a href="<?php echo isset($base_path) ? $base_path : ''; ?>users/buyers.php" 
                               class="nav-link <?php echo $current_page === 'buyers' ? 'active' : ''; ?>">
                                <i class="bi bi-people me-2"></i>
                                <span class="d-none d-sm-inline">Buyers</span>
                            </a>
                        </li>
                        <li class="nav-item w-100">
                            <a href="<?php echo isset($base_path) ? $base_path : ''; ?>products/index.php" 
                               class="nav-link <?php echo $current_page === 'products' ? 'active' : ''; ?>">
                                <i class="bi bi-box-seam me-2"></i>
                                <span class="d-none d-sm-inline">Products</span>
                            </a>
                        </li>
                        <li class="nav-item w-100">
                            <a href="<?php echo isset($base_path) ? $base_path : ''; ?>announcements/index.php" 
                               class="nav-link <?php echo $current_page === 'announcements' ? 'active' : ''; ?>">
                                <i class="bi bi-megaphone me-2"></i>
                                <span class="d-none d-sm-inline">Announcements</span>
                            </a>
                        </li>
                        <li class="nav-item w-100">
                            <a href="<?php echo isset($base_path) ? $base_path : ''; ?>settings/index.php" 
                               class="nav-link <?php echo $current_page === 'settings' ? 'active' : ''; ?>">
                                <i class="bi bi-gear me-2"></i>
                                <span class="d-none d-sm-inline">Settings</span>
                            </a>
                        </li>
                    </ul>
                    <hr class="w-100">
                    <div class="dropdown pb-4">
                        <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-person-circle fs-4 me-2"></i>
                            <span class="d-none d-sm-inline"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                            <li><a class="dropdown-item" href="<?php echo isset($base_path) ? $base_path : ''; ?>profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo isset($base_path) ? $base_path : ''; ?>auth/logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col main-content py-3">
                <nav class="navbar navbar-expand-lg top-navbar mb-4 rounded-3">
                    <div class="container-fluid">
                        <h1 class="h3 mb-0"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                        <div class="ms-auto">
                            <span class="text-muted"><?php echo date('l, F j, Y'); ?></span>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
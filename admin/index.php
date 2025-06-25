<?php
session_start();
include('config/dbcon.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: auth/login.php');
    exit();
}

// Get dashboard statistics
$stats = [
    'total_farmers' => mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as count FROM farmers"))[0],
    'total_buyers' => mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as count FROM buyers"))[0],
    'total_products' => mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as count FROM products"))[0],
    'active_products' => mysqli_fetch_array(mysqli_query($con, "SELECT COUNT(*) as count FROM products WHERE status = 'active'"))[0]
];

$page_title = "Admin Dashboard";
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
        
        .stats-card {
            border: none;
            border-radius: 1rem;
            transition: transform 0.3s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .main-content {
            background-color: #f8f9fa;
        }
        
        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-auto px-0 sidebar">
                <div class="d-flex flex-column align-items-center align-items-sm-start px-3 pt-4 min-vh-100">
                    <a href="index.php" class="d-flex align-items-center pb-3 mb-md-1 text-decoration-none text-white">
                        <span class="fs-5 d-none d-sm-inline">Agri-Connect Admin</span>
                    </a>
                    <ul class="nav nav-pills flex-column mb-sm-auto mb-0 align-items-center align-items-sm-start w-100">
                        <li class="nav-item w-100">
                            <a href="index.php" class="nav-link active">
                                <i class="bi bi-speedometer2 me-2"></i>
                                <span class="d-none d-sm-inline">Dashboard</span>
                            </a>
                        </li>
                        <li class="nav-item w-100">
                            <a href="users/farmers.php" class="nav-link">
                                <i class="bi bi-person-badge me-2"></i>
                                <span class="d-none d-sm-inline">Farmers</span>
                            </a>
                        </li>
                        <li class="nav-item w-100">
                            <a href="users/buyers.php" class="nav-link">
                                <i class="bi bi-people me-2"></i>
                                <span class="d-none d-sm-inline">Buyers</span>
                            </a>
                        </li>
                        <li class="nav-item w-100">
                            <a href="products/index.php" class="nav-link">
                                <i class="bi bi-box-seam me-2"></i>
                                <span class="d-none d-sm-inline">Products</span>
                            </a>
                        </li>
                        <li class="nav-item w-100">
                            <a href="announcements/index.php" class="nav-link">
                                <i class="bi bi-megaphone me-2"></i>
                                <span class="d-none d-sm-inline">Announcements</span>
                            </a>
                        </li>
                        <li class="nav-item w-100">
                            <a href="settings/index.php" class="nav-link">
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
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="auth/logout.php">Sign out</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col main-content py-3">
                <nav class="navbar navbar-expand-lg top-navbar mb-4 rounded-3">
                    <div class="container-fluid">
                        <h1 class="h3 mb-0">Dashboard</h1>
                        <div class="ms-auto">
                            <span class="text-muted"><?php echo date('l, F j, Y'); ?></span>
                        </div>
                    </div>
                </nav>

                <!-- Statistics Cards -->
                <div class="container-fluid">
                    <div class="row g-4">
                        <div class="col-md-6 col-xl-3">
                            <div class="card stats-card bg-primary text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-1">Total Farmers</h5>
                                            <h2 class="mb-0"><?php echo number_format($stats['total_farmers']); ?></h2>
                                        </div>
                                        <div class="ms-3">
                                            <i class="bi bi-person-badge fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="card stats-card bg-success text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-1">Total Buyers</h5>
                                            <h2 class="mb-0"><?php echo number_format($stats['total_buyers']); ?></h2>
                                        </div>
                                        <div class="ms-3">
                                            <i class="bi bi-people fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="card stats-card bg-info text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-1">Total Products</h5>
                                            <h2 class="mb-0"><?php echo number_format($stats['total_products']); ?></h2>
                                        </div>
                                        <div class="ms-3">
                                            <i class="bi bi-box-seam fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-3">
                            <div class="card stats-card bg-warning text-white h-100">
                                <div class="card-body">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-grow-1">
                                            <h5 class="card-title mb-1">Active Products</h5>
                                            <h2 class="mb-0"><?php echo number_format($stats['active_products']); ?></h2>
                                        </div>
                                        <div class="ms-3">
                                            <i class="bi bi-check-circle fs-1"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity Section -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Recent Activity</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Action</th>
                                                    <th>User</th>
                                                    <th>Details</th>
                                                    <th>Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                try {
                                                    $stmt = mysqli_query($con, "
                                                        SELECT aal.*, a.username 
                                                        FROM admin_activity_log aal
                                                        LEFT JOIN admins a ON aal.admin_id = a.id
                                                        ORDER BY aal.created_at DESC
                                                        LIMIT 5
                                                    ");
                                                    while ($activity = mysqli_fetch_assoc($stmt)) {
                                                        echo "<tr>";
                                                        echo "<td>" . htmlspecialchars($activity['action']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($activity['username']) . "</td>";
                                                        echo "<td>" . htmlspecialchars($activity['details']) . "</td>";
                                                        echo "<td>" . date('M j, Y g:i A', strtotime($activity['created_at'])) . "</td>";
                                                        echo "</tr>";
                                                    }
                                                } catch (Exception $e) {
                                                    error_log($e->getMessage());
                                                    echo "<tr><td colspan='4' class='text-center'>No recent activity</td></tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Ensure user is logged in as buyer
if (!isset($_SESSION['buyer_id']) || $_SESSION['buyer_type'] !== 'buyer') {
    $_SESSION['error_message'] = 'Please login to access your dashboard.';
    header('Location: login.php');
    exit();
}

try {
    // Get buyer information
    $stmt = $pdo->prepare('SELECT * FROM buyers WHERE id = ?');
    $stmt->execute([$_SESSION['buyer_id']]);
    $buyer = $stmt->fetch();

    // Get saved products count
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM saved_products WHERE buyer_id = ?');
    $stmt->execute([$_SESSION['buyer_id']]);
    $saved_products_count = $stmt->fetchColumn();

    // Get pending inquiries count
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM product_inquiries WHERE buyer_id = ? AND status = "pending"');
    $stmt->execute([$_SESSION['buyer_id']]);
    $pending_inquiries_count = $stmt->fetchColumn();

    // Get unread notifications count
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM buyer_notifications WHERE buyer_id = ? AND is_read = 0');
    $stmt->execute([$_SESSION['buyer_id']]);
    $unread_notifications_count = $stmt->fetchColumn();

    // Get active price alerts count
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM price_alerts WHERE buyer_id = ? AND status = "active"');
    $stmt->execute([$_SESSION['buyer_id']]);
    $active_alerts_count = $stmt->fetchColumn();

    // Get recent notifications
    $stmt = $pdo->prepare('SELECT * FROM buyer_notifications WHERE buyer_id = ? ORDER BY created_at DESC LIMIT 5');
    $stmt->execute([$_SESSION['buyer_id']]);
    $recent_notifications = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $_SESSION['error_message'] = 'An error occurred while loading your dashboard.';
    header('Location: ../index.php');
    exit();
}

$page_title = 'Buyer Dashboard - Agri-Connect';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            height: 100%;
            transition: transform 0.2s;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
        .stats-icon {
            font-size: 2rem;
            color: #198754;
            margin-bottom: 1rem;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #198754;
        }
        .notification-item {
            padding: 1rem;
            border-bottom: 1px solid #eee;
            transition: background-color 0.2s;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }
        .action-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            text-decoration: none;
            color: inherit;
            transition: all 0.2s;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .action-card:hover {
            transform: translateY(-5px);
            color: #198754;
        }
        .action-icon {
            font-size: 2.5rem;
            color: #198754;
            margin-bottom: 1rem;
        }
        .welcome-section {
            background: linear-gradient(45deg, #198754, #20c997);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>

    <div class="dashboard-container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="h3">Welcome back, <?php echo htmlspecialchars($buyer['name']); ?>! 👋</h1>
            <p class="mb-0">Here's what's happening with your account today</p>
        </div>

        <!-- Stats Grid -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="bi bi-heart-fill stats-icon"></i>
                    <div class="stats-number"><?php echo $saved_products_count; ?></div>
                    <div class="text-muted">Saved Products</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="bi bi-chat-dots-fill stats-icon"></i>
                    <div class="stats-number"><?php echo $pending_inquiries_count; ?></div>
                    <div class="text-muted">Pending Inquiries</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="bi bi-bell-fill stats-icon"></i>
                    <div class="stats-number"><?php echo $unread_notifications_count; ?></div>
                    <div class="text-muted">Unread Notifications</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <i class="bi bi-graph-up stats-icon"></i>
                    <div class="stats-number"><?php echo $active_alerts_count; ?></div>
                    <div class="text-muted">Active Price Alerts</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <h2 class="h4 mb-4">Quick Actions</h2>
        <div class="quick-actions">
            <a href="profile.php" class="action-card">
                <i class="bi bi-person-circle action-icon"></i>
                <h3 class="h5">My Profile</h3>
                <p class="text-muted mb-0">Update your personal information</p>
            </a>
            <a href="saved-products.php" class="action-card">
                <i class="bi bi-heart action-icon"></i>
                <h3 class="h5">Saved Products</h3>
                <p class="text-muted mb-0">View your wishlist</p>
            </a>
            <a href="inquiries.php" class="action-card">
                <i class="bi bi-chat-dots action-icon"></i>
                <h3 class="h5">My Inquiries</h3>
                <p class="text-muted mb-0">Manage your product inquiries</p>
            </a>
            <a href="price-alerts.php" class="action-card">
                <i class="bi bi-bell action-icon"></i>
                <h3 class="h5">Price Alerts</h3>
                <p class="text-muted mb-0">Manage your price alerts</p>
            </a>
        </div>

        <!-- Recent Notifications -->
        <div class="row mt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h2 class="h5 mb-0">Recent Notifications</h2>
                            <a href="notifications.php" class="btn btn-sm btn-outline-success">View All</a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recent_notifications)): ?>
                            <div class="p-4 text-center text-muted">
                                <i class="bi bi-bell-slash fs-1"></i>
                                <p class="mb-0">No new notifications</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($recent_notifications as $notification): ?>
                                <div class="notification-item">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <?php
                                            $icon_class = match($notification['type']) {
                                                'inquiry_response' => 'bi-chat-dots text-primary',
                                                'price_alert' => 'bi-graph-up text-success',
                                                'product_update' => 'bi-bag text-warning',
                                                default => 'bi-info-circle text-info'
                                            };
                                            ?>
                                            <i class="bi <?php echo $icon_class; ?> fs-4"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <h6 class="mb-1"><?php echo htmlspecialchars($notification['title']); ?></h6>
                                            <p class="mb-1 text-muted"><?php echo htmlspecialchars($notification['message']); ?></p>
                                            <small class="text-muted">
                                                <?php echo timeAgo($notification['created_at']); ?>
                                            </small>
                                        </div>
                                        <?php if (!$notification['is_read']): ?>
                                            <span class="badge bg-success rounded-pill">New</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h2 class="h5 mb-0">Account Overview</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted">Email</small>
                            <div><?php echo htmlspecialchars($buyer['email']); ?></div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Phone</small>
                            <div><?php echo htmlspecialchars($buyer['phone']); ?></div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Address</small>
                            <div><?php echo htmlspecialchars($buyer['address'] ?: 'Not set'); ?></div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Member Since</small>
                            <div><?php echo date('F j, Y', strtotime($buyer['created_at'])); ?></div>
                        </div>
                        <div class="d-grid">
                            <a href="profile.php" class="btn btn-outline-success">
                                <i class="bi bi-pencil"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
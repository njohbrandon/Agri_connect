<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Ensure user is logged in as buyer
if (!isset($_SESSION['buyer_id']) || $_SESSION['buyer_type'] !== 'buyer') {
    $_SESSION['error_message'] = 'Please login to access your notifications.';
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_all_read'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE buyer_notifications SET is_read = 1 WHERE buyer_id = ?');
            $stmt->execute([$_SESSION['buyer_id']]);
            $success = 'All notifications marked as read.';
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'Failed to mark notifications as read.';
        }
    }
}

// Handle delete notification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_notification'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $notification_id = filter_var($_POST['notification_id'] ?? 0, FILTER_VALIDATE_INT);
        
        if ($notification_id) {
            try {
                $stmt = $pdo->prepare('DELETE FROM buyer_notifications WHERE id = ? AND buyer_id = ?');
                $stmt->execute([$notification_id, $_SESSION['buyer_id']]);
                $success = 'Notification deleted successfully.';
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Failed to delete notification.';
            }
        }
    }
}

// Handle mark as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $notification_id = filter_var($_POST['notification_id'] ?? 0, FILTER_VALIDATE_INT);
        
        if ($notification_id) {
            try {
                $stmt = $pdo->prepare('UPDATE buyer_notifications SET is_read = 1 WHERE id = ? AND buyer_id = ?');
                $stmt->execute([$notification_id, $_SESSION['buyer_id']]);
                $success = 'Notification marked as read.';
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Failed to mark notification as read.';
            }
        }
    }
}

try {
    // Get all notifications
    $stmt = $pdo->prepare('SELECT * FROM buyer_notifications WHERE buyer_id = ? ORDER BY created_at DESC');
    $stmt->execute([$_SESSION['buyer_id']]);
    $notifications = $stmt->fetchAll();

    // Get unread count
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM buyer_notifications WHERE buyer_id = ? AND is_read = 0');
    $stmt->execute([$_SESSION['buyer_id']]);
    $unread_count = $stmt->fetchColumn();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while loading your notifications.';
}

$page_title = 'Notifications - Agri-Connect';
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
        .notifications-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .notifications-header {
            background: linear-gradient(45deg, #198754, #20c997);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .notification-item {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: transform 0.2s;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }
        .notification-item:hover {
            transform: translateY(-2px);
        }
        .notification-item.unread {
            border-left: 4px solid #198754;
        }
        .notification-item.unread::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(25, 135, 84, 0.05);
            pointer-events: none;
        }
        .notification-type {
            font-size: 0.8rem;
            text-transform: uppercase;
            font-weight: 500;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            display: inline-block;
            margin-bottom: 0.5rem;
        }
        .type-inquiry_response {
            background: #cfe2ff;
            color: #084298;
        }
        .type-price_alert {
            background: #d1e7dd;
            color: #0f5132;
        }
        .type-product_update {
            background: #fff3cd;
            color: #856404;
        }
        .type-system {
            background: #e2e3e5;
            color: #383d41;
        }
        .notification-time {
            font-size: 0.85rem;
            color: #6c757d;
        }
        .empty-notifications {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .empty-notifications i {
            font-size: 4rem;
            color: #198754;
            margin-bottom: 1rem;
        }
        .action-buttons {
            position: absolute;
            top: 1rem;
            right: 1rem;
            display: flex;
            gap: 0.5rem;
            opacity: 0;
            transition: opacity 0.2s;
        }
        .notification-item:hover .action-buttons {
            opacity: 1;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>

    <div class="notifications-container">
        <div class="notifications-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">Notifications</h1>
                    <p class="mb-0">You have <?php echo $unread_count; ?> unread notifications</p>
                </div>
                <?php if ($unread_count > 0): ?>
                    <form method="POST" class="d-inline">
                        <?php csrfField(); ?>
                        <button type="submit" name="mark_all_read" class="btn btn-outline-light">
                            <i class="bi bi-check-all"></i> Mark All as Read
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill"></i> <?php echo $success; ?>
            </div>
        <?php endif; ?>

        <?php if (empty($notifications)): ?>
            <div class="empty-notifications">
                <i class="bi bi-bell-slash"></i>
                <h2 class="h4">No notifications</h2>
                <p class="text-muted mb-4">You're all caught up!</p>
                <a href="../products.php" class="btn btn-success">
                    <i class="bi bi-shop"></i> Browse Products
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                    <?php
                    $type_class = match($notification['type']) {
                        'inquiry_response' => 'type-inquiry_response',
                        'price_alert' => 'type-price_alert',
                        'product_update' => 'type-product_update',
                        default => 'type-system'
                    };
                    ?>
                    <span class="notification-type <?php echo $type_class; ?>">
                        <?php echo str_replace('_', ' ', $notification['type']); ?>
                    </span>

                    <h2 class="h5 mb-2"><?php echo htmlspecialchars($notification['title']); ?></h2>
                    <p class="mb-2"><?php echo htmlspecialchars($notification['message']); ?></p>
                    <div class="notification-time">
                        <?php echo timeAgo($notification['created_at']); ?>
                    </div>

                    <div class="action-buttons">
                        <?php if (!$notification['is_read']): ?>
                            <form method="POST" class="d-inline">
                                <?php csrfField(); ?>
                                <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                                <button type="submit" name="mark_read" class="btn btn-sm btn-success" title="Mark as Read">
                                    <i class="bi bi-check"></i>
                                </button>
                            </form>
                        <?php endif; ?>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Delete this notification?');">
                            <?php csrfField(); ?>
                            <input type="hidden" name="notification_id" value="<?php echo $notification['id']; ?>">
                            <button type="submit" name="delete_notification" class="btn btn-sm btn-outline-danger" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
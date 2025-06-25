<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Ensure user is logged in as buyer
if (!isset($_SESSION['buyer_id']) || $_SESSION['buyer_type'] !== 'buyer') {
    $_SESSION['error_message'] = 'Please login to access your inquiries.';
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

// Handle delete inquiry
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_inquiry'])) {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $inquiry_id = filter_var($_POST['inquiry_id'] ?? 0, FILTER_VALIDATE_INT);
        
        if ($inquiry_id) {
            try {
                $stmt = $pdo->prepare('DELETE FROM product_inquiries WHERE id = ? AND buyer_id = ?');
                $stmt->execute([$inquiry_id, $_SESSION['buyer_id']]);
                $success = 'Inquiry deleted successfully.';
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'Failed to delete inquiry.';
            }
        }
    }
}

try {
    // Get all inquiries with product and farmer information
    $stmt = $pdo->prepare('
        SELECT pi.*, p.name as product_name, p.image as product_image, 
               p.price, p.unit, f.name as farmer_name, f.phone as farmer_phone
        FROM product_inquiries pi
        JOIN products p ON pi.product_id = p.id
        JOIN farmers f ON p.farmer_id = f.id
        WHERE pi.buyer_id = ?
        ORDER BY pi.created_at DESC
    ');
    $stmt->execute([$_SESSION['buyer_id']]);
    $inquiries = $stmt->fetchAll();

} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while loading your inquiries.';
}

$page_title = 'My Inquiries - Agri-Connect';
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
        .inquiries-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .inquiries-header {
            background: linear-gradient(45deg, #198754, #20c997);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        .inquiry-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .inquiry-header {
            padding: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        .inquiry-body {
            padding: 1.5rem;
        }
        .inquiry-footer {
            padding: 1.5rem;
            background: #f8f9fa;
            border-top: 1px solid #eee;
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 25px;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-responded {
            background: #d4edda;
            color: #155724;
        }
        .status-closed {
            background: #e2e3e5;
            color: #383d41;
        }
        .whatsapp-btn {
            background: #25D366;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.2s;
        }
        .whatsapp-btn:hover {
            background: #128C7E;
            color: white;
            transform: scale(1.05);
        }
        .empty-inquiries {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        .empty-inquiries i {
            font-size: 4rem;
            color: #198754;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>

    <div class="inquiries-container">
        <div class="inquiries-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">My Product Inquiries</h1>
                    <p class="mb-0">Track your conversations with farmers</p>
                </div>
                <a href="../products.php" class="btn btn-outline-light">
                    <i class="bi bi-shop"></i> Browse Products
                </a>
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

        <?php if (empty($inquiries)): ?>
            <div class="empty-inquiries">
                <i class="bi bi-chat-dots"></i>
                <h2 class="h4">No inquiries yet</h2>
                <p class="text-muted mb-4">Start browsing products and send inquiries to farmers!</p>
                <a href="../products.php" class="btn btn-success">
                    <i class="bi bi-shop"></i> Browse Products
                </a>
            </div>
        <?php else: ?>
            <?php foreach ($inquiries as $inquiry): ?>
                <div class="inquiry-card">
                    <div class="inquiry-header">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <img src="<?php echo $inquiry['product_image'] ? '../uploads/products/' . htmlspecialchars($inquiry['product_image']) : '../assets/images/default-product.jpg'; ?>" 
                                     alt="<?php echo htmlspecialchars($inquiry['product_name']); ?>" 
                                     class="product-image">
                            </div>
                            <div class="col">
                                <h2 class="h5 mb-1"><?php echo htmlspecialchars($inquiry['product_name']); ?></h2>
                                <div class="text-muted">
                                    ₱<?php echo number_format($inquiry['price'], 2); ?>/<?php echo htmlspecialchars($inquiry['unit']); ?>
                                </div>
                            </div>
                            <div class="col-auto">
                                <?php
                                $status_class = match($inquiry['status']) {
                                    'pending' => 'status-pending',
                                    'responded' => 'status-responded',
                                    'closed' => 'status-closed'
                                };
                                ?>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo ucfirst($inquiry['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="inquiry-body">
                        <div class="mb-4">
                            <h3 class="h6 text-muted mb-2">Your Message:</h3>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($inquiry['message'])); ?></p>
                        </div>

                        <?php if ($inquiry['quantity']): ?>
                            <div class="mb-2">
                                <strong>Quantity Requested:</strong> 
                                <?php echo $inquiry['quantity'] . ' ' . htmlspecialchars($inquiry['unit']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($inquiry['preferred_delivery_date']): ?>
                            <div class="mb-2">
                                <strong>Preferred Delivery:</strong> 
                                <?php echo date('F j, Y', strtotime($inquiry['preferred_delivery_date'])); ?>
                            </div>
                        <?php endif; ?>

                        <div class="mb-2">
                            <strong>Farmer:</strong> <?php echo htmlspecialchars($inquiry['farmer_name']); ?>
                        </div>
                        <div>
                            <strong>Contact:</strong> <?php echo htmlspecialchars($inquiry['farmer_phone']); ?>
                        </div>
                    </div>
                    <div class="inquiry-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Sent <?php echo timeAgo($inquiry['created_at']); ?>
                            </small>
                            <div class="d-flex gap-2">
                                <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $inquiry['farmer_phone']); ?>?text=Hi, regarding my inquiry about: <?php echo urlencode($inquiry['product_name']); ?>" 
                                   target="_blank" class="whatsapp-btn">
                                    <i class="bi bi-whatsapp"></i> Contact Farmer
                                </a>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Delete this inquiry?');">
                                    <?php csrfField(); ?>
                                    <input type="hidden" name="inquiry_id" value="<?php echo $inquiry['id']; ?>">
                                    <button type="submit" name="delete_inquiry" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
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
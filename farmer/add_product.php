<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Require login for this page
requireLogin();

$error = '';
$success = '';

// Get categories for dropdown
try {
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY name');
    $categories = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while fetching categories.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Enable error reporting for debugging
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    // Validate and sanitize input
    $name = htmlspecialchars(trim($_POST['name'] ?? ''));
    $category_id = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $unit = htmlspecialchars(trim($_POST['unit'] ?? ''));
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $status = htmlspecialchars(trim($_POST['status'] ?? ''));

    // Debug log
    error_log('POST data received: ' . print_r($_POST, true));

    // Validate required fields
    if (empty($name) || empty($category_id) || empty($description) || 
        empty($price) || empty($unit) || empty($quantity)) {
        $error = 'Please fill in all required fields.';
        error_log('Validation failed: Missing required fields');
    } else {
        try {
            // Verify farmer_id exists in session
            if (!isset($_SESSION['farmer_id'])) {
                throw new Exception('Session expired. Please login again.');
            }

            // Debug log
            error_log('Farmer ID from session: ' . $_SESSION['farmer_id']);

            // Verify category exists
            $stmt = $pdo->prepare('SELECT id FROM categories WHERE id = ?');
            $stmt->execute([$category_id]);
            if (!$stmt->fetch()) {
                throw new Exception('Invalid category selected.');
            }

            // Start transaction
            $pdo->beginTransaction();

            // Get category name for the category field
            $stmt = $pdo->prepare('SELECT name FROM categories WHERE id = ?');
            $stmt->execute([$category_id]);
            $category = $stmt->fetch()['name'];

            // Insert product
            $stmt = $pdo->prepare('INSERT INTO products (
                farmer_id, category, name, description, price, 
                unit, quantity, status, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())');

            $params = [
                $_SESSION['farmer_id'],
                $category,
                $name,
                $description,
                $price,
                $unit,
                $quantity,
                $status ?? 'active'
            ];

            // Debug log
            error_log('Executing product insert with params: ' . print_r($params, true));

            $stmt->execute($params);
            $product_id = $pdo->lastInsertId();

            error_log('Product inserted successfully with ID: ' . $product_id);

            // Handle image upload if present
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 5 * 1024 * 1024; // 5MB

                // Debug log
                error_log('Processing image upload: ' . print_r($file, true));

                if (!in_array($file['type'], $allowed_types)) {
                    throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
                }

                if ($file['size'] > $max_size) {
                    throw new Exception('File size too large. Maximum size is 5MB.');
                }

                // Generate unique filename
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                
                // Use absolute path for XAMPP
                $upload_path = $_SERVER['DOCUMENT_ROOT'] . '/farmers_market_place/uploads/products/';
                
                error_log('Upload path: ' . $upload_path);

                // Create upload directory if it doesn't exist
                if (!file_exists($upload_path)) {
                    error_log('Creating upload directory: ' . $upload_path);
                    if (!mkdir($upload_path, 0777, true)) {
                        throw new Exception('Failed to create upload directory. Path: ' . $upload_path);
                    }
                }

                // Ensure directory is writable
                if (!is_writable($upload_path)) {
                    error_log('Upload directory is not writable: ' . $upload_path);
                    throw new Exception('Upload directory is not writable. Please check permissions.');
                }

                $target_file = $upload_path . $filename;
                error_log('Attempting to move uploaded file to: ' . $target_file);

                // Move uploaded file
                if (!move_uploaded_file($file['tmp_name'], $target_file)) {
                    $upload_error = error_get_last();
                    throw new Exception('Failed to upload image. Error: ' . ($upload_error ? $upload_error['message'] : 'Unknown error'));
                }

                error_log('File uploaded successfully to: ' . $target_file);

                // Update product with image filename
                $stmt = $pdo->prepare('UPDATE products SET image = ? WHERE id = ?');
                $stmt->execute([$filename, $product_id]);
            }

            // Commit transaction
            $pdo->commit();
            error_log('Transaction committed successfully');

            // Set success message and redirect
            $_SESSION['success_message'] = 'Product added successfully.';
            header('Location: products.php');
            exit();

        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            error_log('Error adding product: ' . $e->getMessage());
            error_log('Stack trace: ' . $e->getTraceAsString());
            $error = 'Error: ' . $e->getMessage();
        }
    }
}

$page_title = 'Add New Product';
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
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/header.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="h3 text-center mb-4">
                            <i class="bi bi-plus-circle text-success"></i>
                            Add New Product
                        </h1>

                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" required
                                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                                    <div class="invalid-feedback">
                                        Please enter a product name.
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="category_id" class="form-label">Category *</label>
                                    <select class="form-select" id="category_id" name="category_id" required>
                                        <option value="">Select a category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>"
                                                    <?php echo isset($_POST['category_id']) && $_POST['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a category.
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="description" class="form-label">Description *</label>
                                    <textarea class="form-control" id="description" name="description" rows="4" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                                    <div class="invalid-feedback">
                                        Please enter a description.
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="price" class="form-label">Price *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="number" class="form-control" id="price" name="price" 
                                               step="0.01" min="0" required
                                               value="<?php echo isset($_POST['price']) ? htmlspecialchars($_POST['price']) : ''; ?>">
                                    </div>
                                    <div class="invalid-feedback">
                                        Please enter a valid price.
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="unit" class="form-label">Unit *</label>
                                    <select class="form-select" id="unit" name="unit" required>
                                        <option value="">Select unit</option>
                                        <option value="kg" <?php echo isset($_POST['unit']) && $_POST['unit'] === 'kg' ? 'selected' : ''; ?>>Kilogram (kg)</option>
                                        <option value="g" <?php echo isset($_POST['unit']) && $_POST['unit'] === 'g' ? 'selected' : ''; ?>>Gram (g)</option>
                                        <option value="piece" <?php echo isset($_POST['unit']) && $_POST['unit'] === 'piece' ? 'selected' : ''; ?>>Piece</option>
                                        <option value="dozen" <?php echo isset($_POST['unit']) && $_POST['unit'] === 'dozen' ? 'selected' : ''; ?>>Dozen</option>
                                        <option value="bundle" <?php echo isset($_POST['unit']) && $_POST['unit'] === 'bundle' ? 'selected' : ''; ?>>Bundle</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a unit.
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="quantity" class="form-label">Quantity *</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                           min="1" required
                                           value="<?php echo isset($_POST['quantity']) ? htmlspecialchars($_POST['quantity']) : ''; ?>">
                                    <div class="invalid-feedback">
                                        Please enter a valid quantity.
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="image" class="form-label">Product Image</label>
                                    <input type="file" class="form-control" id="image" name="image" 
                                           accept="image/jpeg,image/png,image/gif">
                                    <div class="form-text">
                                        Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?php echo isset($_POST['status']) && $_POST['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo isset($_POST['status']) && $_POST['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <hr class="my-4">
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="products.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle"></i> Add Product
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include '../includes/footer.php'; ?>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        (function () {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Preview image before upload
        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                if (file.size > 5 * 1024 * 1024) {
                    alert('File size too large. Maximum size is 5MB.');
                    this.value = '';
                    return;
                }
                
                if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
                    alert('Invalid file type. Only JPG, PNG and GIF are allowed.');
                    this.value = '';
                    return;
                }
            }
        });
    </script>
</body>
</html> 
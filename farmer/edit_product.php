<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Require login for this page
requireLogin();

$error = '';
$success = '';

// Get product ID from URL
$product_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$product_id) {
    header('Location: products.php');
    exit();
}

// Get categories for dropdown
try {
    $stmt = $pdo->query('SELECT * FROM categories ORDER BY name');
    $categories = $stmt->fetchAll();

    // Get product details
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = ? AND farmer_id = ?');
    $stmt->execute([$product_id, $_SESSION['farmer_id']]);
    $product = $stmt->fetch();

    if (!$product) {
        header('Location: products.php');
        exit();
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while fetching data.';
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
    error_log('Sanitized data:');
    error_log('name: ' . $name);
    error_log('category_id: ' . $category_id);
    error_log('description: ' . $description);
    error_log('price: ' . $price);
    error_log('unit: ' . $unit);
    error_log('quantity: ' . $quantity);
    error_log('status: ' . $status);

    // Validate required fields with detailed error message
    $missing_fields = [];
    if (empty($name)) $missing_fields[] = 'Product Name';
    if (empty($category_id)) $missing_fields[] = 'Category';
    if (empty($description)) $missing_fields[] = 'Description';
    if ($price === false || $price === null) $missing_fields[] = 'Price';
    if (empty($unit)) $missing_fields[] = 'Unit';
    if ($quantity === false || $quantity === null) $missing_fields[] = 'Quantity';

    if (!empty($missing_fields)) {
        $error = 'Please fill in the following required fields: ' . implode(', ', $missing_fields);
        error_log('Validation failed. Missing fields: ' . implode(', ', $missing_fields));
    } else {
        try {
            // Start transaction
            $pdo->beginTransaction();

            // Get category name from category_id
            $stmt = $pdo->prepare('SELECT name FROM categories WHERE id = ?');
            $stmt->execute([$category_id]);
            $category_result = $stmt->fetch();
            
            if (!$category_result) {
                throw new Exception('Invalid category selected.');
            }
            
            $category = $category_result['name'];

            // Update product
            $stmt = $pdo->prepare('UPDATE products SET 
                category = ?, name = ?, description = ?, price = ?, 
                unit = ?, quantity = ?, status = ?, updated_at = NOW()
                WHERE id = ? AND farmer_id = ?');

            $params = [
                $category,
                $name,
                $description,
                $price,
                $unit,
                $quantity,
                $status ?? 'active',
                $product_id,
                $_SESSION['farmer_id']
            ];

            // Debug log
            error_log('Executing update with params: ' . print_r($params, true));

            $stmt->execute($params);

            // Handle image upload if present
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['image'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                $max_size = 5 * 1024 * 1024; // 5MB

                if (!in_array($file['type'], $allowed_types)) {
                    throw new Exception('Invalid file type. Only JPG, PNG and GIF are allowed.');
                }

                if ($file['size'] > $max_size) {
                    throw new Exception('File size too large. Maximum size is 5MB.');
                }

                // Generate unique filename
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $upload_path = '../uploads/products/';

                // Create upload directory if it doesn't exist
                if (!file_exists($upload_path)) {
                    mkdir($upload_path, 0777, true);
                }

                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $upload_path . $filename)) {
                    // Delete old image if exists
                    if (!empty($product['image'])) {
                        $old_image = $upload_path . $product['image'];
                        if (file_exists($old_image)) {
                            unlink($old_image);
                        }
                    }

                    // Update product with new image filename
                    $stmt = $pdo->prepare('UPDATE products SET image = ? WHERE id = ?');
                    $stmt->execute([$filename, $product_id]);
                } else {
                    throw new Exception('Failed to upload image.');
                }
            }

            // Commit transaction
            $pdo->commit();

            // Set success message and redirect
            $_SESSION['success_message'] = 'Product updated successfully.';
            header('Location: products.php');
            exit();

        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            error_log($e->getMessage());
            $error = 'An error occurred while updating the product. Please try again.';
        }
    }
}

$page_title = 'Edit Product';
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
                            <i class="bi bi-pencil text-success"></i>
                            Edit Product
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
                                           value="<?php echo htmlspecialchars($product['name']); ?>">
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
                                                    <?php echo $product['category'] === $category['name'] ? 'selected' : ''; ?>>
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
                                    <textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                    <div class="invalid-feedback">
                                        Please enter a description.
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="price" class="form-label">Price (XAF) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">XAF</span>
                                        <input type="number" class="form-control" id="price" name="price" 
                                               step="100" min="0" required
                                               value="<?php echo htmlspecialchars($product['price']); ?>">
                                    </div>
                                    <div class="invalid-feedback">
                                        Please enter a valid price.
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="unit" class="form-label">Unit *</label>
                                    <select class="form-select" id="unit" name="unit" required>
                                        <option value="">Select unit</option>
                                        <option value="kg" <?php echo $product['unit'] === 'kg' ? 'selected' : ''; ?>>Kilogram (kg)</option>
                                        <option value="g" <?php echo $product['unit'] === 'g' ? 'selected' : ''; ?>>Gram (g)</option>
                                        <option value="piece" <?php echo $product['unit'] === 'piece' ? 'selected' : ''; ?>>Piece</option>
                                        <option value="dozen" <?php echo $product['unit'] === 'dozen' ? 'selected' : ''; ?>>Dozen</option>
                                        <option value="bundle" <?php echo $product['unit'] === 'bundle' ? 'selected' : ''; ?>>Bundle</option>
                                    </select>
                                    <div class="invalid-feedback">
                                        Please select a unit.
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <label for="quantity" class="form-label">Quantity *</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                           min="1" required
                                           value="<?php echo htmlspecialchars($product['quantity']); ?>">
                                    <div class="invalid-feedback">
                                        Please enter a valid quantity.
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="image" class="form-label">Product Image</label>
                                    <?php if (!empty($product['image'])): ?>
                                        <div class="mb-2">
                                            <img src="<?php echo '../uploads/products/' . $product['image']; ?>" 
                                                 alt="Current product image" class="img-thumbnail" style="max-height: 100px;">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" id="image" name="image" 
                                           accept="image/jpeg,image/png,image/gif">
                                    <div class="form-text">
                                        Maximum file size: 5MB. Allowed formats: JPG, PNG, GIF
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label for="status" class="form-label">Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="active" <?php echo $product['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $product['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <hr class="my-4">
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="products.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Cancel
                                        </a>
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle"></i> Update Product
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
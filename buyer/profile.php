<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Ensure user is logged in as buyer
if (!isset($_SESSION['buyer_id']) || $_SESSION['buyer_type'] !== 'buyer') {
    $_SESSION['error_message'] = 'Please login to access your profile.';
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

try {
    // Get buyer information
    $stmt = $pdo->prepare('SELECT * FROM buyers WHERE id = ?');
    $stmt->execute([$_SESSION['buyer_id']]);
    $buyer = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify CSRF token
        if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
            $error = 'Invalid request. Please try again.';
        } else {
            $name = sanitizeInput($_POST['name'] ?? '');
            $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $phone = sanitizeInput($_POST['phone'] ?? '');
            $address = sanitizeInput($_POST['address'] ?? '');
            $current_password = $_POST['current_password'] ?? '';
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            // Basic validation
            if (empty($name) || empty($email) || empty($phone)) {
                $error = 'Name, email, and phone are required.';
            } else {
                // Check if email exists (excluding current user)
                $stmt = $pdo->prepare('SELECT id FROM buyers WHERE email = ? AND id != ?');
                $stmt->execute([$email, $_SESSION['buyer_id']]);
                if ($stmt->fetch()) {
                    $error = 'This email is already registered.';
                }

                // Check if phone exists (excluding current user)
                $stmt = $pdo->prepare('SELECT id FROM buyers WHERE phone = ? AND id != ?');
                $stmt->execute([$phone, $_SESSION['buyer_id']]);
                if ($stmt->fetch()) {
                    $error = 'This phone number is already registered.';
                }

                if (empty($error)) {
                    // Handle profile image upload
                    $profile_image = $buyer['profile_image']; // Keep existing image by default
                    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
                        $file_type = $_FILES['profile_image']['type'];
                        
                        if (!in_array($file_type, $allowed_types)) {
                            $error = 'Only JPG, PNG, and GIF images are allowed.';
                        } else {
                            $file_name = uniqid('profile_') . '_' . basename($_FILES['profile_image']['name']);
                            $upload_path = '../uploads/profiles/' . $file_name;
                            
                            // Create directory if it doesn't exist
                            if (!file_exists('../uploads/profiles')) {
                                mkdir('../uploads/profiles', 0777, true);
                            }

                            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                                // Delete old profile image if exists
                                if ($buyer['profile_image'] && file_exists('../uploads/profiles/' . $buyer['profile_image'])) {
                                    unlink('../uploads/profiles/' . $buyer['profile_image']);
                                }
                                $profile_image = $file_name;
                            } else {
                                $error = 'Failed to upload profile image.';
                            }
                        }
                    }

                    if (empty($error)) {
                        // Start transaction
                        $pdo->beginTransaction();

                        try {
                            // Update basic information
                            $stmt = $pdo->prepare('UPDATE buyers SET name = ?, email = ?, phone = ?, address = ?, profile_image = ? WHERE id = ?');
                            $stmt->execute([$name, $email, $phone, $address, $profile_image, $_SESSION['buyer_id']]);

                            // Handle password change if requested
                            if (!empty($current_password) && !empty($new_password)) {
                                if (!password_verify($current_password, $buyer['password'])) {
                                    throw new Exception('Current password is incorrect.');
                                }
                                if ($new_password !== $confirm_password) {
                                    throw new Exception('New passwords do not match.');
                                }
                                if (strlen($new_password) < 8) {
                                    throw new Exception('New password must be at least 8 characters long.');
                                }

                                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                                $stmt = $pdo->prepare('UPDATE buyers SET password = ? WHERE id = ?');
                                $stmt->execute([$hashed_password, $_SESSION['buyer_id']]);
                            }

                            $pdo->commit();
                            $_SESSION['buyer_name'] = $name;
                            $success = 'Profile updated successfully.';

                            // Refresh buyer data
                            $stmt = $pdo->prepare('SELECT * FROM buyers WHERE id = ?');
                            $stmt->execute([$_SESSION['buyer_id']]);
                            $buyer = $stmt->fetch();
                        } catch (Exception $e) {
                            $pdo->rollBack();
                            $error = $e->getMessage();
                        }
                    }
                }
            }
        }
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $error = 'An error occurred while processing your request.';
}

$page_title = 'Edit Profile - Agri-Connect';
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
        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        .profile-header {
            background: linear-gradient(45deg, #198754, #20c997);
            color: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        .profile-image-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 1rem;
        }
        .profile-image {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .profile-image-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background: white;
            border-radius: 50%;
            padding: 0.5rem;
            cursor: pointer;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .profile-image-upload:hover {
            transform: scale(1.1);
        }
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 0 20px rgba(0,0,0,0.05);
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 4;
        }
        .password-toggle:hover {
            color: #198754;
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-image-container">
                <img src="<?php echo $buyer['profile_image'] ? '../uploads/profiles/' . htmlspecialchars($buyer['profile_image']) : '../assets/images/default-profile.png'; ?>" 
                     alt="Profile" class="profile-image" id="profileImagePreview">
                <label for="profile_image" class="profile-image-upload">
                    <i class="bi bi-camera-fill text-success"></i>
                </label>
            </div>
            <h1 class="h3"><?php echo htmlspecialchars($buyer['name']); ?></h1>
            <p class="mb-0">Member since <?php echo date('F j, Y', strtotime($buyer['created_at'])); ?></p>
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

        <div class="profile-card">
            <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                <?php csrfField(); ?>
                
                <input type="file" id="profile_image" name="profile_image" class="d-none" accept="image/*">

                <h2 class="h4 mb-4">Personal Information</h2>
                
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="name" name="name" 
                           placeholder="Your Name" required
                           value="<?php echo htmlspecialchars($buyer['name']); ?>">
                    <label for="name">Full Name *</label>
                    <div class="invalid-feedback">Please enter your name.</div>
                </div>

                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="name@example.com" required
                           value="<?php echo htmlspecialchars($buyer['email']); ?>">
                    <label for="email">Email Address *</label>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>

                <div class="form-floating mb-3">
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           placeholder="Phone Number" required pattern="[0-9]{9}"
                           value="<?php echo htmlspecialchars($buyer['phone']); ?>">
                    <label for="phone">Phone Number *</label>
                    <div class="invalid-feedback">Please enter a valid phone number.</div>
                </div>

                <div class="form-floating mb-4">
                    <textarea class="form-control" id="address" name="address" 
                              placeholder="Your Address" style="height: 100px"><?php echo htmlspecialchars($buyer['address']); ?></textarea>
                    <label for="address">Delivery Address</label>
                </div>

                <h2 class="h4 mb-4">Change Password</h2>
                <p class="text-muted mb-4">Leave these fields empty if you don't want to change your password.</p>

                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control" id="current_password" name="current_password" 
                           placeholder="Current Password">
                    <label for="current_password">Current Password</label>
                    <i class="bi bi-eye-slash password-toggle" data-target="current_password"></i>
                </div>

                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control" id="new_password" name="new_password" 
                           placeholder="New Password" minlength="8">
                    <label for="new_password">New Password</label>
                    <i class="bi bi-eye-slash password-toggle" data-target="new_password"></i>
                    <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                </div>

                <div class="form-floating mb-4 position-relative">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm New Password">
                    <label for="confirm_password">Confirm New Password</label>
                    <i class="bi bi-eye-slash password-toggle" data-target="confirm_password"></i>
                    <div class="invalid-feedback">Passwords do not match.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Save Changes
                    </button>
                    <a href="dashboard.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </form>
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

        // Password toggles
        document.querySelectorAll('.password-toggle').forEach(toggle => {
            toggle.addEventListener('click', function() {
                const targetId = this.dataset.target;
                const input = document.getElementById(targetId);
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        });

        // Password confirmation validation
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        function validatePassword() {
            if (confirmPassword.value !== newPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }

        newPassword.addEventListener('change', validatePassword);
        confirmPassword.addEventListener('keyup', validatePassword);

        // Profile image preview
        const profileImage = document.getElementById('profile_image');
        const imagePreview = document.getElementById('profileImagePreview');

        profileImage.addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    imagePreview.src = e.target.result;
                };
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Phone number formatting
        const phoneInput = document.getElementById('phone');
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 9) {
                value = value.substr(0, 9);
            }
            e.target.value = value;
        });
    </script>
</body>
</html> 
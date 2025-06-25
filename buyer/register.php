<?php
require_once '../config/database.php';
require_once '../includes/functions.php';
require_once '../includes/session.php';

// Redirect if already logged in
if (isset($_SESSION['buyer_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $address = sanitizeInput($_POST['address'] ?? '');

        // Validation
        if (empty($name) || empty($phone) || empty($password) || empty($email)) {
            $error = 'Name, phone number, email, and password are required.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($password !== $confirm_password) {
            $error = 'Passwords do not match.';
        } else {
            try {
                // Check if phone already exists
                $stmt = $pdo->prepare('SELECT id FROM buyers WHERE phone = ?');
                $stmt->execute([$phone]);
                if ($stmt->fetch()) {
                    $error = 'This phone number is already registered.';
                } else {
                    // Check if email exists
                    $stmt = $pdo->prepare('SELECT id FROM buyers WHERE email = ?');
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = 'This email is already registered.';
                    }

                    if (empty($error)) {
                        // Hash password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        // Insert new buyer
                        $stmt = $pdo->prepare('INSERT INTO buyers (name, email, phone, password, address, status) 
                                            VALUES (?, ?, ?, ?, ?, "active")');
                        
                        if ($stmt->execute([$name, $email, $phone, $hashed_password, $address])) {
                            $buyer_id = $pdo->lastInsertId();

                            // Set session variables
                            $_SESSION['buyer_id'] = $buyer_id;
                            $_SESSION['buyer_name'] = $name;
                            $_SESSION['buyer_type'] = 'buyer';

                            // Redirect to success page
                            $_SESSION['success_message'] = 'Registration successful! Welcome to Agri-Connect.';
                            header('Location: ../index.php');
                            exit();
                        }
                    }
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'An error occurred during registration. Please try again.';
            }
        }
    }
}

$page_title = 'Register as Buyer - Agri-Connect';
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
        .registration-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .registration-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .registration-header i {
            font-size: 3rem;
            color: #198754;
            margin-bottom: 1rem;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .form-control:focus {
            border-color: #198754;
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
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
        .password-strength {
            height: 5px;
            margin-top: 0.5rem;
            border-radius: 2.5px;
            transition: all 0.3s ease;
        }
        .strength-weak { width: 33%; background-color: #dc3545; }
        .strength-medium { width: 66%; background-color: #ffc107; }
        .strength-strong { width: 100%; background-color: #198754; }
        .floating-image {
            position: fixed;
            bottom: -50px;
            right: -50px;
            width: 300px;
            opacity: 0.1;
            z-index: -1;
            transform: rotate(-15deg);
        }
    </style>
</head>
<body class="bg-light">
    <?php include '../includes/header.php'; ?>

    <div class="container py-5">
        <div class="registration-container">
            <div class="registration-header">
                <i class="bi bi-person-plus-fill"></i>
                <h1 class="h3">Join Agri-Connect as a Buyer</h1>
                <p class="text-muted">Connect directly with local farmers</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <?php csrfField(); ?>

                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="name" name="name" 
                           placeholder="Your Name" required
                           value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                    <label for="name">Full Name *</label>
                    <div class="invalid-feedback">Please enter your name.</div>
                </div>

                <div class="form-floating mb-3">
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           placeholder="Phone Number" required pattern="[0-9]{9}"
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    <label for="phone">Phone Number (6XXXXXXXX) *</label>
                    <div class="form-text">This will be your login username</div>
                    <div class="invalid-feedback">Please enter a valid phone number.</div>
                </div>

                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="name@example.com" required
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <label for="email">Email Address *</label>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>

                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Password" required minlength="8">
                    <label for="password">Password *</label>
                    <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                    <div class="password-strength" id="passwordStrength"></div>
                    <div class="form-text">Must be at least 8 characters long</div>
                    <div class="invalid-feedback">Password must be at least 8 characters long.</div>
                </div>

                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm Password" required minlength="8">
                    <label for="confirm_password">Confirm Password *</label>
                    <i class="bi bi-eye-slash password-toggle" id="toggleConfirmPassword"></i>
                    <div class="invalid-feedback">Passwords do not match.</div>
                </div>

                <div class="form-floating mb-3">
                    <textarea class="form-control" id="address" name="address" 
                              placeholder="Your Address" style="height: 100px"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                    <label for="address">Delivery Address (Optional)</label>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="../terms.php" class="text-success">Terms & Conditions</a>
                    </label>
                    <div class="invalid-feedback">
                        You must agree to the terms and conditions.
                    </div>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle"></i> Complete Registration
                    </button>
                </div>

                <div class="text-center">
                    <p class="mb-0">Already have an account? 
                        <a href="login.php" class="text-success text-decoration-none">Login here</a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <img src="../assets/images/vegetables.png" alt="Decorative" class="floating-image">

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

        // Password toggle
        function setupPasswordToggle(inputId, toggleId) {
            const input = document.getElementById(inputId);
            const toggle = document.getElementById(toggleId);

            toggle.addEventListener('click', function () {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('bi-eye');
                this.classList.toggle('bi-eye-slash');
            });
        }

        setupPasswordToggle('password', 'togglePassword');
        setupPasswordToggle('confirm_password', 'toggleConfirmPassword');

        // Password strength indicator
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        const strengthBar = document.getElementById('passwordStrength');

        function checkPasswordStrength(password) {
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.match(/[a-z]/) && password.match(/[A-Z]/)) strength++;
            if (password.match(/\d/)) strength++;
            if (password.match(/[^a-zA-Z\d]/)) strength++;
            return strength;
        }

        password.addEventListener('input', function() {
            const strength = checkPasswordStrength(this.value);
            strengthBar.className = 'password-strength';
            if (strength >= 3) {
                strengthBar.classList.add('strength-strong');
            } else if (strength >= 2) {
                strengthBar.classList.add('strength-medium');
            } else if (strength >= 1) {
                strengthBar.classList.add('strength-weak');
            }
        });

        // Confirm password validation
        confirmPassword.addEventListener('input', function() {
            if (this.value !== password.value) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
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
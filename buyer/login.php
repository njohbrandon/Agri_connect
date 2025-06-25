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
        $phone = sanitizeInput($_POST['phone']);
        $password = $_POST['password'];

        // Check for too many login attempts
        if (checkLoginAttempts($_SERVER['REMOTE_ADDR'])) {
            $error = 'Too many login attempts. Please try again later.';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT * FROM buyers WHERE phone = ? AND status = "active"');
                $stmt->execute([$phone]);
                $buyer = $stmt->fetch();

                if ($buyer && password_verify($password, $buyer['password'])) {
                    // Update last login
                    $stmt = $pdo->prepare('UPDATE buyers SET last_login = NOW() WHERE id = ?');
                    $stmt->execute([$buyer['id']]);

                    // Set session variables
                    $_SESSION['buyer_id'] = $buyer['id'];
                    $_SESSION['buyer_name'] = $buyer['name'];
                    $_SESSION['buyer_type'] = 'buyer';

                    // Redirect to home page
                    header('Location: ../index.php');
                    exit();
                } else {
                    recordLoginAttempt($_SERVER['REMOTE_ADDR']);
                    $error = 'Invalid phone number or password.';
                }
            } catch (PDOException $e) {
                error_log($e->getMessage());
                $error = 'An error occurred. Please try again.';
            }
        }
    }
}

$page_title = 'Buyer Login - Agri-Connect';
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
        .login-container {
            max-width: 450px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header i {
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
        .btn-success {
            padding: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .alert-danger {
            background-color: #ffe5e5;
            color: #dc3545;
        }
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
        <div class="login-container">
            <div class="login-header">
                <i class="bi bi-person-circle"></i>
                <h1 class="h3">Welcome Back!</h1>
                <p class="text-muted">Login to access your buyer account</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="needs-validation" novalidate>
                <?php csrfField(); ?>
                
                <div class="form-floating mb-3">
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           placeholder="Phone Number" required pattern="[0-9]{9}"
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    <label for="phone">Phone Number</label>
                    <div class="invalid-feedback">Please enter a valid phone number.</div>
                </div>

                <div class="form-floating mb-3 position-relative">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Password" required minlength="8">
                    <label for="password">Password</label>
                    <i class="bi bi-eye-slash password-toggle" id="togglePassword"></i>
                    <div class="invalid-feedback">Password is required.</div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="text-success text-decoration-none">Forgot Password?</a>
                </div>

                <div class="d-grid mb-3">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-box-arrow-in-right"></i> Login
                    </button>
                </div>

                <div class="text-center">
                    <p class="mb-0">Don't have an account? 
                        <a href="register.php" class="text-success text-decoration-none">Register here</a>
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
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
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
<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Redirect if already logged in
if (isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    // Check for too many login attempts
    if (checkLoginAttempts($_SERVER['REMOTE_ADDR'])) {
        $error = 'Too many login attempts. Please try again later.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? AND status = 'active'");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Login successful
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];
                
                // Update last login time
                $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                $stmt->execute([$admin['id']]);
                
                // Log the activity
                $stmt = $pdo->prepare("INSERT INTO admin_activity_log (admin_id, action, details, ip_address) VALUES (?, ?, ?, ?)");
                $stmt->execute([$admin['id'], 'login', 'Admin logged in successfully', $_SERVER['REMOTE_ADDR']]);
                
                header('Location: ../index.php');
                exit();
            } else {
                recordLoginAttempt($_SERVER['REMOTE_ADDR']);
                $error = 'Invalid username or password';
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Agri-Connect</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .login-container {
            max-width: 400px;
            margin: 0 auto;
            padding: 1rem;
        }
        
        .login-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            overflow: hidden;
        }
        
        .login-header {
            background: #2E7D32;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-control:focus {
            border-color: #2E7D32;
            box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.25);
        }
        
        .btn-login {
            background-color: #2E7D32;
            border-color: #2E7D32;
            color: white;
            padding: 0.75rem;
        }
        
        .btn-login:hover {
            background-color: #1B5E20;
            border-color: #1B5E20;
            color: white;
        }
        
        .input-group-text {
            background-color: #f8f9fa;
            border-right: none;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .input-group .form-control:focus {
            border-left: 1px solid #2E7D32;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <i class="bi bi-shield-lock fs-1 mb-2"></i>
                <h4 class="mb-0">Admin Login</h4>
                <p class="mb-0 mt-2">Agri-Connect Management</p>
            </div>
            <div class="login-body">
                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <?php csrfField(); ?>
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" class="form-control" id="username" name="username" 
                                   required autofocus>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="bi bi-key"></i>
                            </span>
                            <input type="password" class="form-control" id="password" name="password" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="d-grid">
                        <button type="submit" name="login" class="btn btn-login">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="text-center mt-3">
            <a href="../../index.php" class="text-decoration-none text-muted">
                <i class="bi bi-arrow-left me-1"></i>Back to Website
            </a>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 
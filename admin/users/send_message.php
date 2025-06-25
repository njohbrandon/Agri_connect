<?php
session_start();
require_once '../../includes/config.php';
require_once '../../includes/functions.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

// Verify CSRF token
if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Validate required fields
    if (!isset($_POST['farmer_id']) || !isset($_POST['subject']) || !isset($_POST['message'])) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }
    
    $farmer_id = (int)$_POST['farmer_id'];
    $subject = sanitizeInput($_POST['subject']);
    $message = sanitizeInput($_POST['message']);
    
    try {
        // Get farmer's email
        $stmt = $pdo->prepare("SELECT email, name FROM farmers WHERE id = ?");
        $stmt->execute([$farmer_id]);
        $farmer = $stmt->fetch();
        
        if (!$farmer) {
            echo json_encode(['success' => false, 'message' => 'Farmer not found']);
            exit();
        }
        
        // Send email
        $to = $farmer['email'];
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Agri-Connect <noreply@agrimarket.com>" . "\r\n";
        
        $email_body = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #2E7D32; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; background: #f9f9f9; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Message from Agri-Connect Admin</h2>
                </div>
                <div class='content'>
                    <p>Dear " . htmlspecialchars($farmer['name']) . ",</p>
                    <p>" . nl2br(htmlspecialchars($message)) . "</p>
                    <p>Best regards,<br>Agri-Connect Admin Team</p>
                </div>
                <div class='footer'>
                    <p>This is an automated message, please do not reply directly to this email.</p>
                </div>
            </div>
        </body>
        </html>";
        
        if (mail($to, $subject, $email_body, $headers)) {
            // Log the activity
            $stmt = $pdo->prepare("INSERT INTO admin_activity_log (admin_id, action, entity_type, entity_id, details, ip_address) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $_SESSION['admin_id'],
                'send_message',
                'farmer',
                $farmer_id,
                "Sent message: " . $subject,
                $_SERVER['REMOTE_ADDR']
            ]);
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send email']);
        }
        
    } catch (PDOException $e) {
        error_log($e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    exit();
}

// Invalid request
header('Content-Type: application/json');
echo json_encode(['success' => false, 'message' => 'Invalid request']); 
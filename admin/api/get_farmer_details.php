<?php
require_once '../includes/admin_session.php';
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('Farmer ID is required');
    }

    $farmerId = intval($_GET['id']);
    
    // Get farmer details
    $query = "SELECT 
                f.*,
                COUNT(DISTINCT p.id) as products_count,
                MAX(l.login_time) as last_login
            FROM farmers f
            LEFT JOIN products p ON f.id = p.farmer_id
            LEFT JOIN login_history l ON f.id = l.user_id AND l.user_type = 'farmer'
            WHERE f.id = ?
            GROUP BY f.id";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$farmerId]);
    
    $farmer = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$farmer) {
        throw new Exception('Farmer not found');
    }
    
    // Format dates
    $farmer['created_at'] = date('M d, Y h:i A', strtotime($farmer['created_at']));
    $farmer['last_login'] = $farmer['last_login'] 
        ? date('M d, Y h:i A', strtotime($farmer['last_login']))
        : 'Never';
    
    // Remove sensitive information
    unset($farmer['password']);
    unset($farmer['reset_token']);
    
    echo json_encode($farmer);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?> 
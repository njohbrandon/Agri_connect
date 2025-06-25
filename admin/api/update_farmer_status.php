<?php
require_once '../includes/admin_session.php';
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['farmer_id']) || !isset($input['status'])) {
        throw new Exception('Farmer ID and status are required');
    }
    
    $farmerId = intval($input['farmer_id']);
    $status = strtolower($input['status']);
    $reason = isset($input['reason']) ? trim($input['reason']) : '';
    
    // Validate status
    $validStatuses = ['active', 'suspended', 'blocked'];
    if (!in_array($status, $validStatuses)) {
        throw new Exception('Invalid status value');
    }
    
    // Start transaction
    $pdo->beginTransaction();
    
    // Update farmer status
    $query = "UPDATE farmers SET status = ?, updated_at = NOW() WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$status, $farmerId]);
    
    // Log the status change
    $query = "INSERT INTO status_changes (user_id, user_type, old_status, new_status, reason, admin_id, created_at)
              SELECT 
                id as user_id,
                'farmer' as user_type,
                status as old_status,
                ? as new_status,
                ?,
                ?,
                NOW()
              FROM farmers
              WHERE id = ?";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute([$status, $reason, $_SESSION['admin_id'], $farmerId]);
    
    // If blocking/suspending, also deactivate all their products
    if ($status !== 'active') {
        $query = "UPDATE products SET status = 'inactive', updated_at = NOW() WHERE farmer_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$farmerId]);
    }
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 
<?php
session_start();
include('../config/dbcon.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if(isset($_POST['farmer_id']) && isset($_POST['status'])) {
    $farmer_id = mysqli_real_escape_string($con, $_POST['farmer_id']);
    $status = mysqli_real_escape_string($con, $_POST['status']);
    $reason = isset($_POST['reason']) ? mysqli_real_escape_string($con, $_POST['reason']) : '';
    
    // Ensure status_logs table exists
    mysqli_query($con, "CREATE TABLE IF NOT EXISTS status_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        user_type ENUM('farmer','buyer','admin') NOT NULL,
        old_status VARCHAR(20) NOT NULL,
        new_status VARCHAR(20) NOT NULL,
        reason TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Start transaction
    mysqli_begin_transaction($con);
    
    try {
        // Update farmer status
        $update_query = "UPDATE farmers SET status = ?, updated_at = NOW() WHERE id = ?";
        $stmt = mysqli_prepare($con, $update_query);
        mysqli_stmt_bind_param($stmt, "si", $status, $farmer_id);
        mysqli_stmt_execute($stmt);
        
        // Get old status
        $old_status_query = "SELECT status FROM farmers WHERE id = ?";
        $stmt = mysqli_prepare($con, $old_status_query);
        mysqli_stmt_bind_param($stmt, "i", $farmer_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $old_status = mysqli_fetch_assoc($result)['status'];
        
        // Log the status change
        $log_query = "INSERT INTO status_logs (user_id, user_type, old_status, new_status, reason, created_at) 
                     VALUES (?, 'farmer', ?, ?, ?, NOW())";
        $stmt = mysqli_prepare($con, $log_query);
        mysqli_stmt_bind_param($stmt, "isss", $farmer_id, $old_status, $status, $reason);
        mysqli_stmt_execute($stmt);
        
        // If status is suspended, also suspend all active products
        if($status == 'suspended') {
            $suspend_products_query = "UPDATE products SET status = 'suspended', updated_at = NOW() 
                                     WHERE farmer_id = ? AND status = 'active'";
            $stmt = mysqli_prepare($con, $suspend_products_query);
            mysqli_stmt_bind_param($stmt, "i", $farmer_id);
            mysqli_stmt_execute($stmt);
        }
        
        // If status is active, reactivate suspended products
        if($status == 'active') {
            $reactivate_products_query = "UPDATE products SET status = 'active', updated_at = NOW() 
                                        WHERE farmer_id = ? AND status = 'suspended'";
            $stmt = mysqli_prepare($con, $reactivate_products_query);
            mysqli_stmt_bind_param($stmt, "i", $farmer_id);
            mysqli_stmt_execute($stmt);
        }
        
        // Commit transaction
        mysqli_commit($con);
        
        echo json_encode(['success' => true, 'message' => 'Status updated successfully']);
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($con);
        echo json_encode(['success' => false, 'message' => 'Error updating status: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?> 
<?php
session_start();
include('../config/dbcon.php');
header('Content-Type: application/json');

if(!isset($_SESSION['admin_id'])){
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit();
}
if(isset($_POST['product_id']) && isset($_POST['status'])){
    $product_id = mysqli_real_escape_string($con,$_POST['product_id']);
    $status = mysqli_real_escape_string($con,$_POST['status']);
    $reason = mysqli_real_escape_string($con, $_POST['reason'] ?? '');

    // Allowed statuses
    $allowed = ['active','inactive'];
    if(!in_array($status,$allowed)){
        echo json_encode(['success'=>false,'message'=>'Invalid status']);
        exit();
    }

    // Ensure status_logs exists
    mysqli_query($con,"CREATE TABLE IF NOT EXISTS status_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        entity_id INT NULL,
        user_type ENUM('farmer','buyer','admin','product') NOT NULL,
        old_status VARCHAR(20) NOT NULL,
        new_status VARCHAR(20) NOT NULL,
        reason TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    mysqli_begin_transaction($con);
    try{
        // Get old status
        $oldRes = mysqli_fetch_assoc(mysqli_query($con,"SELECT status FROM products WHERE id='$product_id' FOR UPDATE"));
        if(!$oldRes){ throw new Exception('Product not found'); }
        $old_status = $oldRes['status'];
        // Update
        mysqli_query($con,"UPDATE products SET status='$status', updated_at=NOW() WHERE id='$product_id'");
        // Log
        $stmt = mysqli_prepare($con,"INSERT INTO status_logs(user_id,entity_id,user_type,old_status,new_status,reason,created_at) VALUES(?,?,?,?,?,?,NOW())");
        mysqli_stmt_bind_param($stmt,'iissss', $_SESSION['admin_id'], $product_id, $user_type,$old_status,$status,$reason);
        $user_type='product';
        mysqli_stmt_execute($stmt);
        mysqli_commit($con);
        echo json_encode(['success'=>true]);
    }catch(Exception $e){
        mysqli_rollback($con);
        echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
    }
} else {
    echo json_encode(['success'=>false,'message'=>'Invalid parameters']);
}
?> 
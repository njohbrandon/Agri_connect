<?php
require_once '../includes/admin_session.php';
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
    
    // Debug: Log the connection status
    error_log("Database connection successful");
    
    // First, let's check the actual table name and structure
    $check_table = $pdo->query("SHOW TABLES LIKE 'farmers'");
    if ($check_table->rowCount() == 0) {
        $check_table = $pdo->query("SHOW TABLES LIKE 'farmer'");
        if ($check_table->rowCount() > 0) {
            error_log("Found table 'farmer' instead of 'farmers'");
            $table_name = 'farmer';
        } else {
            throw new Exception("Farmers table not found");
        }
    } else {
        $table_name = 'farmers';
    }
    
    // Get the table columns
    $columns = $pdo->query("SHOW COLUMNS FROM $table_name")->fetchAll(PDO::FETCH_COLUMN);
    error_log("Table columns: " . implode(", ", $columns));
    
    // Build the query based on existing columns
    $select_columns = array("f.id");
    if (in_array('name', $columns)) $select_columns[] = "f.name";
    if (in_array('email', $columns)) $select_columns[] = "f.email";
    if (in_array('phone', $columns)) $select_columns[] = "f.phone";
    if (in_array('status', $columns)) $select_columns[] = "f.status";
    if (in_array('created_at', $columns)) $select_columns[] = "f.created_at";
    if (in_array('profile_image', $columns)) $select_columns[] = "f.profile_image";
    if (in_array('location', $columns)) $select_columns[] = "f.location";
    
    $query = "SELECT " . implode(", ", $select_columns) . ", 
              COUNT(p.id) as products_count
              FROM $table_name f
              LEFT JOIN products p ON f.id = p.farmer_id";
    
    if (!empty($search)) {
        $searchFields = array();
        if (in_array('name', $columns)) $searchFields[] = "f.name LIKE ?";
        if (in_array('email', $columns)) $searchFields[] = "f.email LIKE ?";
        if (in_array('phone', $columns)) $searchFields[] = "f.phone LIKE ?";
        
        if (!empty($searchFields)) {
            $query .= " WHERE " . implode(" OR ", $searchFields);
            $searchParam = "%$search%";
            $params = array_fill(0, count($searchFields), $searchParam);
        }
    } else {
        $params = array();
    }
    
    $query .= " GROUP BY f.id ORDER BY f.created_at DESC";
    
    // Debug: Log the query
    error_log("Query: " . $query);
    error_log("Params: " . print_r($params, true));
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    
    $farmers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Debug: Log the results
    error_log("Number of farmers found: " . count($farmers));
    if (!empty($farmers)) {
        error_log("Sample farmer data: " . print_r($farmers[0], true));
    }
    
    echo json_encode($farmers);
} catch (Exception $e) {
    error_log("Error in get_farmers.php: " . $e->getMessage());
    error_log("Error trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch farmers: ' . $e->getMessage()]);
}
?> 
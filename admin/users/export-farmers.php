<?php
session_start();
include('../config/dbcon.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Set headers for CSV download
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="farmers_export_' . date('Y-m-d') . '.csv"');

// Create output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM for proper Excel encoding
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Add CSV headers
fputcsv($output, [
    'ID',
    'Name',
    'Email',
    'Phone',
    'Address',
    'Status',
    'Total Products',
    'Active Products',
    'Joined Date'
]);

// Get farmers data
$query = "SELECT f.id, f.name, f.email, f.phone, f.address, f.status,
          (
            SELECT COUNT(p1.id) FROM products p1 WHERE p1.farmer_id = f.id
          ) AS total_products,
          (
            SELECT COUNT(p2.id) FROM products p2 WHERE p2.farmer_id = f.id AND p2.status = 'active'
          ) AS active_products,
          f.created_at
          FROM farmers f
          ORDER BY f.created_at DESC";
$query_run = mysqli_query($con, $query);

// Write data rows
while($farmer = mysqli_fetch_array($query_run)) {
    fputcsv($output, [
        $farmer['id'],
        $farmer['name'],
        $farmer['email'],
        $farmer['phone'],
        $farmer['address'],
        $farmer['status'],
        $farmer['total_products'],
        $farmer['active_products'],
        date('Y-m-d', strtotime($farmer['created_at']))
    ]);
}

// Close the output stream
fclose($output);
?> 
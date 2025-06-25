<?php
session_start();
include('../config/dbcon.php');
if(!isset($_SESSION['admin_id'])){header('Location: ../auth/login.php');exit();}
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="products_export_'.date('Y-m-d').'.csv"');
$output=fopen('php://output','w');
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
fputcsv($output,['ID','Name','Category','Price','Status','Farmer','Created']);
$query="SELECT p.id,p.name,p.category,p.price,p.status,f.name AS farmer_name,p.created_at FROM products p JOIN farmers f ON p.farmer_id=f.id ORDER BY p.created_at DESC";
$run=mysqli_query($con,$query);
while($row=mysqli_fetch_assoc($run)){
    fputcsv($output,[$row['id'],$row['name'],$row['category'],$row['price'],$row['status'],$row['farmer_name'],date('Y-m-d',strtotime($row['created_at']))]);
}
fclose($output);
?> 
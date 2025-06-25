<?php
session_start();
include('../config/dbcon.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch all products with farmer info
$query = "SELECT p.*, f.name AS farmer_name, f.phone AS farmer_phone, f.address AS farmer_address
          FROM products p
          JOIN farmers f ON p.farmer_id = f.id
          ORDER BY p.created_at DESC";
$products_run = mysqli_query($con, $query);

$page_title = 'Manage Products';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - AgriConnect Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .product-row:hover {background-color: rgba(0,0,0,0.03);} 
        .status-badge{padding:5px 10px;border-radius:15px;font-size:0.8rem;white-space:nowrap;}
        .status-active{background:#d4edda;color:#155724;}
        .status-inactive{background:#fff3cd;color:#856404;}
        .status-suspended{background:#f8d7da;color:#721c24;}
        .action-btn{padding:5px 10px;border-radius:5px;font-size:0.9rem;transition:all 0.2s;}
        .action-btn:hover{transform:scale(1.05);} 
        .table-container{overflow-x:auto;} 
        .top-navbar{background:white;box-shadow:0 2px 4px rgba(0,0,0,0.1);} 
        /* Sidebar theme consistent with dashboard */
        :root{--primary-color:#2E7D32;--secondary-color:#66BB6A;--accent-color:#43A047;}
        .sidebar{background:var(--primary-color);min-height:100vh;color:white;}
        .sidebar .nav-link{color:rgba(255,255,255,0.85);padding:0.75rem 1.25rem;margin:0.2rem 0;border-radius:0.375rem;}
        .sidebar .nav-link:hover{color:white;background:rgba(255,255,255,0.1);} 
        .sidebar .nav-link.active{background:var(--secondary-color);color:white;}
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <?php include('../includes/sidebar.php'); ?>
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-2">
            <nav class="navbar navbar-expand-lg top-navbar mb-4 rounded-3">
                <div class="container-fluid">
                    <button class="btn btn-outline-secondary d-md-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar"><i class="fas fa-bars"></i></button>
                    <h1 class="h3 mb-0"><?php echo $page_title; ?></h1>
                    <div class="ms-auto d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-outline-secondary" onclick="exportProducts()"><i class="fas fa-download me-1"></i>Export</button>
                        <span class="text-muted d-none d-md-inline-block"><?php echo date('l, F j, Y'); ?></span>
                    </div>
                </div>
            </nav>
            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search products...">
                        <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="suspended">Suspended</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" id="categoryFilter">
                        <option value="">All Categories</option>
                        <?php 
                        $cat_run=mysqli_query($con,"SELECT DISTINCT category FROM products ORDER BY category");
                        while($c=mysqli_fetch_array($cat_run)) echo '<option value="'.htmlspecialchars($c['category']).'">'.htmlspecialchars($c['category']).'</option>'; ?>
                    </select>
                </div>
            </div>
            <!-- Products Table -->
            <div class="table-container">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Price</th>
                            <th>Farmer</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($prod=mysqli_fetch_array($products_run)): ?>
                        <tr class="product-row">
                            <td><?php echo $prod['id']; ?></td>
                            <td><?php echo htmlspecialchars($prod['name']); ?></td>
                            <td><?php echo htmlspecialchars($prod['category']); ?></td>
                            <td><?php echo number_format($prod['price'],0,'.',','); ?> XAF</td>
                            <td><?php echo htmlspecialchars($prod['farmer_name']); ?></td>
                            <td><span class="status-badge status-<?php echo $prod['status']; ?>"><?php echo ucfirst($prod['status']); ?></span></td>
                            <td><?php echo date('M d, Y',strtotime($prod['created_at'])); ?></td>
                            <td>
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary action-btn" onclick="viewProduct(<?php echo $prod['id']; ?>)" title="View"><i class="fas fa-eye"></i></button>
                                    <button class="btn btn-sm btn-outline-success action-btn" onclick="updateProductStatus(<?php echo $prod['id']; ?>,'active')" title="Activate"><i class="fas fa-check"></i></button>
                                    <button class="btn btn-sm btn-outline-danger action-btn" onclick="updateProductStatus(<?php echo $prod['id']; ?>,'inactive')" title="Deactivate"><i class="fas fa-ban"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>
<!-- Product Details Modal -->
<div class="modal fade" id="productDetailsModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Product Details</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="productDetailsContent"></div></div></div></div>
<!-- Status Update Modal -->
<div class="modal fade" id="productStatusModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Update Product Status</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><form id="productStatusForm"><input type="hidden" name="product_id" id="productId"><input type="hidden" name="status" id="productStatus"><div class="mb-3"><label for="reasonP" class="form-label">Reason</label><textarea class="form-control" id="reasonP" name="reason" rows="3"></textarea></div></form></div><div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" onclick="submitProductStatus()">Update</button></div></div></div></div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Search filter
$('#searchInput').on('keyup',function(){const t=$(this).val().toLowerCase();$('.product-row').each(function(){const rowText=$(this).text().toLowerCase();$(this).toggle(rowText.includes(t));});});
// Status filter
$('#statusFilter').on('change',function(){const s=$(this).val();$('.product-row').each(function(){const badge=$(this).find('.status-badge').text().trim().toLowerCase();$(this).toggle(!s||badge===s);});});
// Category filter
$('#categoryFilter').on('change',function(){const c=$(this).val();$('.product-row').each(function(){const cat=$(this).children().eq(2).text().trim();$(this).toggle(!c||cat===c);});});
function viewProduct(id){fetch('product-details.php?product_id='+id).then(r=>r.text()).then(html=>{$('#productDetailsContent').html(html);new bootstrap.Modal(document.getElementById('productDetailsModal')).show();});}
function updateProductStatus(id,status){$('#productId').val(id);$('#productStatus').val(status);new bootstrap.Modal(document.getElementById('productStatusModal')).show();}
function submitProductStatus(){const fd=new FormData(document.getElementById('productStatusForm'));fetch('update-product-status.php',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{if(d.success) location.reload(); else alert('Error: '+d.message);});}
function exportProducts(){window.location.href='export-products.php';}
</script>
</body>
</html> 
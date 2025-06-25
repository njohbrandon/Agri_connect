<?php
session_start();
include('../config/dbcon.php');

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Get all farmers with their product counts
$query = "SELECT 
            f.id,
            f.name,
            f.email,
            f.phone,
            f.address,
            f.status,
            f.created_at,
            (SELECT COUNT(p1.id) FROM products p1 WHERE p1.farmer_id = f.id) AS total_products,
            (SELECT COUNT(p2.id) FROM products p2 WHERE p2.farmer_id = f.id AND p2.status = 'active') AS active_products
          FROM farmers f
          ORDER BY f.created_at DESC";
$query_run = mysqli_query($con, $query);

$page_title = "Manage Farmers";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - AgriConnect Admin</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        .farmer-row {
            transition: all 0.2s;
        }
        .farmer-row:hover {
            background-color: rgba(0,0,0,0.03);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
            white-space: nowrap;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-suspended, .status-blocked {
            background-color: #f8d7da;
            color: #721c24;
        }
        .action-btn {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            transition: all 0.2s;
        }
        .action-btn:hover {
            transform: scale(1.05);
        }
        .table-container {
            overflow-x: auto;
        }
        .table-hover tbody tr:hover {
            background-color: rgba(0,123,255,0.07);
        }
        .farmer-info {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .main-content {
            padding-top: 0 !important;
        }

        /* Consistent admin theme colours */
        :root {
            --primary-color: #2E7D32;
            --secondary-color: #66BB6A;
            --accent-color: #43A047;
        }

        .sidebar {
            background: var(--primary-color);
            min-height: 100vh;
            color: white;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.85);
            padding: 0.75rem 1.25rem;
            margin: 0.2rem 0;
            border-radius: 0.375rem;
        }

        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar .nav-link.active {
            background: var(--secondary-color);
            color: white;
        }

        .top-navbar {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Removed duplicate full-page header include to avoid nested <html> structures -->

    <div class="container-fluid">
        <div class="row">
            <?php include('../includes/sidebar.php'); ?>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-2 main-content">
                <!-- Top Navbar -->
                <nav class="navbar navbar-expand-lg top-navbar mb-4 rounded-3">
                    <div class="container-fluid">
                        <h1 class="h3 mb-0"><?php echo $page_title; ?></h1>
                        <div class="ms-auto d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportFarmers()">
                                <i class="fas fa-download me-1"></i> Export
                            </button>
                            <span class="text-muted d-none d-md-inline-block"><?php echo date('l, F j, Y'); ?></span>
                        </div>
                    </div>
                </nav>

                <!-- Search and Filter Section -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Search farmers...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <select class="form-select" id="statusFilter">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="pending">Pending</option>
                            <option value="suspended">Suspended</option>
                        </select>
                    </div>
                </div>

                <!-- Farmers Table -->
                <div class="table-container">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Farmer Name</th>
                                <th>Contact Information</th>
                                <th>Location</th>
                                <th>Products</th>
                                <th>Status</th>
                                <th>Joined Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($farmer = mysqli_fetch_array($query_run)): ?>
                            <tr class="farmer-row">
                                <td class="fw-bold"><?php echo htmlspecialchars($farmer['name'] ?? 'N/A'); ?></td>
                                <td>
                                    <div><i class="fas fa-envelope text-muted me-2"></i><?php echo htmlspecialchars($farmer['email'] ?? 'N/A'); ?></div>
                                    <div><i class="fas fa-phone text-muted me-2"></i><?php echo htmlspecialchars($farmer['phone'] ?? 'N/A'); ?></div>
                                </td>
                                <td>
                                    <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                    <?php 
                                        $farmerLocation = !empty($farmer['address']) ? $farmer['address'] : 'N/A';
                                        echo htmlspecialchars($farmerLocation);
                                    ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary rounded-pill">
                                        <?php echo $farmer['total_products'] ?? 0; ?> total
                                    </span>
                                    <span class="badge bg-success rounded-pill">
                                        <?php echo $farmer['active_products'] ?? 0; ?> active
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                        $rawStatus = !empty($farmer['status']) ? $farmer['status'] : 'pending';
                                        $displayStatus = $rawStatus;
                                        if($rawStatus === 'suspended' || $rawStatus === 'blocked') {
                                            $displayStatus = 'Blocked';
                                        }
                                        echo '<span class="status-badge status-' . htmlspecialchars($rawStatus) . '">' . htmlspecialchars(ucfirst($displayStatus)) . '</span>'; 
                                    ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($farmer['created_at'] ?? 'now')); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-outline-primary action-btn" 
                                                onclick="viewFarmer(<?php echo $farmer['id']; ?>)" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success action-btn" 
                                                onclick="updateStatus(<?php echo $farmer['id']; ?>, 'active')" title="Approve">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger action-btn" 
                                                onclick="updateStatus(<?php echo $farmer['id']; ?>, 'suspended')" title="Suspend">
                                            <i class="fas fa-ban"></i>
                                        </button>
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

    <!-- Farmer Details Modal -->
    <div class="modal fade" id="farmerDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Farmer Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="farmerDetailsContent">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusUpdateModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Farmer Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="statusUpdateForm">
                        <input type="hidden" id="farmerId" name="farmer_id">
                        <input type="hidden" id="newStatus" name="status">
                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for status change</label>
                            <textarea class="form-control" id="reason" name="reason" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitStatusUpdate()">Update Status</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('.farmer-row');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchText) ? '' : 'none';
            });
        });

        // Status filter
        document.getElementById('statusFilter').addEventListener('change', function() {
            const status = this.value;
            const rows = document.querySelectorAll('.farmer-row');
            
            rows.forEach(row => {
                const cardStatus = row.querySelector('.status-badge').textContent.trim().toLowerCase();
                row.style.display = !status || cardStatus === status ? '' : 'none';
            });
        });

        // View farmer details
        function viewFarmer(id) {
            fetch(`farmer-details.php?farmer_id=${id}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('farmerDetailsContent').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('farmerDetailsModal')).show();
                });
        }

        // Update farmer status
        function updateStatus(id, status) {
            document.getElementById('farmerId').value = id;
            document.getElementById('newStatus').value = status;
            new bootstrap.Modal(document.getElementById('statusUpdateModal')).show();
        }

        // Submit status update
        function submitStatusUpdate() {
            const form = document.getElementById('statusUpdateForm');
            const formData = new FormData(form);

            fetch('update-farmer-status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    location.reload();
                } else {
                    alert('Error updating status: ' + data.message);
                }
            });
        }

        // Export farmers
        function exportFarmers() {
            window.location.href = 'export-farmers.php';
        }
    </script>
</body>
</html> 
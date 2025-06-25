<?php
include('../config/dbcon.php');

if(isset($_GET['farmer_id'])) {
    $farmer_id = mysqli_real_escape_string($con, $_GET['farmer_id']);
    
    // Get farmer details (no p.views or p.rating)
    $query = "SELECT f.*, 
              COUNT(p.id) as total_products,
              SUM(CASE WHEN p.status = 'active' THEN 1 ELSE 0 END) as active_products
              FROM farmers f
              LEFT JOIN products p ON f.id = p.farmer_id
              WHERE f.id = '$farmer_id'
              GROUP BY f.id";
    $query_run = mysqli_query($con, $query);
    
    if(mysqli_num_rows($query_run) > 0) {
        $farmer = mysqli_fetch_array($query_run);
        // Determine the best field for location/address
        $farmer_location = '';
        if (!empty($farmer['address'])) {
            $farmer_location = $farmer['address'];
        } elseif (!empty($farmer['location'])) {
            $farmer_location = $farmer['location'];
        } else {
            $farmer_location = 'N/A';
        }
        ?>
        <div class="row">
            <div class="col-md-4">
                <div class="text-center mb-4">
                    <!-- Removed profile image section due to error -->
                </div>
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Contact Information</h5>
                        <p class="card-text">
                            <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($farmer['email'] ?? 'N/A'); ?><br>
                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($farmer['phone'] ?? 'N/A'); ?><br>
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($farmer_location); ?>
                        </p>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Account Status</h5>
                        <p class="card-text">
                            <span class="badge bg-<?php echo $farmer['status'] == 'active' ? 'success' : ($farmer['status'] == 'pending' ? 'warning' : 'danger'); ?>">
                                <?php echo ucfirst($farmer['status'] ?? 'Pending'); ?>
                            </span>
                            <br>
                            <small class="text-muted">
                                Joined: <?php echo date('M d, Y', strtotime($farmer['created_at'] ?? 'now')); ?>
                            </small>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Profile Information</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($farmer['name'] ?? 'N/A'); ?></p>
                                <p><strong>Business Name:</strong> <?php echo htmlspecialchars($farmer['business_name'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($farmer_location); ?></p>
                                <p><strong>Specialization:</strong> <?php echo htmlspecialchars($farmer['specialization'] ?? 'N/A'); ?></p>
                            </div>
                        </div>
                        <div class="mt-3">
                            <h6>About</h6>
                            <p><?php echo nl2br(htmlspecialchars($farmer['about'] ?? 'No description available.')); ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Performance Metrics</h5>
                        <div class="row text-center">
                            <div class="col-md-6">
                                <h3><?php echo $farmer['total_products'] ?? 0; ?></h3>
                                <p class="text-muted">Total Products</p>
                            </div>
                            <div class="col-md-6">
                                <h3><?php echo $farmer['active_products'] ?? 0; ?></h3>
                                <p class="text-muted">Active Products</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Recent Products</h5>
                        <?php
                        $products_query = "SELECT * FROM products WHERE farmer_id = '$farmer_id' ORDER BY created_at DESC LIMIT 5";
                        $products_run = mysqli_query($con, $products_query);
                        
                        if(mysqli_num_rows($products_run) > 0) {
                            while($product = mysqli_fetch_array($products_run)) {
                                ?>
                                <div class="d-flex align-items-center mb-3">
                                    <?php if(!empty($product['image'])): ?>
                                    <img src="/farmers_market_place/uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                         class="rounded" 
                                         style="width: 50px; height: 50px; object-fit: cover;"
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         onerror="this.onerror=null;this.src='/farmers_market_place/assets/images/default-product.png';">
                                    <?php endif; ?>
                                    <div class="ms-3">
                                        <h6 class="mb-0"><?php echo htmlspecialchars($product['name'] ?? 'N/A'); ?></h6>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($product['created_at'] ?? 'now')); ?> |
                                            <span class="badge bg-<?php echo $product['status'] == 'active' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($product['status'] ?? 'Pending'); ?>
                                            </span>
                                        </small>
                                    </div>
                                </div>
                                <?php
                            }
                        } else {
                            echo '<p class="text-muted">No products found.</p>';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    } else {
        echo '<div class="alert alert-danger">Farmer not found.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request.</div>';
}
?> 
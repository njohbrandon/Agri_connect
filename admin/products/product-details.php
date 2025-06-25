<?php
include('../config/dbcon.php');
if(isset($_GET['product_id'])) {
    $id = mysqli_real_escape_string($con, $_GET['product_id']);
    $query = "SELECT p.*, f.name AS farmer_name, f.email AS farmer_email, f.phone AS farmer_phone, f.address AS farmer_address
              FROM products p JOIN farmers f ON p.farmer_id = f.id WHERE p.id = '$id'";
    $run = mysqli_query($con, $query);
    if(mysqli_num_rows($run)>0){
        $prod = mysqli_fetch_assoc($run);
        ?>
        <div class="row">
            <div class="col-md-4 text-center">
                <img src="/farmers_market_place/<?php echo !empty($prod['image']) ? 'uploads/products/'.$prod['image'] : 'assets/images/default-product.jpg'; ?>" class="img-fluid rounded mb-3" style="max-height:200px;object-fit:cover;">
                <h5 class="fw-bold"><?php echo htmlspecialchars($prod['name']); ?></h5>
                <span class="badge bg-success"><?php echo htmlspecialchars($prod['category']); ?></span>
                <p class="h5 mt-2 text-primary"><?php echo number_format($prod['price'],0,'.',','); ?> XAF</p>
            </div>
            <div class="col-md-8">
                <h6>Description</h6>
                <p><?php echo nl2br(htmlspecialchars($prod['description'])); ?></p>
                <hr>
                <h6>Farmer Information</h6>
                <p>
                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($prod['farmer_name']); ?><br>
                    <i class="fas fa-envelope me-1"></i><?php echo htmlspecialchars($prod['farmer_email']); ?><br>
                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($prod['farmer_phone']); ?><br>
                    <i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($prod['farmer_address']); ?>
                </p>
                <hr>
                <small class="text-muted">Added on <?php echo date('M d, Y', strtotime($prod['created_at'])); ?></small>
            </div>
        </div>
        <?php
    } else {
        echo '<div class="alert alert-danger">Product not found.</div>';
    }
} else {
    echo '<div class="alert alert-danger">Invalid request.</div>';
}
?> 
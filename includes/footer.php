<?php
// Get the current directory depth to handle relative paths
$current_path = $_SERVER['PHP_SELF'];
$root_path = '';
if (strpos($current_path, '/farmer/') !== false) {
    $root_path = '../';
}
?>
<footer class="bg-light mt-5 py-4">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-3">
                <h5>About Agri-Connect</h5>
                <p class="text-muted">Connecting farmers and buyers directly, making fresh produce accessible to everyone.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo $root_path; ?>products.php" class="text-decoration-none text-muted">Browse Products</a></li>
                    <li><a href="<?php echo $root_path; ?>about.php" class="text-decoration-none text-muted">About Us</a></li>
                    <li><a href="<?php echo $root_path; ?>contact.php" class="text-decoration-none text-muted">Contact</a></li>
                    <li><a href="<?php echo $root_path; ?>farmer/register.php" class="text-decoration-none text-muted">Become a Seller</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h5>Contact Us</h5>
                <ul class="list-unstyled text-muted">
                    <li>Email: info@agri-connect.com</li>
                    <li>Phone: (+237) 683076048</li>
                    <li>Address: 123 Farming Street</li>
                    <li>City, Bamenda</li>
                </ul>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <p class="text-muted mb-0">&copy; <?php echo date('Y'); ?> Agri-Connect. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="<?php echo $root_path; ?>privacy.php" class="text-decoration-none text-muted me-3">Privacy Policy</a>
                <a href="<?php echo $root_path; ?>terms.php" class="text-decoration-none text-muted">Terms of Service</a>
            </div>
        </div>
    </div>
</footer> 
</div>
<footer class="bg-dark text-white py-4 mt-4">
    <div class="container">
        <div class="row">
            <div class="col-md-6">
                <h5><?php echo SITE_NAME; ?></h5>
                <p>Sistem manajemen perpustakaan yang lengkap untuk pelacakan buku dan manajemen peminjaman yang
                    efisien.</p>
            </div>
            <div class="col-md-3">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?php echo SITE_URL; ?>/index.php" class="text-white">Home</a></li>
                    <?php if (!isLoggedIn()): ?>
                    <li><a href="<?php echo SITE_URL; ?>/login.php" class="text-white">Login</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/register.php" class="text-white">Register</a></li>
                    <?php else: ?>
                    <li><a href="<?php echo SITE_URL; ?>/logout.php" class="text-white">Logout</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-3">
                <h5>Contact</h5>
                <address>
                    <p><i class="fas fa-map-marker-alt"></i>JL. Merdeka</p>
                    <p><i class="fas fa-phone"></i> 414-122</p>
                    <p><i class="fas fa-envelope"></i> info@perpusdijital.com</p>
                </address>
            </div>
        </div>
        <hr>
        <div class="text-center">
            <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-k6d4wzSIapyDyv1kpU366/PK5hCdSbCRGRCMv+eplOQJWyd1fbcAu9OCUj5zNLiq" crossorigin="anonymous">
</script>
<!-- Custom JavaScript -->
<script src="<?php echo SITE_URL; ?>/assets/js/script.js"></script>
</body>

</html>
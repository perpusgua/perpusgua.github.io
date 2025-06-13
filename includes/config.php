<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'library_db');

// Site configuration
define('SITE_NAME', 'Perpustakaan Digital Gua (Giat Ukur Akal)');
define('SITE_URL', 'http://localhost/library_website');

// Fine calculation
define('FINE_PER_DAY', value: 15000); // $15k per day

function formatRupiah($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

?>
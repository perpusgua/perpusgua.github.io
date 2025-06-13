<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Logout user
logoutUser();

// Redirect ke halaman login dengan pesan sukses
$_SESSION['success_message'] = 'You have been successfully logged out';
redirect('login.php');
?>
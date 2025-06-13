<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'member/dashboard.php');
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username']);
    $password = $_POST['password'];
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter both username and password';
    } else {
        if (loginUser($username, $password)) {
            // Redirect berdasarkan role
            redirect(isAdmin() ? 'admin/dashboard.php' : 'member/dashboard.php');
        } else {
            $error = 'Username / Password Salah!';
        }
    }
}

// Periksa parameter error
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'unauthorized') {
        $error = 'You need admin privileges to access that page';
    } elseif ($_GET['error'] === 'login_required') {
        $error = 'Please login to access that page';
    }
}

$pageTitle = 'Login';


require_once 'includes/header.php';
?>

<div class="row justify-content-center" style="padding-bottom: 25vh;">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Loginnn</h4>
            </div>
            <div class="card-body">
                <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="post" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Masuk</button>
                    </div>
                </form>

                <div class="mt-3 text-center">
                    <p>Ga punya akun? <a href="register.php">Register sini</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php

require_once 'includes/footer.php';
?>
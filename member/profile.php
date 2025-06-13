<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// membutuhkan login untuk mengakses
requireLogin();

// Jangan izin admin akses area anggota
if (isAdmin()) {
    redirect('../admin/dashboard.php');
}

// Get user details
$userId = getCurrentUserId();
$user = getUserById($userId);

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = clean($_POST['full_name']);
    $phone = clean($_POST['phone']);
    $address = clean($_POST['address']);
    
    // Basic validation
    if (empty($fullName)) {
        $_SESSION['error_message'] = 'Full name is required';
    } else {
        $conn = getDB();
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE id = ?");
        $stmt->bind_param("sssi", $fullName, $phone, $address, $userId);
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = 'Profile updated successfully';
            // Reload user data
            $user = getUserById($userId);
        } else {
            $_SESSION['error_message'] = 'Error updating profile';
        }
    }
    redirect('profile.php');
}

// Process password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Basic validation
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $_SESSION['error_message'] = 'All password fields are required';
    } elseif ($newPassword !== $confirmPassword) {
        $_SESSION['error_message'] = 'New passwords do not match';
    } elseif (strlen($newPassword) < 6) {
        $_SESSION['error_message'] = 'New password must be at least 6 characters long';
    } else {
        // Verify current password
        $conn = getDB();
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        
        if (password_verify($currentPassword, $userData['password'])) {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashedPassword, $userId);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Password changed successfully';
            } else {
                $_SESSION['error_message'] = 'Error changing password';
            }
        } else {
            $_SESSION['error_message'] = 'Current password is incorrect';
        }
    }
    
    // Redirect to avoid form resubmission
    redirect('profile.php');
}


$pageTitle = 'My Profile';


require_once '../includes/header.php';
?>

<div class="row">
    <div class="col-md-12">
        <h1 class="mb-4">My Profile</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Personal Information</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>"
                            readonly>
                        <small class="text-muted">Username cannot be changed</small>
                    </div>
                    <div class="mb-3">
                        <label for="full_name" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="full_name" name="full_name"
                            value="<?php echo $user['full_name']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                            value="<?php echo $user['phone']; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <textarea class="form-control" id="address" name="address"
                            rows="3"><?php echo $user['address']; ?></textarea>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Ganti Password</h5>
            </div>
            <div class="card-body">
                <form method="post" action="">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" id="current_password" name="current_password"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                        <small class="text-muted">At least 6 characters long</small>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                            required>
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">Ganti Password</button>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Account Information</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Member Sejak
                        <span><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                    </li>
                    <?php
                    // Get loan statistics
                    $conn = getDB();
                    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM loans WHERE user_id = ?");
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $totalLoans = $result->fetch_assoc()['total'];
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total Pinjaman
                        <span class="badge bg-primary"><?php echo $totalLoans; ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php

require_once '../includes/footer.php';
?>
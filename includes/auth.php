<?php
require_once 'db.php';
require_once 'functions.php';

// Start session if not already started
function startSession() {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

// Register a new user
function registerUser($username, $password, $fullName, $phone = null, $address = null) {
    $conn = getDB();
    
    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return false; // Username already taken
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $conn->prepare("INSERT INTO users (username, password, full_name, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $hashedPassword, $fullName, $phone, $address);
    
    return $stmt->execute();
}

// Login a user
function loginUser($username, $password) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            startSession();
            
            // Store user info in session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            return true;
        }
    }
    
    return false;
}

// Check if user is logged in
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

// Check if user is admin
function isAdmin() {
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Logout user
function logoutUser() {
    startSession();
    session_unset();
    session_destroy();
}

// Get current user ID
function getCurrentUserId() {
    startSession();
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Get current username
function getCurrentUsername() {
    startSession();
    return isset($_SESSION['username']) ? $_SESSION['username'] : null;
}

// Check if user is authorized to access admin area
function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        redirect('../login.php?error=unauthorized');
    }
}

// Check if user is authorized to access member area
function requireLogin() {
    if (!isLoggedIn()) {
        redirect('login.php?error=login_required');
    }
}
?>
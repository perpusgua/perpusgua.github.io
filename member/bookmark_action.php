<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/bookmark_functions.php';

// Cek apakah pengguna sudah login
if (!isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Anda harus login terlebih dahulu']);
    exit;
}

// Cek apakah ada data POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Log untuk debugging
    error_log('Received bookmark request: ' . print_r($_POST, true));
    
    $user_id = getCurrentUserId();
    $book_id = isset($_POST['book_id']) ? intval($_POST['book_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($book_id <= 0) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'ID buku tidak valid']);
        exit;
    }
    
    // Debugging info
    error_log("Processing bookmark: User ID: $user_id, Book ID: $book_id, Action: $action");
    
    // Proses aksi bookmark
    if ($action === 'add') {
        $result = addBookmark($user_id, $book_id);
        $message = $result ? 'Buku berhasil ditambahkan ke bookmark' : 'Buku sudah ada di bookmark';
        $is_bookmarked = true;
    } elseif ($action === 'remove') {
        $result = removeBookmark($user_id, $book_id);
        $message = $result ? 'Buku berhasil dihapus dari bookmark' : 'Gagal menghapus bookmark';
        $is_bookmarked = false;
    } else {
        $result = false;
        $message = 'Aksi tidak valid';
        $is_bookmarked = isBookmarked($user_id, $book_id);
    }
    
    // Log hasil
    error_log("Bookmark result: " . ($result ? 'Success' : 'Failed') . " - $message");
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => $message, 
        'is_bookmarked' => $is_bookmarked
    ]);
    exit;
}

// Jika bukan POST request, redirect ke halaman buku
header('Location: books.php');
exit;
?>
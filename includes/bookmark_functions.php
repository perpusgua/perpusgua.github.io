<?php
require_once 'db.php';

// Fungsi untuk menambah bookmark
function addBookmark($user_id, $book_id) {
  global $conn;
  
  // Periksa koneksi database
  if (!$conn) {
    $conn = getDB();
  }
  
  // Cek apakah bookmark sudah ada
  $stmt = $conn->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND book_id = ?");
  $stmt->bind_param("ii", $user_id, $book_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  if ($result->num_rows > 0) {
    return false; // Bookmark sudah ada
  }
  
  // Tambahkan bookmark baru
  $stmt = $conn->prepare("INSERT INTO bookmarks (user_id, book_id) VALUES (?, ?)");
  $stmt->bind_param("ii", $user_id, $book_id);
  $success = $stmt->execute();
  
  return $success;
}

// Fungsi untuk menghapus bookmark
function removeBookmark($user_id, $book_id) {
  global $conn;
  
  if (!$conn) {
    $conn = getDB();
  }
  
  $stmt = $conn->prepare("DELETE FROM bookmarks WHERE user_id = ? AND book_id = ?");
  $stmt->bind_param("ii", $user_id, $book_id);
  $success = $stmt->execute();
  
  return $success;
}

// Fungsi untuk mengecek apakah buku sudah di-bookmark
function isBookmarked($user_id, $book_id) {
  global $conn;
  
  if (!$conn) {
    $conn = getDB();
  }
  
  $stmt = $conn->prepare("SELECT id FROM bookmarks WHERE user_id = ? AND book_id = ?");
  $stmt->bind_param("ii", $user_id, $book_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  return ($result->num_rows > 0);
}

// Fungsi untuk mendapatkan semua bookmark user
function getUserBookmarks($user_id) {
  global $conn;
  
  if (!$conn) {
    $conn = getDB();
  }
  
  $stmt = $conn->prepare("
    SELECT b.*, bm.created_at as bookmarked_at 
    FROM bookmarks bm
    JOIN books b ON bm.book_id = b.id
    WHERE bm.user_id = ?
    ORDER BY bm.created_at DESC
  ");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $result = $stmt->get_result();
  
  $bookmarks = [];
  while ($row = $result->fetch_assoc()) {
    $bookmarks[] = $row;
  }
  
  return $bookmarks;
}
?>
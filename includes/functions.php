<?php
require_once 'db.php';

// Sanitize input data
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Mengalihkan ke halaman tertentu
function redirect($page) {
    header("Location: $page");
    exit;
}

// Hitung denda untuk buku yang sudah lewat
function calculateFine($dueDate) {
    $today = date('Y-m-d');
    $due = new DateTime($dueDate);
    $current = new DateTime($today);
    $diff = $current->diff($due);
    
    // Jika hari ini melewati tanggal jatuh tempo
    if ($due < $current) {
        $daysLate = $diff->days;
        return $daysLate * FINE_PER_DAY;
    }
    
    return 0;
}

// Periksa apakah sebuah buku tersedia untuk meminjam
function isBookAvailable($bookId) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT available_copies FROM books WHERE id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $book = $result->fetch_assoc();
        return $book['available_copies'] > 0;
    }
    
    return false;
}

// Perbarui jumlah salinan buku yang tersedia
function updateBookAvailability($bookId, $increment = false) {
    $conn = getDB();
    
    if ($increment) {
        $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE id = ?");
    } else {
        $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE id = ?");
    }
    
    $stmt->bind_param("i", $bookId);
    return $stmt->execute();
}

// Dapatkan semua kategori dari buku
function getCategories() {
    $conn = getDB();
    $result = $conn->query("SELECT DISTINCT category FROM books ORDER BY category");
    
    $categories = [];
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
    }
    return $categories;
}

// Get user role
function getUserRole($userId) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        return $user['role'];
    }
    
    return null;
}

// Periksa apakah user memiliki buku yang sudah lewat
function hasOverdueBooks($userId) {
    $conn = getDB();
    $today = date('Y-m-d');
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM loans WHERE user_id = ? AND due_date < ? AND return_date IS NULL");
    $stmt->bind_param("is", $userId, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['count'] > 0;
}

// Dapatkan semua buku dengan filter opsional
function getBooks($category = null, $search = null) {
    $conn = getDB();
    $sql = "SELECT * FROM books WHERE 1=1";
    
    if ($category) {
        $sql .= " AND category = '$category'";
    }
    
    if ($search) {
        $sql .= " AND (title LIKE '%$search%' OR author LIKE '%$search%' OR isbn LIKE '%$search%')";
    }
    
    $sql .= " ORDER BY title";
    $result = $conn->query($sql);
    
    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    
    return $books;
}

// Dapatkan detail buku dengan ID
function getBookById($bookId) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->bind_param("i", $bookId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Dapatkan detail user dengan ID
function getUserById($userId) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Dapatkan Detail Pinjaman dengan ID
function getLoanById($loanId) {
    $conn = getDB();
    $stmt = $conn->prepare("SELECT l.*, b.title as book_title, u.username FROM loans l 
                           JOIN books b ON l.book_id = b.id 
                           JOIN users u ON l.user_id = u.id 
                           WHERE l.id = ?");
    $stmt->bind_param("i", $loanId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Dapatkan pinjaman user
function getUserLoans($userId, $status = null) {
    $conn = getDB();
    $sql = "SELECT l.*, b.title as book_title, b.author FROM loans l 
            JOIN books b ON l.book_id = b.id 
            WHERE l.user_id = ?";
    
    if ($status) {
        $sql .= " AND l.status = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $userId, $status);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $loans = [];
    while ($row = $result->fetch_assoc()) {
        $loans[] = $row;
    }
    
    return $loans;
}

// Perbarui pinjaman yang sudah lewat
function updateOverdueLoans() {
    $conn = getDB();
    $today = date('Y-m-d');
    
    // Perbarui status untuk terlambat
    $stmt = $conn->prepare("UPDATE loans SET status = 'terlambat' WHERE due_date < ? AND return_date IS NULL");
    $stmt->bind_param("s", $today);
    $stmt->execute();
}

// Mengembalikan buku
function returnBook($loanId) {
    $conn = getDB();
    $today = date('Y-m-d');
    
    // Dapatkan info pinjaman
    $loan = getLoanById($loanId);
    if (!$loan) return false;
    
    // Hitung denda
    $fine = calculateFine($loan['due_date']);
    
    // Perbarui pinjaman
    $stmt = $conn->prepare("UPDATE loans SET return_date = ?, fine_amount = ?, status = 'dikembalikan' WHERE id = ?");
    $stmt->bind_param("sdi", $today, $fine, $loanId);
    $result = $stmt->execute();
    
    if ($result) {
        // tambah salinan yang tersedia
        updateBookAvailability($loan['book_id'], true);
    }
    
    return $result;
}

// Dapatkan semua pinjaman untuk admin
function getAllLoans($status = null) {
    $conn = getDB();
    $sql = "SELECT l.*, b.title as book_title, u.username, u.full_name 
            FROM loans l 
            JOIN books b ON l.book_id = b.id 
            JOIN users u ON l.user_id = u.id";
    
    if ($status) {
        $sql .= " WHERE l.status = '$status'";
    }
    
    $sql .= " ORDER BY l.borrow_date DESC";
    $result = $conn->query($sql);
    
    $loans = [];
    while ($row = $result->fetch_assoc()) {
        $loans[] = $row;
    }
    
    return $loans;
}

// Dapatkan semua user untuk admin
function getAllUsers($role = null) {
    $conn = getDB();
    $sql = "SELECT * FROM users";
    
    if ($role) {
        $sql .= " WHERE role = '$role'";
    }
    
    $sql .= " ORDER BY username";
    $result = $conn->query($sql);
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    return $users;
}
?>
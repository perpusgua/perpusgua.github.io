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

// Perbarui pinjaman yang sudah lewat
updateOverdueLoans();

// Get user ID
$userId = getCurrentUserId();

// Check if user has overdue books
$hasOverdue = hasOverdueBooks($userId);

// Get book ID if provided
$bookId = isset($_GET['book_id']) ? (int)$_GET['book_id'] : null;
$book = null;

if ($bookId) {
    $book = getBookById($bookId);
    
    // Check if book exists and is available
    if (!$book || $book['available_copies'] < 1) {
        $_SESSION['error_message'] = 'Buku tidak dapat dipinjam';
        redirect('books.php');
    }
}

// Process borrow request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow'])) {
    $bookId = (int)$_POST['book_id'];
    
    // Check if book is available
    if (!isBookAvailable($bookId)) {
        $_SESSION['error_message'] = 'Maaf, buku ini tidak dapat dipinjam';
    } 
    // Check if user has overdue books
    elseif ($hasOverdue) {
        $_SESSION['error_message'] = 'Anda memiliki buku yang sudah lewat jatuh tempo. Harap kembalikan sebelum meminjam buku baru';
    } 
    // Check if user has reached maximum allowed loans (5)
    else {
        $conn = getDB();
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM loans WHERE user_id = ? AND return_date IS NULL");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $currentLoans = $result->fetch_assoc()['count'];
        
        if ($currentLoans >= 5) {
            $_SESSION['error_message'] = 'Anda telah mencapai jumlah maksimum (5) buku yang dapat dipinjam sekaligus';
        } else {
            // Set borrow and due dates
            $borrowDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime('+14 days')); // 2 weeks loan period
            
            // Create loan record
            $stmt = $conn->prepare("INSERT INTO loans (user_id, book_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'borrowed')");
            $stmt->bind_param("iiss", $userId, $bookId, $borrowDate, $dueDate);
            
            if ($stmt->execute()) {
                // Update book availability
                updateBookAvailability($bookId);
                
                $_SESSION['success_message'] = 'Buku berhasil dipinjam. Tanggal jatuh tempo: ' . date('M d, Y', strtotime($dueDate));
                redirect('history.php');
            } else {
                $_SESSION['error_message'] = 'Error processing your request';
                redirect('borrow.php?book_id=' . $bookId);
            }
        }
    }
}

// Get user's current loans count
$conn = getDB();
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM loans WHERE user_id = ? AND return_date IS NULL");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$currentLoans = $result->fetch_assoc()['count'];

// Get categories
$categories = getCategories();

// Get filters
$category = isset($_GET['category']) ? clean($_GET['category']) : null;
$search = isset($_GET['search']) ? clean($_GET['search']) : null;

// Get available books based on filters
$conn = getDB();
$sql = "SELECT * FROM books WHERE available_copies > 0";

if ($category) {
    $sql .= " AND category = '" . $conn->real_escape_string($category) . "'";
}

if ($search) {
    $sql .= " AND (title LIKE '%" . $conn->real_escape_string($search) . "%' OR author LIKE '%" . $conn->real_escape_string($search) . "%')";
}

$sql .= " ORDER BY title";
$result = $conn->query($sql);

$books = [];
while ($row = $result->fetch_assoc()) {
    $books[] = $row;
}


$pageTitle = $book ? 'Borrow: ' . $book['title'] : 'Borrow Books';


require_once '../includes/header.php';
?>

<?php if ($book): ?>
<!-- Borrow Specific Book -->
<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="borrow.php">Pinjam Buku</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $book['title']; ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title"><?php echo $book['title']; ?></h1>
                <h6 class="card-subtitle mb-3 text-muted">Dari <?php echo $book['author']; ?></h6>

                <div class="mb-3">
                    <span class="badge bg-secondary"><?php echo $book['category']; ?></span>
                    <span class="badge bg-success">Tersedia (<?php echo $book['available_copies']; ?> salinan)</span>
                </div>

                <div class="mb-4">
                    <p class="card-text"><?php echo $book['description']; ?></p>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-bordered">
                        <tbody>
                            <tr>
                                <th width="150">ISBN</th>
                                <td><?php echo $book['isbn']; ?></td>
                            </tr>
                            <tr>
                                <th>Penerbit</th>
                                <td><?php echo $book['publisher']; ?></td>
                            </tr>
                            <tr>
                                <th>Tahun Penerbitan</th>
                                <td><?php echo $book['tahun_terbitan']; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <?php if ($hasOverdue): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> Anda memiliki buku yang sudah jatuh tempo. Harap
                    kembalikan sebelum meminjam buku baru.
                </div>
                <a href="history.php?status=terlambat" class="btn btn-warning">Lihat Buku yang Sudah Jatuh Tempo</a>
                <?php elseif ($currentLoans >= 5): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i> Anda telah mencapai jumlah maksimum buku (5)
                    yang dapat dipinjam sekaligus.
                </div>
                <a href="history.php" class="btn btn-warning">Lihat Pinjaman Saya</a>
                <?php else: ?>
                <form method="post" action="">
                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                    <div class="mb-3">
                        <label class="form-label">Jangka Waktu Pinjaman</label>
                        <p class="form-control-plaintext">14 Hari (standard)</p>
                        <small class="text-muted">Tanggal jatuh temponya adalah:
                            <?php echo date('M d, Y', strtotime('+14 days')); ?></small>
                    </div>
                    <button type="submit" name="borrow" class="btn btn-primary">Konfirmasi Peminjaman</button>
                    <a href="borrow.php" class="btn btn-outline-secondary ms-2">Cancel</a>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Informasi Pinjaman</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Jangka Waktu Pinjaman
                        <span>14 Hari</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Biaya Keterlambatan
                        <span><?php echo formatRupiah(FINE_PER_DAY); ?> per hari</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Pinjaman Anda Saat Ini</h5>
            </div>
            <div class="card-body">
                <div class="text-center">
                    <h2 class="mb-0"><?php echo $currentLoans; ?></h2>
                    <p class="text-muted">dari maksimal 5 yang diizinkan</p>

                    <div class="progress mb-3">
                        <div class="progress-bar bg-primary" role="progressbar"
                            style="width: <?php echo ($currentLoans / 5) * 100; ?>%"
                            aria-valuenow="<?php echo $currentLoans; ?>" aria-valuemin="0" aria-valuemax="5"></div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white">
                <a href="history.php" class="btn btn-sm btn-outline-primary">Lihat Pinjaman Saya</a>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Book Listing for Borrowing -->
<div class="row mb-4">
    <div class="col-md-8">
        <h1>Pinjam Buku</h1>
        <p class="lead">Pilih buku yang akan dipinjam dari koleksi kami yang sediakan</p>
    </div>
    <div class="col-md-4">
        <form action="" method="get" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search books..."
                value="<?php echo $search; ?>">
            <button type="submit" class="btn btn-primary">Cari</button>
        </form>
    </div>
</div>

<div class="row">
    <!-- Filters -->
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Kategori</h5>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <a href="borrow.php"
                        class="list-group-item list-group-item-action <?php echo !$category ? 'active' : ''; ?>">
                        Semua Kategori
                    </a>
                    <?php foreach ($categories as $cat): ?>
                    <a href="borrow.php?category=<?php echo urlencode($cat); ?>"
                        class="list-group-item list-group-item-action <?php echo $category === $cat ? 'active' : ''; ?>">
                        <?php echo $cat; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Your Status</h5>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Current Loans
                        <span class="badge bg-primary">
                            <?php echo $currentLoans; ?> / 5
                        </span>
                    </li>
                </ul>

                <?php if ($hasOverdue): ?>
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i> You have overdue books. Please return them before
                    borrowing new books.
                    <div class="mt-2">
                        <a href="history.php?status=terlambat" class="btn btn-sm btn-warning">View Overdue Books</a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($currentLoans >= 5): ?>
                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-circle me-2"></i> You have reached the maximum number of books that can
                    be borrowed at once.
                    <div class="mt-2">
                        <a href="history.php" class="btn btn-sm btn-info">Return Some Books</a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Books -->
    <div class="col-md-9">
        <?php if (count($books) > 0): ?>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php foreach ($books as $book): ?>
            <div class="col">
                <div class="card h-100">
                    <?php if($book['image_path']): ?>
                    <img src="<?php echo '../uploads/covers/' . $book['image_path']; ?>" class="card-img-top"
                        alt="<?php echo $book['title']; ?>" style="height: 220px; object-fit: cover;">
                    <?php else: ?>
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center"
                        style="height: 220px;">
                        <i class="fas fa-book fa-3x text-secondary"></i>
                    </div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $book['title']; ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">By <?php echo $book['author']; ?></h6>
                        <div class="mb-2">
                            <span class="badge bg-secondary"><?php echo $book['category']; ?></span>
                            <span class="badge bg-success">Tersedia (<?php echo $book['available_copies']; ?>)</span>
                        </div>
                        <p class="card-text"><?php echo substr($book['description'], 0, 80) . '...'; ?></p>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
                        <small class="text-muted">Published: <?php echo $book['tahun_terbitan']; ?></small>
                        <a href="borrow.php?book_id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary"
                            <?php echo ($hasOverdue || $currentLoans >= 5) ? 'disabled' : ''; ?>>
                            Borrow
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($hasOverdue || $currentLoans >= 5): ?>
        <div class="alert alert-warning mt-4">
            <?php if ($hasOverdue): ?>
            <i class="fas fa-exclamation-triangle me-2"></i> Borrowing is disabled because you have overdue books.
            <a href="history.php?status=terlambat" class="btn btn-sm btn-warning ms-2">View Overdue Books</a>
            <?php else: ?>
            <i class="fas fa-exclamation-circle me-2"></i> Borrowing is disabled because you have reached the maximum of
            5 books.
            <a href="history.php" class="btn btn-sm btn-info ms-2">Return Some Books</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php else: ?>
        <div class="alert alert-info">
            <p class="mb-0">No available books found. Try a different search or category.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php

require_once '../includes/footer.php';
?>
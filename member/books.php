<?php

require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';
require_once '../includes/bookmark_functions.php';

// Membutuhkan login untuk mengakses
requireLogin();

// Jangan izinkan admin mengakses area anggota
if (isAdmin()) {
    redirect('../admin/dashboard.php');
}

// Get categories
$categories = getCategories();

// Get filters
$category = isset($_GET['category']) ? clean($_GET['category']) : null;
$search = isset($_GET['search']) ? clean($_GET['search']) : null;

// Get buku berdasarkan filter
$books = getBooks($category, $search);

// Dapatkan detail buku tertentu jika ID disediakan
$bookDetails = null;
if (isset($_GET['id'])) {
    $bookId_get = (int)$_GET['id']; // Gunakan variabel berbeda untuk GET ID
    $bookDetails = getBookById($bookId_get);
}

// Proses permintaan pinjaman
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow'])) {
    $bookId_post = (int)$_POST['book_id']; // ID buku dari POST
    $userId = getCurrentUserId();
    
    // Ambil detail buku terbaru berdasarkan ID dari POST untuk validasi
    $currentBookDetailsForLoan = getBookById($bookId_post);

    if (!$currentBookDetailsForLoan) {
        $_SESSION['error_message'] = 'Buku tidak ditemukan.';
    }
    // Periksa apakah buku tersedia (menggunakan data terbaru)
    elseif ($currentBookDetailsForLoan['available_copies'] <= 0) {
        $_SESSION['error_message'] = 'Maaf, buku ini sudah tidak tersedia untuk dipinjam.';
    } 
    // Periksa apakah pengguna memiliki buku lain yang sudah lewat jatuh tempo
    elseif (hasOverdueBooks($userId)) {
        $_SESSION['error_message'] = 'Anda memiliki buku yang sudah lewat jatuh tempo. Harap kembalikan buku tersebut sebelum meminjam buku baru.';
    } 
    // Jika semua pemeriksaan awal lolos, periksa batas peminjaman untuk buku yang sama
    else {
        $conn = getDB();
        // Tentukan batas maksimal peminjaman untuk buku yang sama
        $max_borrows_same_book = 5;

        // Hitung berapa banyak salinan buku ini yang sedang dipinjam oleh pengguna
        $stmt_check_limit = $conn->prepare("SELECT COUNT(*) as count FROM loans WHERE user_id = ? AND book_id = ? AND (status = 'dipinjam' OR status = 'terlambat')");
        $stmt_check_limit->bind_param("ii", $userId, $bookId_post);
        $stmt_check_limit->execute();
        $result_limit = $stmt_check_limit->get_result()->fetch_assoc();
        $current_borrows_of_this_book = (int)$result_limit['count'];
        $stmt_check_limit->close();

        if ($current_borrows_of_this_book >= $max_borrows_same_book) {
            $_SESSION['error_message'] = 'Anda telah mencapai batas maksimal peminjaman (' . $max_borrows_same_book . ' salinan) untuk buku yang sama.';
        } else {
            // Semua pemeriksaan lolos, proses peminjaman
            $borrowDate = date('Y-m-d');
            $dueDate = date('Y-m-d', strtotime('+14 days')); // Periode pinjaman 2 minggu
            
            // Buat catatan pinjaman baru
            $stmt_insert_loan = $conn->prepare("INSERT INTO loans (user_id, book_id, borrow_date, due_date, status) VALUES (?, ?, ?, ?, 'dipinjam')");
            $stmt_insert_loan->bind_param("iiss", $userId, $bookId_post, $borrowDate, $dueDate);
            
            if ($stmt_insert_loan->execute()) {
                // Perbarui ketersediaan buku
                updateBookAvailability($bookId_post);
                
                $_SESSION['success_message'] = 'Buku berhasil dipinjam. Tanggal jatuh tempo: ' . date('M d, Y', strtotime($dueDate));
                redirect('history.php'); // Redirect ke halaman riwayat setelah sukses
            } else {
                $_SESSION['error_message'] = 'Terjadi kesalahan saat memproses permintaan peminjaman Anda.';
            }
            $stmt_insert_loan->close();
        }
    }
    
    // Redirect kembali ke halaman buku jika ada error atau kondisi tidak terpenuhi
    // (Peminjaman sukses sudah redirect ke history.php)
    $redirect_url_after_post = 'books.php'; // Default redirect
    if (isset($_GET['id'])) { // Jika user berada di halaman detail buku saat POST
        $redirect_url_after_post = 'books.php?id=' . (int)$_GET['id'];
    } elseif (isset($_POST['book_id'])) { // Fallback jika $_GET['id'] tidak ada tapi book_id ada di POST
         $redirect_url_after_post = 'books.php?id=' . (int)$_POST['book_id'];
    }
    redirect($redirect_url_after_post);
}


$pageTitle = $bookDetails ? htmlspecialchars($bookDetails['title']) : 'Cari Buku'; // Gunakan htmlspecialchars untuk keamanan


require_once '../includes/header.php';
?>

<?php if ($bookDetails): ?>
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="books.php" class="text-decoration-none"><i
                        class="fas fa-home me-1"></i>Buku</a></li>
            <li class="breadcrumb-item active fw-semibold" aria-current="page">
                <?php echo htmlspecialchars($bookDetails['title']); ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-4 mb-6">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <?php if($bookDetails['image_path']): ?>
                <img src="<?php echo '../uploads/covers/' . htmlspecialchars($bookDetails['image_path']); ?>"
                    alt="<?php echo htmlspecialchars($bookDetails['title']); ?>" class="card-img-top book-cover">
                <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center book-cover">
                    <i class="fas fa-book fa-5x text-secondary"></i>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row g-4">
                <div class="col-lg-8 mb-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-body p-4">
                            <div class="mb-2">
                                <span
                                    class="badge rounded-pill <?php echo $bookDetails['available_copies'] > 0 ? 'bg-success' : 'bg-danger'; ?> px-2 py-1">
                                    <i
                                        class="fas <?php echo $bookDetails['available_copies'] > 0 ? 'fa-check-circle' : 'fa-times-circle'; ?> me-1"></i>
                                    <?php echo $bookDetails['available_copies'] > 0 ? 'Tersedia' : 'Tidak Tersedia'; ?>
                                </span>
                            </div>

                            <h1 class="card-title fw-bold mb-2"><?php echo htmlspecialchars($bookDetails['title']); ?>
                            </h1>
                            <p class="card-subtitle text-muted fs-5 mb-3">By
                                <?php echo htmlspecialchars($bookDetails['author']); ?></p>

                            <div class="d-flex mb-3 flex-wrap gap-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary fs-6 px-3 py-2">
                                    <i
                                        class="fas fa-tag me-1"></i><?php echo htmlspecialchars($bookDetails['category']); ?>
                                </span>
                                <?php if($bookDetails['tahun_terbitan']): ?>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary fs-6 px-3 py-2">
                                    <i
                                        class="fas fa-calendar me-1"></i><?php echo htmlspecialchars($bookDetails['tahun_terbitan']); ?>
                                </span>
                                <?php endif; ?>
                            </div>

                            <div class="mb-4">
                                <h5 class="fw-bold mb-3">Deskripsi</h5>
                                <p class="card-text">
                                    <?php echo nl2br(htmlspecialchars($bookDetails['description'] ?? 'Tidak ada deskripsi untuk buku ini.')); ?>
                                </p>
                            </div>

                            <div class="book-details mb-4">
                                <h5 class="fw-bold mb-3">Informasi Detail</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped border">
                                        <tbody>
                                            <tr>
                                                <th class="bg-light" width="150">ISBN</th>
                                                <td><?php echo htmlspecialchars($bookDetails['isbn']); ?></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Penerbit</th>
                                                <td><?php echo htmlspecialchars($bookDetails['publisher']); ?></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Tahun Penerbitan</th>
                                                <td><?php echo htmlspecialchars($bookDetails['tahun_terbitan']); ?></td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">Salinan Tersedia</th>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <?php echo (int)$bookDetails['available_copies']; ?> /
                                                            <?php echo (int)$bookDetails['total_copies']; ?></div>
                                                        <div class="progress flex-grow-1" style="height: 8px;">
                                                            <div class="progress-bar <?php echo $bookDetails['available_copies'] > 0 ? 'bg-success' : 'bg-danger'; ?>"
                                                                role="progressbar"
                                                                style="width: <?php echo ($bookDetails['total_copies'] > 0 ? ((int)$bookDetails['available_copies'] / (int)$bookDetails['total_copies']) * 100 : 0); ?>%"
                                                                aria-valuenow="<?php echo (int)$bookDetails['available_copies']; ?>"
                                                                aria-valuemin="0"
                                                                aria-valuemax="<?php echo (int)$bookDetails['total_copies']; ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <?php if ($bookDetails['available_copies'] > 0): ?>
                            <form method="post"
                                action="books.php?id=<?php echo $bookDetails['id']; // Action ke halaman detail buku ini ?>"
                                class="d-flex gap-3 align-items-start flex-wrap">
                                <input type="hidden" name="book_id" value="<?php echo $bookDetails['id']; ?>">

                                <button type="submit" name="borrow"
                                    class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm"
                                    <?php echo hasOverdueBooks(getCurrentUserId()) ? 'disabled' : ''; ?>>
                                    <i class="fas fa-book me-2"></i>Pinjam Buku Ini
                                </button>

                                <?php 
                                $user_id_bookmark = getCurrentUserId(); // Gunakan var berbeda jika perlu
                                $book_id_bookmark = $bookDetails['id'];
                                $is_bookmarked = isBookmarked($user_id_bookmark, $book_id_bookmark);
                                ?>
                                <button id="bookmark-btn" type="button"
                                    class="btn <?php echo $is_bookmarked ? 'btn-warning' : 'btn-outline-warning'; ?> btn-lg rounded-pill px-4 shadow-sm"
                                    data-action="<?php echo $is_bookmarked ? 'remove' : 'add'; ?>"
                                    data-book-id="<?php echo $book_id_bookmark; ?>">
                                    <i class="<?php echo $is_bookmarked ? 'fas' : 'far'; ?> fa-bookmark me-2"></i>
                                    <?php echo $is_bookmarked ? 'Hapus Bookmark' : 'Bookmark'; ?>
                                </button>
                            </form>

                            <?php if (hasOverdueBooks(getCurrentUserId())): ?>
                            <div class="alert alert-danger d-flex align-items-center mt-3" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                <div>Anda memiliki buku yang sudah jatuh tempo. Tolong kembalikan sebelum meminjam buku
                                    baru.</div>
                            </div>
                            <?php endif; ?>
                            <?php else: ?>
                            <div class="d-flex gap-3 flex-wrap">
                                <button class="btn btn-secondary btn-lg rounded-pill px-4 shadow-sm" disabled>
                                    <i class="fas fa-times-circle me-2"></i>Buku Habis
                                </button>

                                <?php 
                                $user_id_bookmark_unavailable = getCurrentUserId();
                                $book_id_bookmark_unavailable = $bookDetails['id'];
                                $is_bookmarked_unavailable = isBookmarked($user_id_bookmark_unavailable, $book_id_bookmark_unavailable);
                                ?>
                                <button id="bookmark-btn" type="button"
                                    class="btn <?php echo $is_bookmarked_unavailable ? 'btn-warning' : 'btn-outline-warning'; ?> btn-lg rounded-pill px-4 shadow-sm"
                                    data-action="<?php echo $is_bookmarked_unavailable ? 'remove' : 'add'; ?>"
                                    data-book-id="<?php echo $book_id_bookmark_unavailable; ?>">
                                    <i
                                        class="<?php echo $is_bookmarked_unavailable ? 'fas' : 'far'; ?> fa-bookmark me-2"></i>
                                    <?php echo $is_bookmarked_unavailable ? 'Hapus Bookmark' : 'Bookmark'; ?>
                                </button>
                            </div>
                            <?php endif; ?>


                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-header bg-light py-3 border-0">
                            <h5 class="mb-0 fw-bold">Selengkapnya di Kategori ini</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush rounded-bottom">
                                <?php 
                                $relatedBooks = getBooks($bookDetails['category']);
                                $related_count = 0; // Ganti nama variabel agar tidak konflik
                                foreach ($relatedBooks as $related_book_item) { // Ganti nama variabel
                                    if ($related_book_item['id'] != $bookDetails['id'] && $related_count < 5) {
                                        echo '<a href="books.php?id=' . $related_book_item['id'] . '" class="list-group-item list-group-item-action border-0 py-3 px-4">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1 text-truncate">' . htmlspecialchars($related_book_item['title']) . '</h6>
                                                <small><i class="fas fa-chevron-right"></i></small>
                                            </div>
                                            <small class="text-muted">' . htmlspecialchars($related_book_item['author']) . '</small>
                                        </a>';
                                        $related_count++;
                                    }
                                }
                                
                                if ($related_count === 0) {
                                    echo '<div class="p-4 text-center text-muted">Tidak Ada Buku Lain Di Kategori Ini</div>';
                                }
                                ?>
                            </div>
                        </div>
                        <?php if ($related_count > 0): ?>
                        <div class="card-footer bg-white border-0 p-3">
                            <a href="books.php?category=<?php echo urlencode($bookDetails['category']); ?>"
                                class="btn btn-sm btn-outline-primary rounded-pill w-100">
                                <i class="fas fa-list me-2"></i>Lihat semua di
                                <?php echo htmlspecialchars($bookDetails['category']); ?>
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-light py-3 border-0">
                            <h5 class="mb-0 fw-bold">Informasi Pinjaman</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li
                                    class="list-group-item border-0 py-3 px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                                        Jangka Waktu Pinjaman
                                    </div>
                                    <span class="badge bg-light text-dark px-3 py-2 rounded-pill">14 Hari</span>
                                </li>
                                <li
                                    class="list-group-item border-0 py-3 px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-coins text-warning me-2"></i>
                                        Biaya Keterlambatan
                                    </div>
                                    <span
                                        class="badge bg-light text-dark px-3 py-2 rounded-pill"><?php echo function_exists('formatRupiah') ? formatRupiah(defined('FINE_PER_DAY') ? FINE_PER_DAY : 0) : (defined('FINE_PER_DAY') ? FINE_PER_DAY : 0); ?>
                                        per hari</span>
                                </li>
                                <li
                                    class="list-group-item border-0 py-3 px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <i class="fas fa-copy text-info me-2"></i>
                                        Max Pinjam Buku (yang sama)
                                    </div>
                                    <span class="badge bg-light text-dark px-3 py-2 rounded-pill">5 Buku</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<div class="container py-4">
    <div class="row align-items-center mb-4">
        <div class="col-lg-6">
            <h1 class="fw-bold mb-0"><i class="fas fa-books me-2 text-primary"></i>Cari Buku</h1>
            <p class="text-muted lead">Telusuri koleksi perpustakaan kami</p>
        </div>
        <div class="col-lg-6">
            <form action="books.php" method="get" class="d-flex">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0 py-2"
                        placeholder="Judul buku, penulis, atau kata kunci..."
                        value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    <button type="submit" class="btn btn-primary px-4 rounded-end">Cari</button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($category || $search): ?>
    <div class="mb-4">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="text-muted">Filter:</span>

            <?php if ($category): ?>
            <div class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 d-flex align-items-center">
                <i class="fas fa-tag me-2"></i>
                <span><?php echo htmlspecialchars($category); ?></span>
                <a href="<?php echo $search ? 'books.php?search=' . urlencode($search) : 'books.php'; ?>"
                    class="ms-2 text-primary text-decoration-none">
                    <i class="fas fa-times-circle"></i>
                </a>
            </div>
            <?php endif; ?>

            <?php if ($search): ?>
            <div class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 d-flex align-items-center">
                <i class="fas fa-search me-2"></i>
                <span>"<?php echo htmlspecialchars($search); ?>"</span>
                <a href="<?php echo $category ? 'books.php?category=' . urlencode($category) : 'books.php'; ?>"
                    class="ms-2 text-primary text-decoration-none">
                    <i class="fas fa-times-circle"></i>
                </a>
            </div>
            <?php endif; ?>

            <a href="books.php" class="ms-auto text-decoration-none">
                <i class="fas fa-times me-1"></i>Reset Semua Filter
            </a>
        </div>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-light py-3 border-0">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-tag me-2 text-primary"></i>Kategori</h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="books.php"
                            class="list-group-item list-group-item-action <?php echo !$category ? 'active' : ''; ?> py-3 px-4 border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-layer-group me-2"></i>
                                    Semua Kategori
                                </div>
                                <span
                                    class="badge rounded-pill bg-<?php echo !$category ? 'white text-primary' : 'primary bg-opacity-75'; ?>">
                                    <?php echo count(getBooks(null, null)); // Hitung semua buku tanpa filter ?>
                                </span>
                            </div>
                        </a>
                        <?php foreach ($categories as $cat_item): // Ganti nama var 
                            $cat_item_count = count(getBooks($cat_item, null)); // Hitung buku per kategori
                        ?>
                        <a href="books.php?category=<?php echo urlencode($cat_item); ?>"
                            class="list-group-item list-group-item-action <?php echo $category === $cat_item ? 'active' : ''; ?> py-3 px-4 border-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-bookmark me-2"></i>
                                    <?php echo htmlspecialchars($cat_item); ?>
                                </div>
                                <span
                                    class="badge rounded-pill bg-<?php echo $category === $cat_item ? 'white text-primary' : 'primary bg-opacity-75'; ?>">
                                    <?php echo $cat_item_count; ?>
                                </span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <?php if (count($books) > 0): ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                <?php foreach ($books as $book_item): // Ganti nama var ?>
                <div class="col">
                    <div class="card h-100 border-0 shadow-sm rounded-4 book-card">
                        <div class="position-relative">

                            <?php if(isset($book_item['image_path']) && $book_item['image_path']): ?>
                            <img src="<?php echo '../uploads/covers/' . htmlspecialchars($book_item['image_path']); ?>"
                                class="card-img-top rounded-top-4"
                                alt="<?php echo htmlspecialchars($book_item['title']); ?>"
                                style="height: 260px; object-fit: cover;">
                            <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center rounded-top-4"
                                style="height: 260px;">
                                <i class="fas fa-book fa-4x text-secondary"></i>
                            </div>
                            <?php endif; ?>
                        </div>

                        <div class="card-body">
                            <div class="mb-2">
                                <?php if($book_item['available_copies'] > 0): ?>
                                <span class="badge bg-success rounded-pill px-2 py-1">
                                    <i class="fas fa-check-circle me-1"></i>Tersedia
                                </span>
                                <?php else: ?>
                                <span class="badge bg-danger rounded-pill px-2 py-1">
                                    <i class="fas fa-times-circle me-1"></i>Tidak Tersedia
                                </span>
                                <?php endif; ?>
                            </div>

                            <h5 class="card-title fw-bold"><?php echo htmlspecialchars($book_item['title']); ?></h5>
                            <p class="card-subtitle mb-2 text-muted">Dari
                                <?php echo htmlspecialchars($book_item['author']); ?></p>

                            <div class="mb-2">
                                <span class="badge bg-primary bg-opacity-10 text-primary">
                                    <i
                                        class="fas fa-tag me-1"></i><?php echo htmlspecialchars($book_item['category']); ?>
                                </span>
                            </div>

                            <p class="card-text text-muted small">
                                <?php echo htmlspecialchars(substr($book_item['description'] ?? '', 0, 100)) . (strlen($book_item['description'] ?? '') > 100 ? '...' : 'Tidak ada deskripsi untuk buku ini.'); ?>
                            </p>
                        </div>

                        <div class="card-footer bg-white border-top-0 pt-0">
                            <div class="d-flex gap-1">
                                <a href="books.php?id=<?php echo $book_item['id']; ?>"
                                    class="btn btn-primary flex-grow-1 rounded-pill">
                                    <i class="fas fa-info-circle me-2"></i>Detail
                                </a>
                                <?php  
                                $user_id_grid = getCurrentUserId();
                                $is_bookmarked_grid = isBookmarked($user_id_grid, $book_item['id']);
                                ?>
                                <button
                                    class="btn <?php echo $is_bookmarked_grid ? 'btn-warning' : 'btn-outline-warning'; ?> rounded-pill bookmark-grid-btn"
                                    data-action="<?php echo $is_bookmarked_grid ? 'remove' : 'add'; ?>"
                                    data-book-id="<?php echo $book_item['id']; ?>">
                                    <i class="<?php echo $is_bookmarked_grid ? 'fas' : 'far'; ?> fa-bookmark"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="alert alert-info d-flex align-items-center shadow-sm rounded-4 p-4">
                <i class="fas fa-info-circle fa-2x me-3 text-primary"></i>
                <div>
                    <h5 class="alert-heading fw-bold mb-1">Tidak ada buku yang ditemukan</h5>
                    <p class="mb-0">Coba pencarian atau kategori lain untuk menemukan buku yang Anda cari.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.book-cover {
    height: 600px;
    width: 100%;
    object-fit: cover;
}

@media (max-width: 992px) {

    .book-cover {
        height: 450px;
    }
}

@media (max-width: 768px) {

    .book-cover {
        height: 350px;
    }
}


.book-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

.list-group-item.active {
    background-color: var(--bs-primary);
    color: white;
}

.list-group-item.active .badge {
    color: var(--bs-primary) !important;
    background-color: white !important;
}

.bookmark-grid-btn {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
}

.bookmark-grid-btn .fa-bookmark {
    margin-right: 0 !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function setupBookmarkButton(btn) {
        btn.addEventListener('click', function() {
            const bookId = this.getAttribute('data-book-id');
            let action = this.getAttribute('data-action'); // Gunakan let karena bisa berubah

            const formData = new FormData();
            formData.append('book_id', bookId);
            formData.append('action', action);

            const iconElement = this.querySelector('i.fa-bookmark');
            const isDetailButton = this.id === 'bookmark-btn'; // Cek apakah ini tombol halaman detail
            const originalText = isDetailButton ? (action === 'add' ? ' Bookmark' : ' Hapus Bookmark') :
                null;
            const processingText = isDetailButton ? (action === 'add' ? ' Menambah...' :
                ' Menghapus...') : null;

            // Optimistic UI update
            if (isDetailButton && processingText) {
                this.childNodes[1].nodeValue = processingText;
            }
            this.disabled = true;


            fetch('../includes/bookmark_action.php', { // Pastikan path ke bookmark_action.php benar
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    this.disabled = false; // Re-enable button
                    if (data.success) {
                        // Update tombol berdasarkan respons server aktual
                        if (data.new_action ===
                            'remove') { // Server bilang sekarang sudah di-bookmark
                            if (isDetailButton) this.childNodes[1].nodeValue = ' Hapus Bookmark';
                            this.classList.remove('btn-outline-warning');
                            this.classList.add('btn-warning');
                            this.setAttribute('data-action', 'remove');
                            if (iconElement) iconElement.classList.replace('far', 'fas');
                        } else { // Server bilang sekarang tidak di-bookmark
                            if (isDetailButton) this.childNodes[1].nodeValue = ' Bookmark';
                            this.classList.remove('btn-warning');
                            this.classList.add('btn-outline-warning');
                            this.setAttribute('data-action', 'add');
                            if (iconElement) iconElement.classList.replace('fas', 'far');
                        }
                    } else {
                        // Kembalikan UI jika server gagal
                        if (isDetailButton && originalText) this.childNodes[1].nodeValue =
                            originalText;
                        alert(data.message || 'Gagal memproses bookmark.');
                        // Kembalikan class dan atribut action jika gagal
                        if (action === 'add') {
                            this.classList.remove('btn-warning');
                            this.classList.add('btn-outline-warning');
                            if (iconElement) iconElement.classList.replace('fas', 'far');
                        } else {
                            this.classList.remove('btn-outline-warning');
                            this.classList.add('btn-warning');
                            if (iconElement) iconElement.classList.replace('far', 'fas');
                        }
                        this.setAttribute('data-action', action);
                    }
                })
                .catch(error => {
                    this.disabled = false; // Re-enable button
                    if (isDetailButton && originalText) this.childNodes[1].nodeValue = originalText;
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses bookmark.');
                    // Kembalikan UI jika ada error jaringan
                    if (action === 'add') {
                        this.classList.remove('btn-warning');
                        this.classList.add('btn-outline-warning');
                        if (iconElement) iconElement.classList.replace('fas', 'far');
                    } else {
                        this.classList.remove('btn-outline-warning');
                        this.classList.add('btn-warning');
                        if (iconElement) iconElement.classList.replace('far', 'fas');
                    }
                    this.setAttribute('data-action', action);
                });
        });
    }

    const detailBookmarkBtn = document.getElementById('bookmark-btn');
    if (detailBookmarkBtn) {
        setupBookmarkButton(detailBookmarkBtn);
    }

    document.querySelectorAll('.bookmark-grid-btn').forEach(gridBtn => {
        setupBookmarkButton(gridBtn);
    });
});
</script>

<?php

require_once '../includes/footer.php';
?>
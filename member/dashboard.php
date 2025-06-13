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

// Get user loans
$activeLoans = getUserLoans($userId, 'dipinjam');
$overdueLoans = getUserLoans($userId, 'terlambat');
$completedLoans = getUserLoans($userId, 'dikembalikan');

// Calculate statistics
$totalLoans = count($activeLoans) + count($overdueLoans) + count($completedLoans);


$pageTitle = 'Member Dashboard';


require_once '../includes/header.php';
?>

<!-- Dashboard Header -->
<div class="bg-light py-4 mb-5 rounded-4 shadow-sm">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-5 fw-bold mb-1">Welkam welkam, <?php echo $user['full_name']; ?></h1>
                <p class="lead text-muted mb-0">Mengelola akun perpustakaan dan barang yang dipinjam</p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="books.php" class="btn btn-primary rounded-pill me-2 px-4">
                    <i class="fas fa-search me-2"></i>Cari Buku
                </a>
                <a href="profile.php" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="fas fa-user me-2"></i>Profile Saya
                </a>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <!-- Summary Cards -->
    <div class="row g-4 mb-5">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 stats-card">
                <div class="card-body position-relative p-4">
                    <div
                        class="stats-icon position-absolute top-0 end-0 m-3 p-2 rounded-circle bg-primary bg-opacity-10">
                        <i class="fas fa-book-open text-primary fa-fw fa-lg"></i>
                    </div>
                    <h6 class="text-muted mb-2">Total Pinjaman</h6>
                    <h2 class="display-5 fw-bold mb-0"><?php echo $totalLoans; ?></h2>
                    <p class="text-muted mb-0 small">Sepanjang waktu</p>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 stats-card">
                <div class="card-body position-relative p-4">
                    <div
                        class="stats-icon position-absolute top-0 end-0 m-3 p-2 rounded-circle bg-primary bg-opacity-10">
                        <i class="fas fa-sync-alt text-primary fa-fw fa-lg"></i>
                    </div>
                    <h6 class="text-muted mb-2">Pinjaman Aktif</h6>
                    <h2 class="display-5 fw-bold mb-0"><?php echo count($activeLoans); ?></h2>
                    <p class="text-muted mb-0 small">Buku yang dipinjam</p>
                </div>
                <div class="card-footer bg-transparent border-0 p-3">
                    <a href="history.php?status=dipinjam" class="btn btn-sm btn-outline-primary rounded-pill w-100">
                        <i class="fas fa-eye me-2"></i>Lihat Detail
                    </a>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 stats-card">
                <div class="card-body position-relative p-4">
                    <div
                        class="stats-icon position-absolute top-0 end-0 m-3 p-2 rounded-circle bg-danger bg-opacity-10">
                        <i class="fas fa-exclamation-circle text-danger fa-fw fa-lg"></i>
                    </div>
                    <h6 class="text-muted mb-2">Terlambat</h6>
                    <h2 class="display-5 fw-bold mb-0 <?php echo count($overdueLoans) > 0 ? 'text-danger' : ''; ?>">
                        <?php echo count($overdueLoans); ?></h2>
                    <p class="text-muted mb-0 small">Buku melewati tenggat</p>
                </div>
                <?php if (count($overdueLoans) > 0): ?>
                <div class="card-footer bg-transparent border-0 p-3">
                    <a href="history.php?status=terlambat" class="btn btn-sm btn-danger rounded-pill w-100">
                        <i class="fas fa-exclamation-triangle me-2"></i>Kembalikan Sekarang
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 stats-card">
                <div class="card-body position-relative p-4">
                    <div
                        class="stats-icon position-absolute top-0 end-0 m-3 p-2 rounded-circle bg-success bg-opacity-10">
                        <i class="fas fa-check-circle text-success fa-fw fa-lg"></i>
                    </div>
                    <h6 class="text-muted mb-2">Dikembalikan</h6>
                    <h2 class="display-5 fw-bold mb-0"><?php echo count($completedLoans); ?></h2>
                    <p class="text-muted mb-0 small">Pinjaman selesai</p>
                </div>
                <div class="card-footer bg-transparent border-0 p-3">
                    <a href="history.php?status=dikembalikan" class="btn btn-sm btn-outline-primary rounded-pill w-100">
                        <i class="fas fa-history me-2"></i>Liat Riwayat
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Recent Loans - Now wider (col-lg-9 instead of col-lg-8) -->
        <div class="col-lg-9 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="fas fa-history me-2 text-primary"></i>Pinjaman Terbaru
                        </h5>
                        <a href="history.php" class="btn btn-sm btn-outline-primary rounded-pill">
                            <i class="fas fa-clock me-2"></i>Lihat Semua
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php 
                    $recentLoans = getUserLoans($userId);
                    if (count($recentLoans) > 0):
                    ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="px-4 py-3">Judul Buku</th>
                                    <th class="px-4 py-3">Tgl Pinjaman</th>
                                    <th class="px-4 py-3">Tgl Jatuh Tempo</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3 text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($recentLoans, 0, 5) as $loan): ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <?php 
                                            $bookDetails = getBookById($loan['book_id']);
                                            $coverImage = isset($bookDetails['image_path']) ? $bookDetails['image_path'] : null;
                                            ?>
                                            <?php if($coverImage): ?>
                                            <div class="book-thumbnail me-3">
                                                <img src="<?php echo '../uploads/covers/' . $coverImage; ?>"
                                                    alt="<?php echo $loan['book_title']; ?>" width="40" height="60"
                                                    class="rounded shadow-sm" style="object-fit: cover;">
                                            </div>
                                            <?php else: ?>
                                            <div class="book-thumbnail me-3 bg-light rounded shadow-sm d-flex align-items-center justify-content-center"
                                                style="width: 40px; height: 60px;">
                                                <i class="fas fa-book text-secondary"></i>
                                            </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold"><?php echo $loan['book_title']; ?></div>
                                                <small
                                                    class="text-muted"><?php echo isset($bookDetails['author']) ? $bookDetails['author'] : ''; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3"><?php echo date('d M Y', strtotime($loan['borrow_date'])); ?>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php 
                                        $dueDate = strtotime($loan['due_date']);
                                        $today = strtotime('today');
                                        $daysLeft = ceil(($dueDate - $today) / (60 * 60 * 24));
                                        ?>

                                        <div class="d-flex align-items-center">
                                            <span class="me-2"><?php echo date('d M Y', $dueDate); ?></span>

                                            <?php if($loan['status'] === 'dipinjam' && $daysLeft > 0): ?>
                                            <span class="badge bg-info rounded-pill">
                                                <?php echo $daysLeft; ?> days left
                                            </span>
                                            <?php elseif($loan['status'] === 'dipinjam' && $daysLeft == 0): ?>
                                            <span class="badge bg-warning rounded-pill">
                                                Due today
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <?php if($loan['status'] === 'dikembalikan'): ?>
                                        <span class="badge bg-success rounded-pill px-3 py-2">
                                            <i class="fas fa-check-circle me-1"></i>Dikembalikan
                                        </span>
                                        <?php elseif($loan['status'] === 'terlambat'): ?>
                                        <span class="badge bg-danger rounded-pill px-3 py-2">
                                            <i class="fas fa-exclamation-circle me-1"></i>Terlambat
                                        </span>
                                        <?php else: ?>
                                        <span class="badge bg-primary rounded-pill px-3 py-2">
                                            <i class="fas fa-book me-1"></i>Borrowed
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-end">
                                        <a href="books.php?id=<?php echo $loan['book_id']; ?>"
                                            class="btn btn-sm btn-outline-primary rounded-pill">
                                            <i class="fas fa-info-circle"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <div class="text-center py-5">
                        <img src="../assets/images/empty-box.png" alt="No loans" class="img-fluid mb-3"
                            style="max-width: 200px; opacity: 0.7;">
                        <h5>You haven't borrowed any books yet</h5>
                        <p class="text-muted">Start exploring our collection and borrow books today!</p>
                        <a href="books.php" class="btn btn-primary rounded-pill px-4 mt-2">
                            <i class="fas fa-search me-2"></i>Browse Books
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Quick Access - Now narrower (col-lg-3 instead of col-lg-4) -->
        <div class="col-lg-3 mb-4">
            <!-- Quick Links -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title fw-bold mb-0">
                        <i class="fas fa-bolt me-2 text-primary"></i>Quick Links
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush rounded-bottom">
                        <a href="books.php" class="list-group-item list-group-item-action p-3 border-0">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon bg-primary bg-opacity-10 me-2 p-2 rounded-circle">
                                    <i class="fas fa-book text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Cari Buku</h6>
                                    <p class="mb-0 text-muted small d-none d-md-block">Browse collection</p>
                                </div>
                            </div>
                        </a>
                        <a href="history.php" class="list-group-item list-group-item-action p-3 border-0">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon bg-primary bg-opacity-10 me-2 p-2 rounded-circle">
                                    <i class="fas fa-history text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Riwayat</h6>
                                    <p class="mb-0 text-muted small d-none d-md-block">Borrowing history</p>
                                </div>
                            </div>
                        </a>
                        <a href="profile.php" class="list-group-item list-group-item-action p-3 border-0">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon bg-primary bg-opacity-10 me-2 p-2 rounded-circle">
                                    <i class="fas fa-user text-primary"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Profile</h6>
                                    <p class="mb-0 text-muted small d-none d-md-block">Account details</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <?php if (hasOverdueBooks($userId)): ?>
            <!-- Overdue Alertttttttttttttt -->
            <div class="card border-0 shadow-sm rounded-3 border-start border-danger border-5 mb-4">
                <div class="card-body p-3">
                    <div class="d-flex">
                        <div class="me-2">
                            <i class="fas fa-exclamation-triangle text-danger fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="card-title text-danger fw-bold mb-1">Buku Terlambat!</h6>
                            <p class="card-text small mb-2">Anda memiliki <?php echo count(value: $overdueLoans); ?>
                                buku yang
                                sudah
                                melewati tenggat.</p>
                            <a href="history.php?status=terlambat" class="btn btn-sm btn-danger rounded-pill w-100">
                                <i class="fas fa-undo me-1"></i>Kembalikan
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Custom ceeses -->
<style>
.stats-card {
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.stats-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.feature-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>

<?php
require_once '../includes/footer.php';
?>
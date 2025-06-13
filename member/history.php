<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Membutuhkan login untuk mengakses
requireLogin();

// Jangan izinkan admin mengakses area anggota
if (isAdmin()) {
    redirect('../admin/dashboard.php');
}

// Perbarui pinjaman yang sudah lewat
updateOverdueLoans();

// Dapatkan ID user
$userId = getCurrentUserId();

// Get status filter
$status = isset($_GET['status']) ? clean($_GET['status']) : null;
$validStatuses = ['dipinjam', 'dikembalikan', 'terlambat'];
if ($status && !in_array($status, $validStatuses)) {
    $status = null;
}

// Dapatkan pinjaman user berdasarkan status
$loans = getUserLoans($userId, $status);

// Proses Pengembalian Buku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_book'])) {
    $loanId = (int)$_POST['loan_id'];
    
    // Periksa apakah pinjaman milik pengguna
    $loan = getLoanById($loanId);
    if ($loan && $loan['user_id'] == $userId) {
        if (returnBook($loanId)) {
            $_SESSION['success_message'] = 'Buku berhasil Dikembalikan';
        } else {
            $_SESSION['error_message'] = 'Error returning book';
        }
    } else {
        $_SESSION['error_message'] = 'Invalid loan';
    }
    
    // Redirect untuk menghindari refres
    redirect('history.php' . ($status ? "?status=$status" : ''));
}


$pageTitle = 'Loan History';


require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1>Pinjaman Saya</h1>
    </div>
    <div class="col-md-4 text-end">
        <div class="btn-group" role="group">
            <a href="history.php" class="btn btn-outline-primary <?php echo !$status ? 'active' : ''; ?>">Semua</a>
            <a href="history.php?status=dipinjam"
                class="btn btn-outline-primary <?php echo $status === 'dipinjam' ? 'active' : ''; ?>">Saat Ini</a>
            <a href="history.php?status=dikembalikan"
                class="btn btn-outline-primary <?php echo $status === 'dikembalikan' ? 'active' : ''; ?>">Dikembalikan</a>
            <a href="history.php?status=terlambat"
                class="btn btn-outline-primary <?php echo $status === 'terlambat' ? 'active' : ''; ?>">Terlambat</a>
        </div>
    </div>
</div>

<?php if (count($loans) > 0): ?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Judul Buku</th>
                        <th>Tgl Dipinjam</th>
                        <th>Tgl Jatuh Tempo</th>
                        <th>Tgl Dikembalikan</th>
                        <th>Denda</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td><?php echo $loan['book_title']; ?></td>
                        <td><?php echo date('M d, Y', strtotime($loan['borrow_date'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($loan['due_date'])); ?></td>
                        <td>
                            <?php echo $loan['return_date'] ? date('M d, Y', strtotime($loan['return_date'])) : '—'; ?>
                        </td>
                        <td>
                            <?php 
                                    if ($loan['status'] === 'dikembalikan' && $loan['fine_amount'] > 0) {
                                        echo formatRupiah($loan['fine_amount']);
                                    } elseif ($loan['status'] === 'terlambat') {
                                        echo formatRupiah(calculateFine($loan['due_date']));
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                        </td>
                        <td>
                            <span class="badge bg-<?php 
                                        if ($loan['status'] === 'dikembalikan') echo 'success';
                                        elseif ($loan['status'] === 'terlambat') echo 'danger';
                                        else echo 'primary';
                                    ?>">
                                <?php echo ucfirst($loan['status']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($loan['status'] !== 'dikembalikan'): ?>
                            <form method="post" action="" class="d-inline">
                                <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                <button type="submit" name="return_book" class="btn btn-sm btn-primary">
                                    Kembalikan Buku
                                </button>
                            </form>
                            <?php else: ?>
                            —
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="alert alert-info">
    <p class="mb-0">Tidak ada catatan pinjaman yang ditemukan.</p>
</div>
<?php endif; ?>

<div class="card mt-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Informasi Pinjaman</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Masa Pinjaman
                        <span>14 Hari</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Biaya Keterlambatan
                        <span><?php echo formatRupiah(FINE_PER_DAY); ?> per hari</span>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Maximal Buku (yang sama)
                        <span>5 Sekaligus</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php

require_once '../includes/footer.php';
?>
<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Membutuhkan hak admin
requireAdmin();

// Perbarui pinjaman yang sudah lewat
updateOverdueLoans();

$conn = getDB(); // dapatkan koneksi database

$status = isset($_GET['status']) ? clean($_GET['status']) : null;
$userId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

// Inisialisasi variabel user
$user = null;

// Dapatkan pinjaman berdasarkan filter
if ($userId) {
    // Dapatkan detail user
    $user = getUserById($userId);
    
    // Dapatkan pinjaman untuk user tertentu
    $loans = getUserLoans($userId, $status);
} else {
    // Dapatkan semua pinjaman
    $loans = getAllLoans($status);
}

// Memproses tindakan pinjaman
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['return_book'])) {
        $loanId = (int)$_POST['loan_id'];
        
        if (returnBook($loanId)) {
            $_SESSION['success_message'] = 'Buku berhasil Dikembalikan';
        } else {
            $_SESSION['error_message'] = 'Error returning book';
        }
        
        // Redirect ke halaman pinjaman dengan status dan user_id yang sesuai
        $redirectUrl = 'loans.php';
        $queryParams = [];
        if ($status) {
            $queryParams['status'] = $status;
        }
        if ($userId) {
            $queryParams['user_id'] = $userId;
        }
        if (!empty($queryParams)) {
            $redirectUrl .= '?' . http_build_query($queryParams);
        }
        redirect($redirectUrl);
    }
}

$pageTitle = $user ? 'Loans: ' . htmlspecialchars($user['full_name']) : 'Manage Loans';

require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><?php echo $pageTitle; ?></h1>
    </div>
    <div class="col-md-4 text-end">
        <?php if ($userId): ?>
        <a href="members.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Members
        </a>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="btn-group" role="group">
            <a href="loans.php<?php echo $userId ? "?user_id=$userId" : ''; ?>"
                class="btn btn-outline-primary <?php echo !$status ? 'active' : ''; ?>">All Loans</a>
            <a href="loans.php?status=dipinjam<?php echo $userId ? "&user_id=$userId" : ''; ?>"
                class="btn btn-outline-primary <?php echo $status === 'dipinjam' ? 'active' : ''; ?>">Dipinjam</a>
            <a href="loans.php?status=dikembalikan<?php echo $userId ? "&user_id=$userId" : ''; ?>"
                class="btn btn-outline-primary <?php echo $status === 'dikembalikan' ? 'active' : ''; ?>">Dikembalikan</a>
            <a href="loans.php?status=terlambat<?php echo $userId ? "&user_id=$userId" : ''; ?>"
                class="btn btn-outline-primary <?php echo $status === 'terlambat' ? 'active' : ''; ?>">Terlambat</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (count($loans) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <?php if (!$userId): ?>
                        <th>Member</th>
                        <?php endif; ?>
                        <th>Borrow Date</th>
                        <th>Due Date</th>
                        <th>Return Date</th>
                        <th>Fine</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($loans as $loan): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                        <?php if (!$userId): ?>
                        <td><a
                                href="loans.php?user_id=<?php echo $loan['user_id']; ?>"><?php echo htmlspecialchars($loan['full_name']); ?></a>
                        </td>
                        <?php endif; ?>
                        <td><?php echo date('M d, Y', strtotime($loan['borrow_date'])); ?></td>
                        <td><?php echo date('M d, Y', strtotime($loan['due_date'])); ?></td>
                        <td>
                            <?php echo $loan['return_date'] ? date('M d, Y', strtotime($loan['return_date'])) : '—'; ?>
                        </td>
                        <td>
                            <?php 
                                    if ($loan['status'] === 'dikembalikan' && ($loan['fine_amount'] ?: 0) > 0) {
                                        echo formatRupiah($loan['fine_amount']);
                                    } elseif ($loan['status'] === 'terlambat') {
                                        // Dengan asumsi CalculateFine selalu mengembalikan angka atau 0
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
                                        else echo 'primary'; // dipinjam
                                    ?>">
                                <?php echo ucfirst(htmlspecialchars($loan['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($loan['status'] !== 'dikembalikan'): ?>
                            <form method="post" action="" class="d-inline">
                                <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                <button type="submit" name="return_book" class="btn btn-sm btn-primary">
                                    Return Book
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
        <?php else: ?>
        <div class="alert alert-info">
            <p class="mb-0">No loan records
                found<?php echo $status ? ' for the status: ' . htmlspecialchars($status) : ''; ?>.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($userId && $user): ?>
<div class="card mt-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">Member Information</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Username
                        <span><?php echo htmlspecialchars($user['username']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Full Name
                        <span><?php echo htmlspecialchars($user['full_name']); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Phone
                        <span><?php echo htmlspecialchars($user['phone'] ?: '—'); ?></span>
                    </li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Member Since
                        <span><?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Account Status
                        <span class="badge bg-<?php echo hasOverdueBooks($userId) ? 'danger' : 'success'; ?>">
                            <?php echo hasOverdueBooks($userId) ? 'Limited (Overdue Books)' : 'Good Standing'; ?>
                        </span>
                    </li>
                    <?php
                    // ambil total denda
                    $stmt = $conn->prepare("SELECT SUM(fine_amount) as total FROM loans WHERE user_id = ? AND status = 'dikembalikan'"); // Only sum fines for returned books
                    $stmt->bind_param("i", $userId);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $totalFinesPaid = $result->fetch_assoc()['total'] ?: 0;
                    ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total Fines Paid
                        <span><?php echo formatRupiah($totalFinesPaid); ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card-footer bg-white">
        <a href="members.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Members
        </a>
        <a href="loans.php?user_id=<?php echo $userId; ?>&status=terlambat"
            class="btn btn-outline-danger ms-2 <?php echo !hasOverdueBooks($userId) ? 'disabled' : ''; ?>">
            <i class="fas fa-exclamation-circle"></i> View Overdue Books
        </a>
    </div>
</div>
<?php endif; ?>

<?php if (!$userId): ?>
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">Loan Statistics</h5>
            </div>
            <div class="card-body">
                <?php
                // Ambil statistik pinjaman secara keseluruhan
                // Hanya menghitung denda untuk buku yang sudah dikembalikan
                // Menggunakan query untuk mendapatkan statistik pinjaman
                $loanStatsResult = $conn->query("SELECT 
                    COUNT(*) as total_loans,
                    SUM(CASE WHEN status = 'dipinjam' THEN 1 ELSE 0 END) as active_loans,
                    SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as overdue_loans,
                    SUM(CASE WHEN status = 'dikembalikan' THEN 1 ELSE 0 END) as returned_loans, 
                    SUM(CASE WHEN status = 'dikembalikan' THEN fine_amount ELSE 0 END) as total_fines_paid 
                    FROM loans"); // Hanya menghitung denda untuk buku yang sudah dikembalikan
                $loanStatsOverall = $loanStatsResult->fetch_assoc();
                ?>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Total Pinjaman</h6>
                                <h2><?php echo $loanStatsOverall['total_loans'] ?: 0; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Dipinjam</h6>
                                <h2><?php echo $loanStatsOverall['active_loans'] ?: 0; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-danger text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Terlambat</h6>
                                <h2><?php echo $loanStatsOverall['overdue_loans'] ?: 0; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <h6 class="card-title">Total Denda</h6>
                                <h2><?php echo formatRupiah($loanStatsOverall['total_fines_paid'] ?: 0); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>

<?php
require_once '../includes/footer.php';
?>
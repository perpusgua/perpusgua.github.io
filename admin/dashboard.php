<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Membutuhkan hak admin
requireAdmin();

// Perbarui pinjaman yang sudah lewat
updateOverdueLoans();

// Dapatkan statistik
$conn = getDB();

// Total member
$memberCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'member'")->fetch_assoc()['count'];
$memberCount = $memberCount ?: 0; // Pastikan tidak null
$memberCount = (int)$memberCount; // Pastikan menjadi integer

// Total buku
$bookCountResult = $conn->query("SELECT 
    COUNT(*) as total_books,
    SUM(total_copies) as total_copies,
    SUM(available_copies) as available_copies
    FROM books");
$bookStats = $bookCountResult->fetch_assoc();

// Statistik pinjaman
$loanStats = $conn->query("SELECT 
    COUNT(*) as total_loans,
    SUM(CASE WHEN status = 'dipinjam' THEN 1 ELSE 0 END) as active_loans,
    SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as overdue_loans,
    SUM(CASE WHEN return_date IS NOT NULL THEN 1 ELSE 0 END) as returned_loans
    FROM loans")->fetch_assoc();

// Dapatkan pinjaman baru
$recentLoans = $conn->query("SELECT l.id, l.borrow_date, l.status, b.title as book_title, u.username, u.full_name
    FROM loans l
    JOIN books b ON l.book_id = b.id
    JOIN users u ON l.user_id = u.id
    ORDER BY l.created_at DESC LIMIT 5");

// Dapatkan anggota baru
$recentMembers = $conn->query("SELECT id, username, full_name, created_at
    FROM users
    WHERE role = 'member'
    ORDER BY created_at DESC LIMIT 5");

$pageTitle = 'Admin Dashboard';

require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1>Admin Dashboard</h1>
    </div>
</div>

<div class="row">
    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-primary text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Members</h6>
                        <h1 class="display-4"><?php echo $memberCount; ?></h1>
                    </div>
                    <i class="fas fa-users fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="members.php" class="text-white">View details <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-success text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Buku</h6>
                        <h1 class="display-4"><?php echo $bookStats['total_books'] ?: 0; ?></h1>
                    </div>
                    <i class="fas fa-book fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="books.php" class="text-white">View details <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-info text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Dipinjam</h6>
                        <h1 class="display-4"><?php echo $loanStats['active_loans'] ?: 0; ?></h1>
                    </div>
                    <i class="fas fa-clipboard-list fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="loans.php?status=dipinjam" class="text-white">View details <i
                        class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-4">
        <div class="card bg-danger text-white h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase">Terlambat</h6>
                        <h1 class="display-4"><?php echo $loanStats['overdue_loans'] ?: 0; ?></h1>
                    </div>
                    <i class="fas fa-exclamation-circle fa-3x opacity-50"></i>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="loans.php?status=terlambat" class="text-white">View details <i
                        class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0">Book Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Total Copies</h6>
                                <h2><?php echo $bookStats['total_copies'] ?: 0; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Available Copies</h6>
                                <h2><?php echo $bookStats['available_copies'] ?: 0; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="height: 250px;" class="mt-4">
                    <canvas id="bookStatsChart"></canvas>
                </div>
            </div>
            <div class="card-footer bg-white">
                <a href="books.php" class="btn btn-sm btn-outline-primary">Manage Books</a>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0">Loan Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Total Pinjaman</h6>
                                <h2><?php echo $loanStats['total_loans'] ?: 0; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Return Rate</h6>
                                <h2><?php echo ($loanStats['total_loans'] ?: 0) > 0 ? round((($loanStats['returned_loans'] ?: 0) / ($loanStats['total_loans'] ?: 1)) * 100) : 0; ?>%
                                </h2>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="height: 250px;" class="mt-4">
                    <canvas id="loanStatsChart"></canvas>
                </div>
            </div>
            <div class="card-footer bg-white">
                <a href="loans.php" class="btn btn-sm btn-outline-primary">View All Loans</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0">Recent Loans</h5>
            </div>
            <div class="card-body">
                <?php if ($recentLoans->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Member</th>
                                <th>Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($loan = $recentLoans->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                                <td><?php echo htmlspecialchars($loan['full_name']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($loan['borrow_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                                if ($loan['status'] === 'dikembalikan') echo 'success';
                                                elseif ($loan['status'] === 'terlambat') echo 'danger';
                                                else echo 'primary';
                                            ?>">
                                        <?php echo ucfirst(htmlspecialchars($loan['status'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-center py-3">No loans found</p>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white">
                <a href="loans.php" class="btn btn-sm btn-outline-primary">View All Loans</a>
            </div>
        </div>
    </div>

    <div class="col-lg-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-light">
                <h5 class="mb-0">New Members</h5>
            </div>
            <div class="card-body">
                <?php if ($recentMembers->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Username</th>
                                <th>Joined</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($member = $recentMembers->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($member['username']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($member['created_at'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <p class="text-center py-3">No members found</p>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white">
                <a href="members.php" class="btn btn-sm btn-outline-primary">View All Members</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Pastikan elemen canvas ada sebelum mencoba membuat chart
    const bookChartCanvas = document.getElementById('bookStatsChart');
    if (bookChartCanvas) {
        const bookCtx = bookChartCanvas.getContext('2d');
        const bookStatsChart = new Chart(bookCtx, {
            type: 'pie',
            data: {
                labels: ['Available Copies', 'Borrowed Copies'],
                datasets: [{
                    data: [
                        <?php echo $bookStats['available_copies'] ?: 0; ?>,
                        <?php echo ($bookStats['total_copies'] ?: 0) - ($bookStats['available_copies'] ?: 0); ?>
                    ],
                    backgroundColor: ['#28a745', '#17a2b8'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    const loanChartCanvas = document.getElementById('loanStatsChart');
    if (loanChartCanvas) {
        const loanCtx = loanChartCanvas.getContext('2d');
        const loanStatsChart = new Chart(loanCtx, {
            type: 'pie',
            data: {
                labels: ['Dikembalikan', 'Dipinjam', 'Terlambat'],
                datasets: [{
                    data: [
                        <?php echo $loanStats['returned_loans'] ?: 0; ?>,
                        <?php echo $loanStats['active_loans'] ?: 0; ?>,
                        <?php echo $loanStats['overdue_loans'] ?: 0; ?>
                    ],
                    backgroundColor: ['#28a745', '#17a2b8', '#dc3545'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed !== null) {
                                    label += context.parsed;
                                }
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php
require_once '../includes/footer.php';
?>
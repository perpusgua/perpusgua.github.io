<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Require admin privileges
requireAdmin();

// Get database connection
$conn = getDB();


$pageTitle = 'Reports';


require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1>Library Reports</h1>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Loan Statistics</h5>
            </div>
            <div class="card-body">
                <?php
                // Get loan statistics
                $loanStats = $conn->query("SELECT 
                    COUNT(*) as total_loans,
                    SUM(CASE WHEN status = 'dipinjam' THEN 1 ELSE 0 END) as active_loans,
                    SUM(CASE WHEN status = 'terlambat' THEN 1 ELSE 0 END) as overdue_loans,
                    SUM(CASE WHEN return_date IS NOT NULL THEN 1 ELSE 0 END) as returned_loans,
                    SUM(fine_amount) as total_fines
                    FROM loans")->fetch_assoc();
                ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Total Pinjaman</h6>
                                <h2><?php echo $loanStats['total_loans']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Active Loans</h6>
                                <h2><?php echo $loanStats['active_loans']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Terlambat</h6>
                                <h2><?php echo $loanStats['overdue_loans']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Total Denda</h6>
                                <h2><?php echo formatRupiah($loanStats['total_fines']); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <canvas id="loanChart" width="400" height="200"></canvas>
            </div>
            <div class="card-footer bg-white">
                <a href="loans.php" class="btn btn-sm btn-outline-primary">View All Loans</a>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Book Statistics</h5>
            </div>
            <div class="card-body">
                <?php
                // Get book statistics
                $bookStats = $conn->query("SELECT 
                    COUNT(*) as total_books,
                    SUM(total_copies) as total_copies,
                    SUM(available_copies) as available_copies
                    FROM books")->fetch_assoc();
                
                // Get top categories
                $topCategories = $conn->query("SELECT 
                    category, COUNT(*) as count 
                    FROM books 
                    GROUP BY category 
                    ORDER BY count DESC 
                    LIMIT 5");
                ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Total Books</h6>
                                <h2><?php echo $bookStats['total_books']; ?></h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card bg-light">
                            <div class="card-body text-center">
                                <h6 class="card-title text-muted">Total Copies</h6>
                                <h2><?php echo $bookStats['total_copies']; ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="mt-4 mb-3">Top Categories</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Books</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($category = $topCategories->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $category['category']; ?></td>
                                <td><?php echo $category['count']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white">
                <a href="books.php" class="btn btn-sm btn-outline-primary">Manage Books</a>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Recent Activity</h5>
            </div>
            <div class="card-body">
                <?php
                // Get recent activity (loans, returns, etc.)
                $recentActivity = $conn->query("SELECT 
                    l.id, l.borrow_date, l.return_date, l.status, 
                    b.title as book_title, 
                    u.username, u.full_name
                    FROM loans l
                    JOIN books b ON l.book_id = b.id
                    JOIN users u ON l.user_id = u.id
                    ORDER BY 
                        CASE 
                            WHEN l.return_date IS NOT NULL THEN l.return_date 
                            ELSE l.borrow_date 
                        END DESC
                    LIMIT 10");
                ?>

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Activity</th>
                                <th>Book</th>
                                <th>Member</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($activity = $recentActivity->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <?php echo $activity['return_date'] 
                                            ? date('M d, Y', strtotime($activity['return_date']))
                                            : date('M d, Y', strtotime($activity['borrow_date'])); ?>
                                </td>
                                <td>
                                    <?php if ($activity['return_date']): ?>
                                    <span class="badge bg-success">Buku Dikembalikan</span>
                                    <?php else: ?>
                                    <span class="badge bg-primary">Buku Dipinjam</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $activity['book_title']; ?></td>
                                <td><?php echo $activity['full_name']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0">Buku yang Sudah Jatuh Tempo</h5>
            </div>
            <div class="card-body">
                <?php
                // Get overdue books
                $overdueBooks = $conn->query("SELECT 
                    l.id, l.borrow_date, l.due_date, 
                    b.title as book_title, b.author,
                    u.username, u.full_name
                    FROM loans l
                    JOIN books b ON l.book_id = b.id
                    JOIN users u ON l.user_id = u.id
                    WHERE l.status = 'terlambat'
                    ORDER BY l.due_date ASC
                    LIMIT 10");
                
                if ($overdueBooks->num_rows > 0):
                ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Book</th>
                                <th>Member</th>
                                <th>Due Date</th>
                                <th>Days Overdue</th>
                                <th>Fine</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($overdue = $overdueBooks->fetch_assoc()): 
                                $today = new DateTime();
                                $dueDate = new DateTime($overdue['due_date']);
                                $daysOverdue = $today->diff($dueDate)->days;
                                $fine = calculateFine($overdue['due_date']);
                            ?>
                            <tr>
                                <td><?php echo $overdue['book_title']; ?></td>
                                <td><?php echo $overdue['full_name']; ?></td>
                                <td><?php echo date('M d, Y', strtotime($overdue['due_date'])); ?></td>
                                <td><span class="text-danger"><?php echo $daysOverdue; ?> days</span></td>
                                <td>$<?php echo number_format($fine, 0); ?></td>
                                <td>
                                    <form method="post" action="loans.php" class="d-inline">
                                        <input type="hidden" name="loan_id" value="<?php echo $overdue['id']; ?>">
                                        <button type="submit" name="return_book" class="btn btn-sm btn-primary">
                                            Return Book
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-success">
                    <p class="mb-0">No overdue books at this time.</p>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer bg-white">
                <a href="loans.php?status=terlambat" class="btn btn-sm btn-outline-primary">View All Overdue Books</a>
            </div>
        </div>
    </div>
</div>

<!-- Inline JavaScript for Charts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Loan statistics chart
    const loanChartCtx = document.getElementById('loanChart').getContext('2d');
    const loanChart = new Chart(loanChartCtx, {
        type: 'pie',
        data: {
            labels: ['Active', 'terlambat', 'dikembalikan'],
            datasets: [{
                data: [
                    <?php echo $loanStats['active_loans']; ?>,
                    <?php echo $loanStats['overdue_loans']; ?>,
                    <?php echo $loanStats['returned_loans']; ?>
                ],
                backgroundColor: ['#17a2b8', '#dc3545', '#28a745'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
});
</script>

<?php

require_once '../includes/footer.php';
?>
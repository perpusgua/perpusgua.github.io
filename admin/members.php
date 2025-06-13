<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Membutuhkan hak admin

requireAdmin();

// Dapatkan Koneksi databes
$conn = getDB();

// Fungsi untuk mendapatkan semua anggota
$members = getAllUsers('member');

// Memproses tindakan user
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete user
    if (isset($_POST['delete_member'])) {
        $memberId = (int)$_POST['member_id'];
        
        // Periksa apakah user memiliki pinjaman aktif
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM loans WHERE user_id = ? AND return_date IS NULL");
        $stmt->bind_param("i", $memberId);
        $stmt->execute();
        $result = $stmt->get_result();
        $activeLoans = $result->fetch_assoc()['count'];
        
        if ($activeLoans > 0) {
            $_SESSION['error_message'] = 'Cannot delete member with active loans';
        } else {
            // Hapus user
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role = 'member'");
            $stmt->bind_param("i", $memberId);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Member deleted successfully';
                redirect('members.php');
            } else {
                $_SESSION['error_message'] = 'Error deleting member: ' . $conn->error;
            }
        }
    }
}

$pageTitle = 'Manage Members';

require_once '../includes/header.php';
?>

<div class="row mb-4">
    <div class="col-md-12">
        <h1>Manage Members</h1>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (count($members) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover" id="membersTable">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Full Name</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Joined</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                    <tr>
                        <td><?php echo $member['username']; ?></td>
                        <td><?php echo $member['full_name']; ?></td>
                        <td><?php echo $member['phone'] ? $member['phone'] : '—'; ?></td>
                        <td><?php echo $member['address'] ? substr($member['address'], 0, 30) . (strlen($member['address']) > 30 ? '...' : '') : '—'; ?>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($member['created_at'])); ?></td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                    data-bs-target="#memberDetailsModal" data-member-id="<?php echo $member['id']; ?>"
                                    data-username="<?php echo $member['username']; ?>"
                                    data-full-name="<?php echo $member['full_name']; ?>"
                                    data-phone="<?php echo $member['phone']; ?>"
                                    data-address="<?php echo $member['address']; ?>"
                                    data-joined="<?php echo date('M d, Y', strtotime($member['created_at'])); ?>">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <a href="loans.php?user_id=<?php echo $member['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-book"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#deleteMemberModal" data-member-id="<?php echo $member['id']; ?>"
                                    data-username="<?php echo $member['username']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <p class="mb-0">No members found.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Detail Anggota Modal -->
<div class="modal fade" id="memberDetailsModal" tabindex="-1" aria-labelledby="memberDetailsModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="memberDetailsModalLabel">Member Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <strong>Username:</strong> <span id="modal-username"></span>
                </div>
                <div class="mb-3">
                    <strong>Full Name:</strong> <span id="modal-full-name"></span>
                </div>
                <div class="mb-3">
                    <strong>Phone:</strong> <span id="modal-phone"></span>
                </div>
                <div class="mb-3">
                    <strong>Address:</strong> <span id="modal-address"></span>
                </div>
                <div class="mb-3">
                    <strong>Joined:</strong> <span id="modal-joined"></span>
                </div>

                <!-- Statistik pinjaman -->
                <div class="card mt-3">
                    <div class="card-header">Loan Statistics</div>
                    <div class="card-body">
                        <div id="loan-stats-loading" class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div id="loan-stats-content" class="d-none">
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <strong>Total Pinjaman:</strong> <span id="modal-total-loans">0</span>
                                </div>
                                <div class="col-6 mb-2">
                                    <strong>Active Loans:</strong> <span id="modal-active-loans">0</span>
                                </div>
                                <div class="col-6 mb-2">
                                    <strong>Terlambat:</strong> <span id="modal-overdue-loans">0</span>
                                </div>
                                <div class="col-6 mb-2">
                                    <strong>Total Denda:</strong> Rp<span id="modal-total-fines">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="#" id="view-loans-link" class="btn btn-primary">View Loans</a>
            </div>
        </div>
    </div>
</div>

<!-- Delete Member Modal -->
<div class="modal fade" id="deleteMemberModal" tabindex="-1" aria-labelledby="deleteMemberModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteMemberModalLabel">Delete Member</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus <strong id="delete-member-name"></strong>?</p>
                <p class="text-danger">Tindakan ini tidak bisa dibatalkan. Semua pinjaman yang terkait dengan pengguna
                    ini akan dihapus.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="post" action="">
                    <input type="hidden" name="member_id" id="delete-member-id">
                    <button type="submit" name="delete_member" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Detail Member Modal
    const memberDetailsModal = document.getElementById('memberDetailsModal');
    if (memberDetailsModal) {
        memberDetailsModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const memberId = button.getAttribute('data-member-id');
            const username = button.getAttribute('data-username');
            const fullName = button.getAttribute('data-full-name');
            const phone = button.getAttribute('data-phone');
            const address = button.getAttribute('data-address');
            const joined = button.getAttribute('data-joined');

            document.getElementById('modal-username').textContent = username;
            document.getElementById('modal-full-name').textContent = fullName;
            document.getElementById('modal-phone').textContent = phone || '—';
            document.getElementById('modal-address').textContent = address || '—';
            document.getElementById('modal-joined').textContent = joined;

            document.getElementById('view-loans-link').href = 'loans.php?user_id=' + memberId;

            // Memuat statistik pinjaman melalui jax
            document.getElementById('loan-stats-loading').classList.remove('d-none');
            document.getElementById('loan-stats-content').classList.add('d-none');

            // Simulasi pemanggilan AJAX untuk mendapatkan statistik pinjaman
            setTimeout(function() {
                // Simulasi data pinjaman
                document.getElementById('modal-total-loans').textContent = Math.floor(Math
                    .random() * 20);
                document.getElementById('modal-active-loans').textContent = Math.floor(Math
                    .random() * 5);
                document.getElementById('modal-overdue-loans').textContent = Math.floor(Math
                    .random() * 3);
                document.getElementById('modal-total-fines').textContent = (Math.random() * 30)
                    .toFixed(2);

                document.getElementById('loan-stats-loading').classList.add('d-none');
                document.getElementById('loan-stats-content').classList.remove('d-none');
            }, 1000);
        });
    }

    // Delete Member Modal
    const deleteMemberModal = document.getElementById('deleteMemberModal');
    if (deleteMemberModal) {
        deleteMemberModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const memberId = button.getAttribute('data-member-id');
            const username = button.getAttribute('data-username');

            document.getElementById('delete-member-name').textContent = username;
            document.getElementById('delete-member-id').value = memberId;
        });
    }
});
</script>

<!-- ya gitu lah -->
<div style="margin-bottom: 250px;"></div>

<?php
require_once '../includes/footer.php';
?>
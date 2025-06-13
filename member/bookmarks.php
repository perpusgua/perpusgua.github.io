<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/bookmark_functions.php';

// Membutuhkan login untuk mengakses
requireLogin();

// Jangan izin admin akses area anggota
if (isAdmin()) {
    redirect('../admin/dashboard.php');
}

$user_id = getCurrentUserId();
$bookmarks = getUserBookmarks($user_id);

$pageTitle = "Bookmark Saya";
include '../includes/header.php';
?>

<div class="container py-4">
    <!-- Header -->
    <div class=" row align-items-center mb-4">
        <div class="col-lg-6">
            <h1 class="fw-bold mb-0"><i class="fas fa-bookmark me-2 text-warning"></i>Bookmark Saya</h1>
            <p class="text-muted lead">Koleksi buku yang Anda simpan untuk dibaca nanti</p>
        </div>
    </div>

    <?php if (empty($bookmarks)): ?>
    <div class="alert alert-info d-flex align-items-center shadow-sm rounded-4 p-4" style="margin-bottom: 200px;">
        <i class="fas fa-info-circle fa-2x me-3 text-primary"></i>
        <div>
            <h5 class="alert-heading fw-bold mb-1">Anda belum memiliki bookmark</h5>
            <p class="mb-0">Kunjungi <a href="books.php" class="alert-link">halaman buku</a> untuk menambahkan bookmark.
            </p>
        </div>
    </div>
    <?php else: ?>
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <?php foreach ($bookmarks as $book): ?>
        <div class="col">
            <div class="card h-100 border-0 shadow-sm rounded-4 book-card">
                <!-- Book Cover -->
                <div class=" position-relative">
                    <?php if(!empty($book['image_path'])): ?>
                    <img src="<?php echo '../uploads/covers/' . $book['image_path']; ?>"
                        class="card-img-top rounded-top-4" alt="<?php echo $book['title']; ?>"
                        style="height: 260px; object-fit: cover;">
                    <?php else: ?>
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center rounded-top-4"
                        style="height: 260px;">
                        <i class="fas fa-book fa-4x text-secondary"></i>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Info buku -->
                <div class="card-body">
                    <!-- status di atas judul -->
                    <div class="mb-2">
                        <?php if($book['available_copies'] > 0): ?>
                        <span class="badge bg-success rounded-pill px-2 py-1">
                            <i class="fas fa-check-circle me-1"></i>Tersedia
                        </span>
                        <?php else: ?>
                        <span class="badge bg-danger rounded-pill px-2 py-1">
                            <i class="fas fa-times-circle me-1"></i>Tidak Tersedia
                        </span>
                        <?php endif; ?>
                    </div>

                    <h5 class="card-title fw-bold"><?php echo $book['title']; ?></h5>
                    <p class="card-subtitle mb-2 text-muted">Dari <?php echo $book['author']; ?></p>

                    <div class="mb-2">
                        <span class="badge bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-tag me-1"></i><?php echo $book['category']; ?>
                        </span>
                    </div>

                    <p class="card-text text-muted small">
                        <?php echo substr($book['description'], 0, 100) . '...'; ?></p>

                    <div class="text-muted small mt-2">
                        <i class="fas fa-clock me-1"></i> Di-bookmark pada:
                        <?php echo date('d M Y', strtotime($book['bookmarked_at'])); ?>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="card-footer bg-white border-top-0 pt-0">
                    <div class="d-flex gap-2">
                        <a href="books.php?id=<?php echo $book['id']; ?>"
                            class="btn btn-primary flex-grow-1 rounded-pill">
                            <i class="fas fa-info-circle me-2"></i>Detail
                        </a>
                        <button class="btn btn-warning rounded-pill remove-bookmark"
                            data-book-id="<?php echo $book['id']; ?>">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- CSS -->
<style>
.book-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle Penghapusan Bookmark
    const removeButtons = document.querySelectorAll('.remove-bookmark');
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const bookId = this.getAttribute('data-book-id');
            const card = this.closest('.col');

            // Send AJAX permintaan untuk menghapus bookmark
            const formData = new FormData();
            formData.append('book_id', bookId);
            formData.append('action', 'remove');

            fetch('bookmark_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove card Dari DOM
                        card.remove();

                        // Jika tidak ada bookmark yang tersisa, refres untuk menunjukkan keadaan kosong
                        if (document.querySelectorAll('.book-card').length === 0) {
                            location.reload();
                        }

                        // Tampilkan hanya pemberitahuan dari server
                        alert(data.message);
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menghapus bookmark');
                });
        });
    });
});
</script>



<?php include '../includes/footer.php'; ?>
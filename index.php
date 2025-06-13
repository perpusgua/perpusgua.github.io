<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Perbarui pinjaman yang sudah lewat
updateOverdueLoans();

// dapatkan buku unggulan
$conn = getDB();
$featuredBooks = $conn->query("SELECT * FROM books ORDER BY RAND() LIMIT 4");

// dapatkan kategori buku untuk tampilan
$categories = getCategories();

$pageTitle = 'Home';


require_once 'includes/header.php';
?>

<div class="hero-section position-relative mb-5">
    <div class="bg-image"
        style="background-image: url('assets/images/library-bg.jpg'); height: 70vh; background-size: cover; background-position: center;">
    </div>
    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark" style="opacity: 0.7;"></div>
    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center">
        <div class="container text-white">
            <div class="row">
                <div class="col-lg-7">
                    <h1 class="display-3 fw-bold animate__animated animate__fadeInUp"><?php echo SITE_NAME; ?></h1>
                    <p class="lead fs-4 mb-4 animate__animated animate__fadeInUp animate__delay-1s">

                        Temukan dan pinjam
                        buku dari koleksi kami yang luas. Bergabunglah dengan komunitas perpustakaan kami hari ini!</p>
                    <?php if (!isLoggedIn()): ?>
                    <div class="mt-4 animate__animated animate__fadeInUp animate__delay-2s">
                        <a href="register.php" class="btn btn-primary btn-lg rounded-pill px-4 me-3 shadow-sm">
                            <i class="fas fa-user-plus me-2"></i>KITA OPEN MEMBERR!!
                        </a>
                        <a href="login.php" class="btn btn-outline-light btn-lg rounded-pill px-4 shadow-sm">
                            <i class="fas fa-sign-in-alt me-2"></i>LOGINN
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="mt-4 animate__animated animate__fadeInUp animate__delay-2s">
                        <a href="<?php echo isAdmin() ? 'admin/dashboard.php' : 'member/books.php'; ?>"
                            class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                            <i class="fas <?php echo isAdmin() ? 'fa-tachometer-alt' : 'fa-search'; ?> me-2"></i>
                            <?php echo isAdmin() ? 'Admin Dashboard' : 'Cari Buku'; ?>
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search -->
<div class="container mb-5">
    <div class="card border-0 shadow-lg rounded-4 p-3">
        <div class="card-body">
            <form
                action="<?php echo isLoggedIn() ? (isAdmin() ? 'admin/books.php' : 'member/books.php') : 'login.php'; ?>"
                method="get" class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i
                                class="fas fa-search text-primary"></i></span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Judul buku, penulis, atau kata kunci...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">Semua Kategori</option>
                        <?php foreach($categories as $category): ?>
                        <option value="<?php echo $category; ?>"><?php echo $category; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="availability" class="form-select">
                        <option value="all">Semua Status</option>
                        <option value="available">Tersedia</option>
                        <option value="unavailable">Tidak Tersedia</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Cari
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bagian buku unggulan -->
<section class="container mb-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="fw-bold"><i class="fas fa-fire me-2 text-primary"></i>Buku Pilihan</h2>
        <a href="<?php echo isLoggedIn() ? (isAdmin() ? 'admin/books.php' : 'member/books.php') : 'login.php'; ?>"
            class="btn btn-outline-primary rounded-pill">
            <i class="fas fa-books me-2"></i>Lihat Semua Buku
        </a>
    </div>

    <div class="row g-4">
        <?php while($book = $featuredBooks->fetch_assoc()): ?>
        <div class="col-md-3">
            <div class="card h-100 border-0 shadow-sm book-card">
                <div class="position-relative">
                    <?php if($book['available_copies'] > 0): ?>
                    <span
                        class="position-absolute top-0 end-0 m-2 badge bg-success rounded-pill px-3 py-2 z-1">Tersedia</span>
                    <?php else: ?>
                    <span class="position-absolute top-0 end-0 m-2 badge bg-danger rounded-pill px-3 py-2 z-1">Tidak
                        Tersedia</span>
                    <?php endif; ?>

                    <?php if($book['image_path']): ?>
                    <img src="uploads/covers/<?php echo $book['image_path']; ?>" class="card-img-top rounded-top"
                        alt="<?php echo $book['title']; ?>" style="height: 250px; object-fit: cover;">
                    <?php else: ?>
                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center rounded-top"
                        style="height: 250px;">
                        <i class="fas fa-book fa-3x text-secondary"></i>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="card-body">
                    <h5 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($book['title']); ?></h5>
                    <p class="card-subtitle mb-2 text-muted fst-italic">By
                        <?php echo htmlspecialchars($book['author']); ?></p>
                    <p class="card-text small text-secondary">
                        <?php
                        // Gunakan operator penggabungan null untuk menyediakan string kosong jika deskripsi bernilai null
                        $description = $book['description'] ?? 'Tidak ada deskripsi untuk buku ini.';
                        // Selain itu, pastikan bahwa `...` hanya ditambahkan jika string benar-benar terpotong.
                        if (mb_strlen($description) > 100) {
                            echo htmlspecialchars(mb_substr($description, 0, 100)) . '...';
                        } else {
                            echo htmlspecialchars($description);
                        }
                        ?>
                    </p>
                </div>

                <div class="card-footer bg-white border-0 pt-0">
                    <a href="<?php echo isLoggedIn() ? (isAdmin() ? 'admin/books.php?id=' . $book['id'] . '&mode=view' : 'member/books.php?id=' . $book['id']) : 'login.php'; ?>"
                        class="btn btn-primary w-100 rounded-pill">
                        <i class="fas fa-info-circle me-2"></i>Detail
                    </a>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</section>

<!-- Bagian Kategori -->
<section class="bg-light py-5 mb-5">
    <div class="container">
        <h2 class="fw-bold text-center mb-4"><i class="fas fa-tags me-2 text-primary"></i>Kategori Populer</h2>
        <div class="row g-4 justify-content-center">
            <?php foreach($categories as $category): ?>
            <div class="col-lg-2 col-md-3 col-sm-4 col-6">
                <a href="<?php echo isLoggedIn() ? (isAdmin() ? 'admin/books.php?category=' . urlencode($category) : 'member/books.php?category=' . urlencode($category)) : 'login.php'; ?>"
                    class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm text-center category-card">
                        <div class="card-body py-4">
                            <div class="category-icon mb-3">
                                <i class="fas fa-bookmark fa-2x text-primary"></i>
                            </div>
                            <h5 class="card-title mb-0"><?php echo $category; ?></h5>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Bagian Fitur Perpustakaan -->
<section class="container mb-5">
    <h2 class="fw-bold text-center mb-4"><i class="fas fa-gears me-2 text-primary"></i>Fitur-fitur Perpustakaan Kami
    </h2>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm feature-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon bg-primary bg-opacity-10 text-primary mx-auto mb-4 rounded-circle p-3"
                        style="width: 80px; height: 80px;">
                        <i class="fas fa-book fa-2x"></i>
                    </div>
                    <h3 class="card-title h4 fw-bold">Koleksi Yang Luas</h3>
                    <p class="card-text text-secondary">Akses ribuan buku dari berbagai kategori dan genre. Temukan
                        klasik, bestseller, dan hidden gem dari seluruh dunia.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm feature-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon bg-primary bg-opacity-10 text-primary mx-auto mb-4 rounded-circle p-3"
                        style="width: 80px; height: 80px;">
                        <i class="fas fa-laptop fa-2x"></i>
                    </div>
                    <h3 class="card-title h4 fw-bold">Management Online</h3>
                    <p class="card-text text-secondary">Kelola pinjaman Anda, periksa tanggal jatuh tempo, dan telusuri
                        koleksi kami secara online. Akses kapan saja dan di mana saja.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm feature-card">
                <div class="card-body text-center p-4">
                    <div class="feature-icon bg-primary bg-opacity-10 text-primary mx-auto mb-4 rounded-circle p-3"
                        style="width: 80px; height: 80px;">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                    <h3 class="card-title h4 fw-bold">Peminjaman yang mudah</h3>
                    <p class="card-text text-secondary">Proses peminjaman yang mudah dan cepat. Cukup beberapa klik
                        untuk meminjam buku favorit Anda dan mulai membaca.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Bagian Statistik -->
<section class="bg-primary text-white py-5 mb-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3 col-6 mb-4 mb-md-0">
                <div class="display-4 fw-bold mb-2">1,500+</div>
                <div class="h5">Buku Tersedia</div>
            </div>
            <div class="col-md-3 col-6 mb-4 mb-md-0">
                <div class="display-4 fw-bold mb-2">500+</div>
                <div class="h5">Member Aktif</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="display-4 fw-bold mb-2">20+</div>
                <div class="h5">Kategori</div>
            </div>
            <div class="col-md-3 col-6">
                <div class="display-4 fw-bold mb-2">24/7</div>
                <div class="h5">Akses Online</div>
            </div>
        </div>
    </div>
</section>


<!-- Bagian CTA -->
<section class="bg-light py-5 mb-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mb-4 mb-lg-0">
                <h2 class="fw-bold">Siap untuk mulai membaca?</h2>
                <p class="lead mb-0">Bergabunglah dengan perpustakaan kami hari ini dan akses ribuan buku dalam
                    genggaman Anda.</p>
            </div>
            <div class="col-lg-4 text-lg-end">
                <a href="<?php echo isLoggedIn() ? (isAdmin() ? 'admin/books.php' : 'member/books.php') : 'register.php'; ?>"
                    class="btn btn-primary btn-lg rounded-pill px-4 shadow-sm">
                    <?php echo isLoggedIn() ? 'Jelajahi Buku' : 'Daftar Sekarang'; ?>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
/* Kelas animasi */
.animate__animated {
    animation-duration: 1s;
    animation-fill-mode: both;
}

.animate__fadeInUp {
    animation-name: fadeInUp;
}

.animate__delay-1s {
    animation-delay: 0.3s;
}

.animate__delay-2s {
    animation-delay: 0.6s;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 30px, 0);
    }

    to {
        opacity: 1;
        transform: translate3d(0, 0, 0);
    }
}

/* Card hover effects */
.book-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.book-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

.category-card {
    transition: all 0.3s ease;
}

.category-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    background-color: var(--bs-primary);
    color: white;
}

.category-card:hover .text-primary {
    color: white !important;
}

.feature-card {
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-5px);
}

.feature-card:hover .feature-icon {
    background-color: var(--bs-primary) !important;
    color: white !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function setupBookmarkButton(btn) {
        btn.addEventListener('click', function() {
            const bookId = this.getAttribute('data-book-id');
            const action = this.getAttribute('data-action');

            const formData = new FormData();
            formData.append('book_id', bookId);
            formData.append('action', action);

            fetch('bookmark_action.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update tampilan tombol
                        if (action === 'add') {
                            this.innerHTML = '<i class="fas fa-bookmark me-2"></i> Hapus Mark';
                            this.classList.remove('btn-outline-warning');
                            this.classList.add('btn-warning');
                            this.setAttribute('data-action', 'remove');
                        } else {
                            this.innerHTML = '<i class="far fa-bookmark me-2"></i> Bookmark';
                            this.classList.remove('btn-warning');
                            this.classList.add('btn-outline-warning');
                            this.setAttribute('data-action', 'add');
                        }
                    }
                    alert(data.message);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat memproses bookmark');
                });
        });
    }

    const detailBtn = document.getElementById('bookmark-btn');
    if (detailBtn) setupBookmarkButton(detailBtn);

    document.querySelectorAll('.bookmark-grid-btn').forEach(btn => {
        setupBookmarkButton(btn);
    });
});
</script>

<?php
require_once 'includes/footer.php';
?>
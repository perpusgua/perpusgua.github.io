<?php
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/auth.php';

// Membutuhkan hak admin
requireAdmin();

// Dapatkan Koneksi databes
$conn = getDB();

// Dapatkan kategori
$categories = getCategories();

// Dapatkan filter
$category = isset($_GET['category']) ? clean($_GET['category']) : null;
$search = isset($_GET['search']) ? clean($_GET['search']) : null;

// Dapatkan buku berdasarkan filter
$books = getBooks($category, $search);

// Dapatkan detail buku tertentu jika ID disediakan
$bookDetails = null;
if (isset($_GET['id'])) {
    $bookId = (int)$_GET['id'];
    $bookDetails = getBookById($bookId);
}

// Tentukan Mode (Lihat atau Edit)
$mode = isset($_GET['mode']) ? clean($_GET['mode']) : 'edit'; // Default edit aja


// Proses Buku (Tambah, Edit, Hapus)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Tambahkan buku baru
    if (isset($_POST['add_book'])) {
        $title = clean($_POST['title']);
        $author = clean($_POST['author']);
        $isbn = clean($_POST['isbn']);
        
        // Periksa apakah "other" dipilih dan kategori baru muncul
        if ($_POST['category'] === 'other' && !empty($_POST['new_category'])) {
            $category = clean($_POST['new_category']);
        } else {
            $category = clean($_POST['category']);
        }
        
        $publicationYear = clean($_POST['tahun_terbitan']);
        $publisher = clean($_POST['publisher']);
        $copies = (int)$_POST['copies'];
        $description = clean($_POST['description']);
        
        // Proses aplod gambar dulu
        $imagePath = null;
        if(isset($_FILES['book_image']) && $_FILES['book_image']['size'] > 0) {
            $uploadDir = '../uploads/covers/';
            $fileExtension = pathinfo($_FILES['book_image']['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid('book_') . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFileName;
            
            // Periksa jenis file dan ukuran
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 2 * 1024 * 1024; // 2MB 
            //$maxFileSize = 2048 * 1024 * 1024; // dua giga rupiah 
            
            if(!in_array($_FILES['book_image']['type'], $allowedTypes)) {
                $_SESSION['error_message'] = 'Only JPG, PNG and GIF images are allowed';
            } elseif($_FILES['book_image']['size'] > $maxFileSize) {
                $_SESSION['error_message'] = 'Image size should not exceed 2MB';
            } elseif(move_uploaded_file($_FILES['book_image']['tmp_name'], $uploadFile)) {
                $imagePath = $newFileName;
            } else {
                $_SESSION['error_message'] = 'Error uploading image';
            }
        }
        
        // Validasi Dasar
        if (empty($title) || empty($author) || empty($isbn) || empty($category)) {
            $_SESSION['error_message'] = 'Please fill all required fields';
        } else {
            // Periksa apakah ISBN sudah ada
            $stmt = $conn->prepare("SELECT id FROM books WHERE isbn = ?");
            $stmt->bind_param("s", $isbn);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $_SESSION['error_message'] = 'Book with this ISBN already exists';
            } else {
                // Masukkan buku baru
                $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, category, tahun_terbitan, publisher, available_copies, total_copies, description, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssississ", $title, $author, $isbn, $category, $publicationYear, $publisher, $copies, $copies, $description, $imagePath);
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Book added successfully';
                    redirect('books.php');
                } else {
                    $_SESSION['error_message'] = 'Error adding book: ' . $conn->error;
                }
            }
        }
    }

    // Tambahkan validasi untuk tahun publikasi
    if (!empty($_POST['tahun_terbitan']) && $_POST['tahun_terbitan'] !== '-') {
        // Hanya validasi sebagai angka jika bukan placeholder
        if (!is_numeric($_POST['tahun_terbitan']) || 
            (int)$_POST['tahun_terbitan'] < 1000 || 
            (int)$_POST['tahun_terbitan'] > date('Y')) {
            $_SESSION['error_message'] = 'Please enter a valid publication year or "-"';
        }
    }
    
    // Edit book
    if (isset($_POST['edit_book'])) {
        $bookId = (int)$_POST['book_id'];
        $title = clean($_POST['title']);
        $author = clean($_POST['author']);
        $isbn = clean($_POST['isbn']);
        
        // Periksa apakah "other" dipilih dan kategori baru muncul
        if ($_POST['category'] === 'other' && !empty($_POST['new_category'])) {
            $category = clean($_POST['new_category']);
        } else {
            $category = clean($_POST['category']);
        }
        
        $publicationYear = clean($_POST['tahun_terbitan']);
        $publisher = clean($_POST['publisher']);
        $totalCopies = (int)$_POST['total_copies'];
        $availableCopies = (int)$_POST['available_copies'];
        $description = clean($_POST['description']);
        
        // Dapatkan detail buku saat ini untuk menyimpan gambar jika tidak ada yang baru diunggah
        $currentBook = getBookById($bookId);
        $imagePath = $currentBook['image_path']; // Default ke gambar yang ada
        
        // Proses gambar uplod jika gambar baru di uplod
        if(isset($_FILES['book_image']) && $_FILES['book_image']['size'] > 0) {
            $uploadDir = '../uploads/covers/';
            $fileExtension = pathinfo($_FILES['book_image']['name'], PATHINFO_EXTENSION);
            $newFileName = uniqid('book_') . '.' . $fileExtension;
            $uploadFile = $uploadDir . $newFileName;
            
            // Periksa jenis file dan ukuran
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 2 * 1024 * 1024; // 2MB
            
            if(!in_array($_FILES['book_image']['type'], $allowedTypes)) {
                $_SESSION['error_message'] = 'Only JPG, PNG and GIF images are allowed';
            } elseif($_FILES['book_image']['size'] > $maxFileSize) {
                $_SESSION['error_message'] = 'Image size should not exceed 2MB';
            } elseif(move_uploaded_file($_FILES['book_image']['tmp_name'], $uploadFile)) {
                // Delete old image if exists
                if($currentBook['image_path'] && file_exists($uploadDir . $currentBook['image_path'])) {
                    unlink($uploadDir . $currentBook['image_path']);
                }
                $imagePath = $newFileName;
            } else {
                $_SESSION['error_message'] = 'Error uploading image';
            }
        }
        
        // Validasi Dasar
        if (empty($title) || empty($author) || empty($isbn) || empty($category)) {
            $_SESSION['error_message'] = 'Please fill all required fields';
        } else {
            // Periksa apakah ISBN sudah ada (tidak termasuk buku saat ini)
            $stmt = $conn->prepare("SELECT id FROM books WHERE isbn = ? AND id != ?");
            $stmt->bind_param("si", $isbn, $bookId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $_SESSION['error_message'] = 'Another book with this ISBN already exists';
            } else {
                // Update book
                $stmt = $conn->prepare("UPDATE books SET title = ?, author = ?, isbn = ?, category = ?, tahun_terbitan = ?, publisher = ?, total_copies = ?, available_copies = ?, description = ?, image_path = ? WHERE id = ?");
                $stmt->bind_param("ssssississi", $title, $author, $isbn, $category, $publicationYear, $publisher, $totalCopies, $availableCopies, $description, $imagePath, $bookId);
                
                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Book updated successfully';
                    redirect('books.php?id=' . $bookId);
                } else {
                    $_SESSION['error_message'] = 'Error updating book: ' . $conn->error;
                }
            }
        }
    }

    $placeholderValues = ["-", "N/A", "Unknown"];
        if (!in_array($isbn, $placeholderValues)) {
            // Validasi ISBN  
        }
    
    // Delete book
    if (isset($_POST['delete_book'])) {
        $bookId = (int)$_POST['book_id'];
        
        // Periksa apakah buku memiliki pinjaman aktif
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM loans WHERE book_id = ? AND return_date IS NULL");
        $stmt->bind_param("i", $bookId);
        $stmt->execute();
        $result = $stmt->get_result();
        $activeLoans = $result->fetch_assoc()['count'];
        
        if ($activeLoans > 0) {
            $_SESSION['error_message'] = 'Cannot delete book with active loans';
        } else {
            // Delete book
            $stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
            $stmt->bind_param("i", $bookId);
            
            if ($stmt->execute()) {
                $_SESSION['success_message'] = 'Book deleted successfully';
                redirect('books.php');
            } else {
                $_SESSION['error_message'] = 'Error deleting book: ' . $conn->error;
            }
        }
    }
}


$pageTitle = $bookDetails ? ($mode === 'view' ? 'Book Details: ' . $bookDetails['title'] : 'Edit Book: ' . $bookDetails['title']) : (isset($_GET['action']) && $_GET['action'] === 'add' ? 'Add New Book' : 'Manage Books');


require_once '../includes/header.php';
?>

<?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
<!-- Add Book Form -->
<div class="row mb-3">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-light p-2 rounded">
                <li class="breadcrumb-item"><a href="books.php" class="text-decoration-none">Books</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">Tambah Buku Baru</li>
            </ol>
        </nav>
        <h1 class="h3 mb-3">Tambah Buku Baru</h1>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card book-form-container">
            <div class="card-header book-form-header">
                <h5 class="card-title mb-0 text-white"><i class="fas fa-plus-circle me-2"></i>Detail Buku</h5>
            </div>
            <div class="card-body p-3">
                <form method="post" action="" enctype="multipart/form-data" class="needs-validation" novalidate>

                    <!-- Informasi dasar -->
                    <div class="form-section">
                        <h6 class="fw-bold"><i class="fas fa-info-circle me-2"></i>Informasi Dasar</h6>
                        <div class="row row-compact g-2">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Judul <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-book"></i></span>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="author" class="form-label">Penulis <span
                                        class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-user-edit"></i></span>
                                    <input type="text" class="form-control" id="author" name="author" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Penerbitan -->
                    <div class="form-section">
                        <h6 class="fw-bold"><i class="fas fa-building me-2"></i>Detail Penerbitan</h6>
                        <div class="row row-compact g-2">
                            <div class="col-md-4">
                                <label for="publisher" class="form-label">Penerbit</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <input type="text" class="form-control" id="publisher" name="publisher">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="tahun_terbitan" class="form-label">Tahun Penerbitan</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="text" class="form-control" id="tahun_terbitan" name="tahun_terbitan"
                                        pattern="(\d{4}|-)" title="tambah 4-digit tahun atau '-'">
                                </div>
                                <small class="text-muted">Format: 4-digit atau '-'</small>
                            </div>
                            <div class="col-md-4">
                                <label for="copies" class="form-label">Jumlah Salinan <span
                                        class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-copy"></i></span>
                                    <input type="number" class="form-control" id="copies" name="copies" min="1"
                                        value="1" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Klasifikasi -->
                    <div class="form-section">
                        <h6 class="fw-bold"><i class="fas fa-tag me-2"></i>Klasifikasi</h6>
                        <div class="row row-compact g-2">
                            <div class="col-md-6">
                                <label for="isbn" class="form-label">ISBN <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    <input type="text" class="form-control" id="isbn" name="isbn" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">Kategori <span
                                        class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <select class="form-select" id="category" name="category" required>
                                        <option value="">Pilih Kategori</option>
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>"><?php echo $cat; ?></option>
                                        <?php endforeach; ?>
                                        <option value="other">Lainnya (Kategori Baru)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 d-none" id="new-category-container">
                                <label for="new_category" class="form-label">Kategori Baru <span
                                        class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-plus-circle"></i></span>
                                    <input type="text" class="form-control" id="new_category" name="new_category">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- cover dan deskripsi -->
                    <div class="form-section">
                        <h6 class="fw-bold"><i class="fas fa-file-image me-2"></i>Cover & Deskripsi</h6>
                        <div class="row row-compact g-2">
                            <div class="col-md-12">
                                <label for="book_image" class="form-label">Cover Buku</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-image"></i></span>
                                    <input type="file" class="form-control" id="book_image" name="book_image"
                                        accept="image/jpeg,image/png,image/gif">
                                </div>
                                <small class="text-muted">Recommended: 300x450px. Max: 2MB</small>
                                <div id="image-preview-container" class="mt-2"></div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="description" name="description" rows="3"
                                    placeholder="Masukkan deskripsi buku di sini..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol -->
                    <div class="d-flex justify-content-between mt-3">
                        <a href="books.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Batal
                        </a>
                        <button type="submit" name="add_book" class="btn btn-primary-gradient">
                            <i class="fas fa-plus-circle me-2"></i>Tambah Buku
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php elseif ($bookDetails && $mode === 'view'): ?>
<!-- Detail Buku Tampilan -->
<div class="row mb-4">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="books.php">Books</a></li>
                <li class="breadcrumb-item active" aria-current="page"><?php echo $bookDetails['title']; ?></li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h1><?php echo $bookDetails['title']; ?></h1>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body text-center">
                <?php if($bookDetails['image_path']): ?>
                <img src="<?php echo '../uploads/covers/' . $bookDetails['image_path']; ?>" alt="Book cover"
                    class="img-fluid" style="max-height: 500px;">
                <?php else: ?>
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 400px;">
                    <i class="fas fa-book fa-5x text-secondary"></i>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Book Information</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Penulis:</div>
                    <div class="col-md-9"><?php echo $bookDetails['author']; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">ISBN:</div>
                    <div class="col-md-9"><?php echo $bookDetails['isbn']; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Kategori:</div>
                    <div class="col-md-9"><?php echo $bookDetails['category']; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Tahun Penerbitan:</div>
                    <div class="col-md-9"><?php echo $bookDetails['tahun_terbitan'] ?: 'N/A'; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Penerbit:</div>
                    <div class="col-md-9"><?php echo $bookDetails['publisher'] ?: 'N/A'; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3 text-muted">Ketersediaan:</div>
                    <div class="col-md-9">
                        <span
                            class="badge bg-<?php echo $bookDetails['available_copies'] > 0 ? 'success' : 'danger'; ?>">
                            <?php echo $bookDetails['available_copies'] > 0 ? 'Tersedia' : 'Tidak Tersedia'; ?>
                        </span>
                        <span class="ms-2"><?php echo $bookDetails['available_copies']; ?> dari
                            <?php echo $bookDetails['total_copies']; ?> Salinan tersedia</span>
                    </div>
                </div>
            </div>
        </div>

        <?php if($bookDetails['description']): ?>
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">Description</h5>
            </div>
            <div class="card-body">
                <p><?php echo nl2br($bookDetails['description']); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between">
            <a href="books.php" class="btn btn-secondary">Back to Books</a>
            <a href="books.php?id=<?php echo $bookDetails['id']; ?>&mode=edit" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit Book
            </a>
        </div>
    </div>
</div>

<?php elseif ($bookDetails): ?>
<!-- Edit Book Form -->
<div class="row mb-3">
    <div class="col-md-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-light p-2 rounded">
                <li class="breadcrumb-item"><a href="books.php" class="text-decoration-none">Books</a></li>
                <li class="breadcrumb-item active fw-semibold" aria-current="page">Edit:
                    <?php echo $bookDetails['title']; ?></li>
            </ol>
        </nav>
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-3">Edit Book</h1>
            <form method="post" action="" class="d-inline"
                onsubmit="return confirm('Are you sure you want to delete this book?');">
                <input type="hidden" name="book_id" value="<?php echo $bookDetails['id']; ?>">
                <button type="submit" name="delete_book" class="btn btn-danger">
                    <i class="fas fa-trash me-2"></i> Delete Book
                </button>
            </form>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card book-form-container">
            <div class="card-header book-form-header">
                <h5 class="card-title mb-0 text-white"><i class="fas fa-edit me-2"></i>Edit Book Details</h5>
            </div>
            <div class="card-body p-3">
                <form method="post" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="book_id" value="<?php echo $bookDetails['id']; ?>">

                    <!-- Basic Information -->
                    <div class="form-section">
                        <h6 class="fw-bold"><i class="fas fa-info-circle me-2"></i>Informasi Dasar</h6>
                        <div class="row row-compact g-2">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Judul <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-book"></i></span>
                                    <input type="text" class="form-control" id="title" name="title"
                                        value="<?php echo $bookDetails['title']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="author" class="form-label">Penulis <span
                                        class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-user-edit"></i></span>
                                    <input type="text" class="form-control" id="author" name="author"
                                        value="<?php echo $bookDetails['author']; ?>" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Penerbitan -->
                    <div class="form-section">
                        <h6 class="fw-bold"><i class="fas fa-building me-2"></i>Detail Penerbitan</h6>
                        <div class="row row-compact g-2">
                            <div class="col-md-6">
                                <label for="publisher" class="form-label">Penerbit</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-building"></i></span>
                                    <input type="text" class="form-control" id="publisher" name="publisher"
                                        value="<?php echo $bookDetails['publisher']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="tahun_terbitan" class="form-label">Tahun Penerbitan</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="text" class="form-control" id="tahun_terbitan" name="tahun_terbitan"
                                        pattern="(\d{4}|-)" title="Enter a 4-digit year or '-'"
                                        value="<?php echo $bookDetails['tahun_terbitan']; ?>">
                                </div>
                                <small class="text-muted">Format: 4-digit or '-'</small>
                            </div>
                        </div>
                    </div>

                    <!-- Klasifikasi -->
                    <div class="form-section">
                        <h6 class="fw-bold"><i class="fas fa-tag me-2"></i>Klasifikasi</h6>
                        <div class="row row-compact g-2">
                            <div class="col-md-6">
                                <label for="isbn" class="form-label">ISBN <span class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                    <input type="text" class="form-control" id="isbn" name="isbn"
                                        value="<?php echo $bookDetails['isbn']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="category" class="form-label">Kategori <span
                                        class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                    <select class="form-select" id="category" name="category" required>
                                        <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat; ?>"
                                            <?php echo $cat === $bookDetails['category'] ? 'selected' : ''; ?>>
                                            <?php echo $cat; ?>
                                        </option>
                                        <?php endforeach; ?>
                                        <option value="other">Lainnya (Kategori Baru)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 d-none" id="new-category-container">
                                <label for="new_category" class="form-label">Kategori Baru <span
                                        class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-plus-circle"></i></span>
                                    <input type="text" class="form-control" id="new_category" name="new_category">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ketersedia -->
                    <div class="form-section">
                        <h6 class="fw-bold"><i class="fas fa-box-open me-2"></i>Ketersediaan</h6>
                        <div class="row row-compact g-2">
                            <div class="col-md-4">
                                <label for="total_copies" class="form-label">Total Salinan <span
                                        class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-copy"></i></span>
                                    <input type="number" class="form-control" id="total_copies" name="total_copies"
                                        min="0" value="<?php echo $bookDetails['total_copies']; ?>" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="available_copies" class="form-label">Tersedia Salinan <span
                                        class="text-danger">*</span></label>
                                <div class="input-group has-validation">
                                    <span class="input-group-text"><i class="fas fa-check-circle"></i></span>
                                    <input type="number" class="form-control" id="available_copies"
                                        name="available_copies" min="0"
                                        max="<?php echo $bookDetails['total_copies']; ?>"
                                        value="<?php echo $bookDetails['available_copies']; ?>" required>
                                </div>
                                <small class="text-muted">Tidak boleh melebihi total salinan</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Status</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                                    <div class="form-control d-flex align-items-center">
                                        <?php if ($bookDetails['available_copies'] > 0): ?>
                                        <span class="badge bg-success px-3 py-2">Tersedia</span>
                                        <?php else: ?>
                                        <span class="badge bg-danger px-3 py-2">Habis</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- cover dan deskripsi -->
                    <div class="form-section">
                        <h6 class="fw-bold"><i class="fas fa-file-image me-2"></i>Cover & Deskripsi</h6>
                        <div class="row row-compact g-2">
                            <div class="col-md-12">
                                <label for="edit_book_image" class="form-label">Cover Buku</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-image"></i></span>
                                    <input type="file" class="form-control" id="edit_book_image" name="book_image"
                                        accept="image/jpeg,image/png,image/gif">
                                </div>
                                <small class="text-muted">Recommended: 300x450px. Max: 2MB</small>

                                <div id="edit-image-preview-container" class="mt-2">
                                    <?php if($bookDetails['image_path']): ?>
                                    <div class="cover-preview">
                                        <div class="d-flex align-items-center">
                                            <img src="<?php echo '../uploads/covers/' . $bookDetails['image_path']; ?>"
                                                alt="Book cover" style="max-height: 150px;">
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-12 mt-2">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="description" name="description"
                                    rows="3"><?php echo $bookDetails['description']; ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Tombol lagi -->
                    <div class="d-flex justify-content-between mt-3">
                        <a href="books.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </a>
                        <button type="submit" name="edit_book" class="btn btn-primary-gradient">
                            <i class="fas fa-save me-2"></i>Update Book
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- Daftar Buku -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Manage Books</h1>
            <a href="books.php?action=add" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Book
            </a>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-8">
        <form action="" method="get" class="d-flex">
            <input type="text" name="search" class="form-control me-2" placeholder="Search books..."
                value="<?php echo $search; ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>
    <div class="col-md-4">
        <select class="form-select" id="category-filter" onchange="window.location.href=this.value;">
            <option value="books.php">All Categories</option>
            <?php foreach ($categories as $cat): ?>
            <option value="books.php?category=<?php echo urlencode($cat); ?>"
                <?php echo $category === $cat ? 'selected' : ''; ?>>
                <?php echo $cat; ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <?php if (count($books) > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Cover</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>ISBN</th>
                        <th>Category</th>
                        <th>Copies</th>
                        <th>Available</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $book): ?>
                    <tr>
                        <td>
                            <?php if($book['image_path']): ?>
                            <img src="<?php echo '../uploads/covers/' . $book['image_path']; ?>" alt="Cover"
                                style="width: 50px; height: 75px; object-fit: cover;">
                            <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center"
                                style="width: 50px; height: 75px;">
                                <i class="fas fa-book text-secondary"></i>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $book['title']; ?></td>
                        <td><?php echo $book['author']; ?></td>
                        <td><?php echo $book['isbn']; ?></td>
                        <td><?php echo $book['category']; ?></td>
                        <td><?php echo $book['total_copies']; ?></td>
                        <td>
                            <span class="badge bg-<?php echo $book['available_copies'] > 0 ? 'success' : 'danger'; ?>">
                                <?php echo $book['available_copies']; ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="books.php?id=<?php echo $book['id']; ?>&mode=view" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="books.php?id=<?php echo $book['id']; ?>" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="alert alert-info">
            <p class="mb-0">No books found. Try a different search or category.</p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- JavaScript untuk pemilihan kategori -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const categorySelect = document.getElementById('category');
    const newCategoryContainer = document.getElementById('new-category-container');
    const newCategoryInput = document.getElementById('new_category');

    if (categorySelect && newCategoryContainer && newCategoryInput) {
        categorySelect.addEventListener('change', function() {
            if (this.value === 'other') {
                newCategoryContainer.classList.remove('d-none');
                newCategoryInput.setAttribute('required', 'required');
            } else {
                newCategoryContainer.classList.add('d-none');
                newCategoryInput.removeAttribute('required');
            }
        });
    }

    // Untuk Edit: Salinan yang Tersedia <= Total Salinan
    const totalCopiesInput = document.getElementById('total_copies');
    const availableCopiesInput = document.getElementById('available_copies');

    if (totalCopiesInput && availableCopiesInput) {
        totalCopiesInput.addEventListener('change', function() {
            availableCopiesInput.setAttribute('max', this.value);
            if (parseInt(availableCopiesInput.value) > parseInt(this
                    .value)) {
                availableCopiesInput.value = this.value;
            }
        });
    }
});
</script>

<!-- JavaScript untuk fungsionalitas form -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi liat gambar untuk Add Book form
    const bookImageInput = document.getElementById('book_image');
    const imagePreviewContainer = document.getElementById('image-preview-container');

    if (bookImageInput && imagePreviewContainer) {
        bookImageInput.addEventListener('change', function() {
            previewImage(this, imagePreviewContainer);
        });
    }

    // Fungsi liat gambar untuk Edit Book form
    const editBookImageInput = document.getElementById('edit_book_image');
    const editImagePreviewContainer = document.getElementById('edit-image-preview-container');

    if (editBookImageInput && editImagePreviewContainer) {
        editBookImageInput.addEventListener('change', function() {
            // Hapus konten saat ini dahulu (tetapi simpan gambar yang ada kalo ada)
            const existingPreview = editImagePreviewContainer.querySelector('.new-image-preview');
            if (existingPreview) {
                existingPreview.remove();
            }

            previewImage(this, editImagePreviewContainer, true);
        });
    }

    // Fungsi untuk membuat view gambar
    function previewImage(input, container, isEdit = false) {
        if (input.files && input.files[0]) {
            const file = input.files[0];

            // Periksa jenis file
            if (!file.type.match('image.*')) {
                const errorDiv = document.createElement('div');
                errorDiv.className = 'alert alert-warning mt-2';
                errorDiv.innerHTML =
                    '<i class="fas fa-exclamation-triangle me-2"></i>File yang dipilih bukan gambar.';

                // Hapus container jika bukan mode edit
                if (!isEdit) {
                    container.innerHTML = '';
                }
                container.appendChild(errorDiv);
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'cover-preview mt-2 new-image-preview';

                previewDiv.innerHTML = `
          <div class="d-flex align-items-center">
            <img src="${e.target.result}" alt="Book cover preview" 
                 style="max-height: 300px; max-width: 450px;">
          </div>
        `;

                // Untuk add form,clear container terlebih dahulu

                if (!isEdit) {
                    container.innerHTML = '';
                }
                container.appendChild(previewDiv);
            };

            reader.readAsDataURL(file);
        }
    }

    // Pemilihan kategori
    const categorySelect = document.getElementById('category');
    const newCategoryContainer = document.getElementById('new-category-container');
    const newCategoryInput = document.getElementById('new_category');

    if (categorySelect && newCategoryContainer && newCategoryInput) {
        categorySelect.addEventListener('change', function() {
            if (this.value === 'other') {
                newCategoryContainer.classList.remove('d-none');
                newCategoryInput.setAttribute('required', 'required');
            } else {
                newCategoryContainer.classList.add('d-none');
                newCategoryInput.removeAttribute('required');
            }
        });
    }

    // Batasan salinan yang tersedia
    const totalCopiesInput = document.getElementById('total_copies');
    const availableCopiesInput = document.getElementById('available_copies');

    if (totalCopiesInput && availableCopiesInput) {
        totalCopiesInput.addEventListener('change', function() {
            availableCopiesInput.setAttribute('max', this.value);
            if (parseInt(availableCopiesInput.value) > parseInt(this.value)) {
                availableCopiesInput.value = this.value;
            }
        });
    }
});
</script>

<style>
.book-form-container {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border: none;
}

.book-form-header {
    background: linear-gradient(to right, #4568dc, #3f5efb);
    padding: 1rem 1.5rem;
    border-bottom: none;
    border-radius: 8px 8px 0 0;
}

.form-section {
    border-left: 3px solid #4568dc;
    padding: 0.8rem 1rem;
    margin-bottom: 1rem;
    background-color: #f8f9ff;
    border-radius: 0 4px 4px 0;
}

.form-section h6 {
    margin-bottom: 0.5rem;
    color: #4568dc;
}

.form-control,
.form-select {
    border-color: #e2e8f0;
}

.form-control:focus,
.form-select:focus {
    border-color: #4568dc;
    box-shadow: 0 0 0 0.2rem rgba(69, 104, 220, 0.25);
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #e2e8f0;
    color: #4568dc;
}

.form-label {
    font-weight: 500;
    font-size: 0.9rem;
    margin-bottom: 0.2rem;
}

.btn-primary-gradient {
    background: linear-gradient(to right, #4568dc, #3f5efb);
    border: none;
    color: white;
    box-shadow: 0 4px 10px rgba(69, 104, 220, 0.3);
    transition: all 0.3s;
}

.btn-primary-gradient:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(69, 104, 220, 0.4);
}

.btn-outline-secondary {
    border-color: #e2e8f0;
    color: #718096;
}

.btn-outline-secondary:hover {
    background-color: #f7fafc;
    color: #4a5568;
}

.cover-preview {
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 0.5rem;
    background-color: white;
    display: inline-block;
    transition: all 0.3s;
}

.cover-preview:hover {
    border-color: #4568dc;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
}

.row-compact>* {
    margin-bottom: 0.5rem;
}

.input-group.has-validation>.input-group-text {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}
</style>

<?php

require_once '../includes/footer.php';
?>
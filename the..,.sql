-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for library_db
DROP DATABASE IF EXISTS `library_db`;
CREATE DATABASE IF NOT EXISTS `library_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `library_db`;

-- Dumping structure for table library_db.bookmarks
DROP TABLE IF EXISTS `bookmarks`;
CREATE TABLE IF NOT EXISTS `bookmarks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_bookmark` (`user_id`,`book_id`),
  KEY `book_id` (`book_id`),
  CONSTRAINT `bookmarks_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookmarks_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table library_db.bookmarks: ~1 rows (approximately)

-- Dumping structure for table library_db.books
DROP TABLE IF EXISTS `books`;
CREATE TABLE IF NOT EXISTS `books` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `author` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `isbn` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `category` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `tahun_terbitan` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `publisher` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `available_copies` int NOT NULL DEFAULT '0',
  `total_copies` int NOT NULL DEFAULT '0',
  `description` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `image_path` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `isbn` (`isbn`),
  KEY `idx_books_category` (`category`)
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table library_db.books: ~200 rows (approximately)
REPLACE INTO `books` (`id`, `title`, `author`, `isbn`, `category`, `tahun_terbitan`, `publisher`, `available_copies`, `total_copies`, `description`, `created_at`, `updated_at`, `image_path`) VALUES
	(1, '9 bulan penuh keajaiban', 'ANNE MARIE MESER, Endy Maryam', '978-602-18792-3-8', 'Kesehatan', '2012', 'Mitra buku', 5, 5, '9 bulan penuh keajaiban : Panduan super lengkap bagi ibu hamil/ Anne Marie Meser; penerjemah, Endy Maryam', '2025-03-11 14:41:54', '2025-03-11 16:13:44', 'book_67d05028e3a02.jpg'),
	(2, '20 obat terpopuler penurun pertensi', 'soeryoko, hery', '9789792916577', 'Kesehatan', '2011', 'Andi', 6, 8, 'Dalam buku ini penulis menjelaskan seluk-beluk tekanan darah tinggi dengan bahasa yang sederhana dan efektif. Setiap orang diharapkan dapat memahami pengertian hipertensi dengan mudah. Beberapa tanaman obat menjadi bahasan terpenting dalm buku ini. 20 tanaman obat terbukti mampu mengontrol tekanan darah dengan baik. Untuk melengkapi buku ini, penulis menyajikan tanaman obat yang biasa digunakan masyarakat dan para herbalis (ahli herbal ) untuk menanggulangi tekanan darah tinggi. Selain itu kesaksian para pengguna tanaman obat juga diserta kan.\r\n\r\nBuku ini juga dilengkapi :\r\n- Cara mengonsumsi dan meracik\r\n- Cara pengolahan menjadi sirup, jus, dan lauk\r\n- Berbagai resep untuk berbagai tipe hipertensi (tanpa penyerta &amp; dengan pnyakit penyerta, seperti diabetes, maag, kolesterol, &amp; srtoke )', '2025-03-11 15:31:18', '2025-05-30 15:26:25', 'book_67d05746dcb66.JPG'),
	(3, '20 pahlawan nasional', 'Murni Irian Ningsih', '923.5 MUR 2', 'Sejarah', '2008', 'CV PG Kilat Jaya : Bandung', 2, 4, '', '2025-03-11 16:19:09', '2025-05-19 16:01:33', 'book_67d0627dcfea0.JPG'),
	(4, '26 Kiat Tubuh Sehat dan Bugar', '-', '9793632483', 'Kesehatan', '2007', 'Sunda Kelapa Pustaka / Gramedia Digital (versi e-book)', 7, 7, '', '2025-03-12 15:26:07', '2025-03-12 15:31:02', 'book_67d1a78f5816b.jpg'),
	(5, '60 Menu Makanan &amp; Minuman untuk Mengatasi &amp; Mencegah Penyakit Reumatik, Asam Urat, Ginjal, Maag, Prostat dan Pencernaan', 'Nining &amp; Anti', '-', 'Kesehatan', '2010', 'self-published', 2, 3, '60 Menu Makanan &amp; Minuman untuk Mengatasi &amp; Mencegah Penyakit Rematik', '2025-03-12 15:30:24', '2025-05-30 15:25:58', 'book_67d1a89087f71.jpg'),
	(6, '99 Misteri Terbesar di Dunia Sepanjang Masa', 'Saut Pasaribu', '978-602-98035-2-5', 'Misteri', '2011', 'New Diglossia', 9, 9, '', '2025-03-12 15:32:53', '2025-03-12 15:32:53', 'book_67d1a925c253c.JPG'),
	(7, '99,99% Tembus TOEIC', 'Achmad Fanani, Maisarah', '978-602-1129-80-7', 'Pendidikan', '2019', 'Indoliterasi (Desa Pustaka Group)', 19, 19, '', '2025-03-12 15:33:45', '2025-05-30 14:54:50', 'book_67d1a959d5356.JPG'),
	(8, '100+1 Cara Bahagia', 'Ainun Mahya &amp; Triyanto', '978-602-0808-22-2', 'Motivasi', '2016', 'Trans Idea Publishing / Trans Info Media (TIM)', 100, 101, '100+1 Cara Bahagia: 100 Inspirasi, 1 Aksi / 100 + 1 cara bahagia : 100 inspirasi, 1 aksi', '2025-03-12 15:34:49', '2025-05-30 15:25:30', 'book_67d1a9a3d3a4b.JPG'),
	(9, '333 Dongeng Binatang', 'Retno Wulandari dan Utami Widijati (pencerita kembali)', '978-623-7194-41-5', 'Fiksi', '2019', 'Desa Pustaka Indonesia / CV Tirta Buana Media', 334, 333, '335 Dongeng Binatang dari Seluruh Dunia', '2025-03-12 15:36:13', '2025-05-17 04:21:49', 'book_67d1a9ed6bd25.JPG'),
	(10, '1000 Mindset Berpikir Positif', 'Febri Surya', '978-602-1129-90-6', 'Motivasi', '2022', 'Indoliterasi (Desa Pustaka Group)', 511, 1000, '', '2025-03-12 15:36:54', '2025-05-30 15:25:36', 'book_67d1aa16b0fab.JPG'),
	(11, '1001 Pengetahuan Modern Untuk Anak', 'Imas Kurniasih', '978-602-97660-1-1', 'Pendidikan', '2018', 'Familia Pustaka Keluarga', 1000, 1001, '', '2025-03-12 15:38:34', '2025-05-30 15:25:40', 'book_67d1aa7a9052d.jpg'),
	(12, 'Datarnak Sapi', 'Drs. Sumarno Dwi Saputra, M.Si. / Roby Darmawan, M.Eng', '978-602-262-498-1', 'Peternakan', '2015', 'Graha Ilmu', 11, 11, 'Beternak Sapi Potong / Hasil Analisis Pengumpulan Data Produktivitas Ternak Sapi dan Kerbau', '2025-03-12 15:41:29', '2025-05-17 04:21:50', 'book_67d1ab2919af9.jpg'),
	(13, 'A History of China Sejarah Cina', 'J.A.G. Roberts', '9780674391129', 'Pendidikan', '1996', 'Harvard University Press', 10, 14, '', '2025-03-12 15:44:50', '2025-04-17 14:56:42', 'book_67d1abf240d11.JPG'),
	(14, 'A Short History of The World Sejarah Singkat Dunia', 'J.M. Roberts', '978-602-5270-54-1', 'Pendidikan', '2018', 'Blackstone Publishing / Oasis (Penerbit versi terjemahan Indonesia)', 13, 13, '', '2025-03-12 15:45:33', '2025-05-17 04:16:47', 'book_67d1ac9099e08.JPG'),
	(15, 'Seputar Desa Pakraman dan Adat Bali', 'I Wayan Surpha', '979-8496-32-9', 'Budaya', '2002', 'Pustaka Bali Post , BP , Balai Pustaka Depdikbud , Paramita', 12, 12, 'Seputar Desa Pakraman dan Adat Bali: Buku ini memaparkan adat, hukum adat, dan desa pakraman, dilengkapi dengan himpunan keputusan seminar kesatuan tafsir terhadap aspek-aspek agama Hindu dan Perda tentang Desa Pakraman. Buku ini menawarkan pemahaman mendalam tentang struktur desa adat, prajurut adat, dan peran desa adat dalam pemerintahan', '2025-05-19 13:57:05', '2025-05-19 13:57:05', 'book_682b38b12cd51.JPG'),
	(16, 'Mengapa Bali Disebut Bali?', 'I Ketut Wiana', '979-722-125-3', 'Sejarah', '2004', 'Paramita, Pustaka Bali Post', 3, 3, 'Menguraikan latar belakang filosofis Agama Hindu di Bali.', '2025-05-19 13:58:15', '2025-05-19 13:58:15', 'book_682b38f77512f.JPG'),
	(17, 'True Spirit Gita Wirjawan', 'Ira Puspito Rini', '978-602-7900-64-6', 'Biografi', '2013', 'Indoliterasi', 1, 1, 'Mengungkap sosok Gita Wirjawan dan kiprahnya di politik.', '2025-05-19 13:59:01', '2025-05-19 13:59:51', 'book_682b3957b4c49.JPG'),
	(18, 'Pak Harto di Mata Para Sahabat', 'Ira Puspito Rini', '978-602-1129-32-6', 'Biografi', '2014', 'Indoliterasi', 4, 4, 'Kumpulan serpihan kenangan tentang Pak Harto.', '2025-05-19 14:00:42', '2025-05-30 14:55:08', 'book_682b398a8c2fa.JPG'),
	(19, 'Ensiklopedi Mini Tokoh Seni Rupa Dunia', 'Febri Surya', '978-602-6559-03-6', 'Seni', '2020', 'Indoeduka', 15, 15, 'Ditujukan untuk pembelajaran siswa SMK tentang tokoh seni rupa dunia.', '2025-05-19 14:01:39', '2025-05-19 14:01:39', 'book_682b39c3d5648.JPG'),
	(20, 'Ensiklopedi Presiden RI Susilo Bambang Yudhoyono', 'Ade Makruf', '978-602-7874-99-2', 'Biografi', '2016', 'Ar-Ruzz Media', 6, 6, 'Biografi lengkap Susilo Bambang Yudhoyono.', '2025-05-19 14:02:31', '2025-05-30 14:57:27', 'book_682b39f702b3d.JPG'),
	(21, 'Falsafah Cinta Sejati Ibu Tien &amp; Pak Harto', 'Ira Tri Onggo', '978-602-7900-44-8', 'Biografi', '2013', 'Desa Pustaka, Indoliterasi', 0, 1, 'Kisah cinta dan pernikahan Pak Harto dan Ibu Tien.', '2025-05-19 14:04:13', '2025-05-30 15:26:32', 'book_682b3a5d4a0c3.JPG'),
	(22, 'Spirit Semut Ireng Jokowi Muka Metal Korongcong', 'R. Toto Sugiharto', '978-602-18792-0-7', 'Biografi', '2012', 'Bangkit', 5, 5, 'Mengapresiasi perjalanan politik Jokowi.', '2025-05-19 14:26:42', '2025-05-19 14:26:42', 'book_682b3fa22c388.JPG'),
	(23, 'Ensiklopedi Presiden RI Habibie', 'Ade Makruf', '978-602-7874-96-1, 978-602-7874-97-8', 'Biografi', '2016', 'Ar-Ruzz Media Group', 7, 7, 'Mengungkap kisah hidup B.J. Habibie.', '2025-05-19 14:30:04', '2025-05-19 14:30:04', 'book_682b406c8a9c4.JPG'),
	(24, 'Introduction to the Study of History Pengantar Ilmu Sejarah', 'CH.V. Langlois &amp; CH. Seignobos', '978-602-1129-94-4', 'Sejarah', '2015', 'Indoliterasi', 5, 5, 'Studi pendahuluan dalam mencari sejarah.', '2025-05-19 14:34:41', '2025-05-19 14:34:41', 'book_682b41810b52d.JPG'),
	(25, 'Cerita Cinta Telapak Tangan', 'Yasunari Kawabata', '978-602-391-267-4', 'Fiksi', '2016', 'Diva Press', 2, 2, 'Cerita-cerita sederhana dengan imajinasi mendalam.', '2025-05-19 14:37:01', '2025-05-19 14:37:01', 'book_682b420d6fbba.JPG'),
	(26, 'Geguritan Parikesit Prelaya', 'Jro Made M. Mardika', '978-602-204-473-4', 'Agama Hindu', '2015', 'Paramita', 1, 1, '', '2025-05-19 14:40:45', '2025-05-19 14:40:45', 'book_682b42ed6d60f.JPG'),
	(27, 'Geguritan Sucita', 'Ida Ketut Jlantik', '979-617-002-7', 'Budaya', '2018', 'Pusat Pembinaan dan Pengembangan Bahasa', 1, 1, 'Geguritan klasik Bali mengandung konsep budaya Bali.', '2025-05-19 14:42:35', '2025-05-30 15:06:22', 'book_682b435bb2721.JPG'),
	(28, 'Geguritan Begawan Bisma', 'I Wayan Pamit', '9789797225032', 'Agama Hindu', '2019', 'Paramita', 8, 9, 'Tradisi menyanyi metembang dari', '2025-05-19 14:44:46', '2025-05-30 14:55:09', 'book_682b46a17ca2d.JPG'),
	(29, 'Sucita Subudi', 'Ida Ketut Jlantik', '979 459 236 6', 'Sastra Bali', '1992', 'Pusat Pembinaan dan Pengembangan Bahasa', 1, 1, 'Geguritan Sucita Muah Subudhi berbahasa Bali.', '2025-05-19 14:47:36', '2025-05-19 14:47:36', 'book_682b44882f486.JPG'),
	(30, 'Sabar Itu Super!', 'Zian Farodis', '978-602-407-104-2', 'Motivasi', '2017', 'Laksana', 6, 12, 'Menceritakan tentang sabar dan aktivasinya dalam kehidupan.', '2025-05-19 14:58:53', '2025-05-30 15:25:21', 'book_682b472d68d6e.jpg'),
	(31, 'Budidaya Jambu Biji', 'Daru Wijayanti', '978-602-1129-59-3', 'Pertanian', '2016', 'Indopublika, Niaga Swadaya', 4, 4, 'Memberikan tip dan trik untuk berkebun jambu biji.', '2025-05-19 15:00:12', '2025-05-19 15:00:12', 'book_682b477cafb76.jpg'),
	(32, 'Stop! Hipertensi', 'Ulfa Nurrahmani, S.Kep.,Ns', '978-602-98663-8-4', 'Kesehatan', '2015', 'Familia', 1, 1, 'Panduan praktis untuk mencegah dan mengelola hipertensi.', '2025-05-19 15:01:18', '2025-05-19 15:01:18', 'book_682b47be20fda.jpg'),
	(33, 'Step by Step Ikan Hias Cupang', 'Devan Ramadhan', '978-602-98193-9-7', 'Hobi', '2015', 'Literindo', 5, 5, 'Langkah demi langkah budidaya ikan hias cupang.', '2025-05-19 15:02:44', '2025-05-19 15:02:44', 'book_682b4814035cd.jpg'),
	(34, 'Budidaya Kentang', 'Setiadi, Budi Ardiansyah, Faadhila A', '978-979-487-169-8', 'Pertanian', '2009', 'Tim Agro Mandiri', 3, 3, 'Membahas teknik budidaya kentang.', '2025-05-19 15:04:44', '2025-05-19 15:04:44', 'book_682b488c9d3e7.jpg'),
	(35, 'Pekerjaan Dasar Teknik Otomotif', 'Z. Furqon, S.T., Drs. Joko Pramono, Wahyu Tribudianti', '978-602-444-339-9', 'Teknik Otomotif', '2019', 'Indo Publika', 3, 3, 'Materi pembelajaran untuk siswa SMK tentang teknik otomotif.', '2025-05-19 15:06:12', '2025-05-19 15:06:12', 'book_682b48e464686.jpg'),
	(36, 'Ensiklopedi Penyakit Menular dan Infeksi', 'Sinta Sasika Novel', '978-602-98663-2-2', 'Kesehatan', '2020', 'Familia', 3, 3, 'Informasi tentang berbagai penyakit menular dan infeksi.', '2025-05-19 15:07:27', '2025-05-19 15:07:27', 'book_682b492fe7d2e.jpg'),
	(37, 'Cara Mudah Mengatasi Problem Anemia', 'Ikhsan Soebroto', '979-17095-8-0', 'Kesehatan', '2016', 'Jogja Bangkit', 1, 1, 'Pemahaman tentang anemia dan cara mudah mengatasinya.', '2025-05-19 15:09:31', '2025-05-19 15:09:31', 'book_682b49abd901d.jpg'),
	(38, 'Cara Mudah Mengatasi Problem Kolesterol', 'Ikhsan Soebroto', '979-17095-7-2', 'Kesehatan', '2017', 'Bangkit', 4, 4, 'Tip mudah dan murah mengendalikan kolesterol.', '2025-05-19 15:11:26', '2025-05-19 15:11:26', 'book_682b4a1e457b5.jpg'),
	(39, 'Akomodasi Perhotelan', 'Febri Surya', '978-602-352-045-9', 'Pariwisata', '2017', 'Indoeduka', 5, 7, 'Materi pembelajaran SMK tentang akomodasi perhotelan.', '2025-05-19 15:13:52', '2025-05-30 15:26:12', 'book_682b4ab0e8bba.jpg'),
	(40, 'Tata Boga Dasar untuk Pembelajaran SMK', 'Rifka', '978-979-29-6557-5', 'Tata Boga', '2019', 'Indo Publika', 4, 4, 'Materi pembelajaran SMK tentang tata boga.', '2025-05-19 15:15:57', '2025-05-19 15:15:57', 'book_682b4b2d16ec1.jpg'),
	(41, 'Manajemen Emosi Ibu Hamil', 'Bunda Mezy', '978-602-391-775-4', 'Parenting', '2016', 'Saufa', 5, 5, 'Pengaruh emosi ibu hamil terhadap anak dan cara mengelolanya.', '2025-05-19 15:18:24', '2025-05-19 15:18:24', 'book_682b4bc092a6d.jpg'),
	(42, 'Jutawan Kampus', 'Febriana Werdiningsih', '978-602-0808-18-5', 'Motivasi', '2016', 'Trans Idea Publishing', 14, 14, 'Cara memulai bisnis bagi mahasiswa.', '2025-05-19 15:19:24', '2025-05-19 15:19:24', 'book_682b4bfc0eb2b.jpg'),
	(43, 'Jus Sehat untuk Tumbuh Kembang Anak', 'Mama Lubna', '978-602-296-181-9', 'Kesehatan', '2016', 'Flash Books', 10, 10, 'Resep jus buah dan sayur untuk kesehatan dan kecerdasan anak.', '2025-05-19 15:20:34', '2025-05-19 15:20:34', 'book_682b4c428f03b.jpg'),
	(44, 'Buku Pedoman Keperawatan', 'Masruroh Hasyim, S.Kep., NS., M.Kes., Ann Isaacs', '978-602-1129-21-0, 979-448-655-8,', 'Keperawatan', '2014', 'Indoliterasi', 11, 11, 'Pedoman etika dan istilah dalam keperawatan.', '2025-05-19 15:21:38', '2025-05-19 15:21:38', 'book_682b4c82d07e9.jpg'),
	(45, 'Beternak Itik', 'Abdul Wakhid', '978-979-002-383-3', 'Peternakan', '2007', 'Tim Agro Mandiri', 16, 16, 'Cara beternak itik secara intensif.', '2025-05-19 15:22:55', '2025-05-19 15:22:55', 'book_682b4ccf00b34.jpg'),
	(46, 'Mesin Listrik', 'Agus Suhartono', '978-602-5923-40-1', 'Teknologi', '2014', 'Erlangga', 14, 14, 'Teori dasar tentang mesin-mesin listrik.', '2025-05-19 15:25:00', '2025-05-19 15:25:00', 'book_682b4d4c5bb00.jpg'),
	(47, 'Budidaya Bonsai', 'Iswarta Bima', '978-602-0869-52-0', 'Budidaya', '2016', 'Indoliterasi', 17, 17, 'Tahap-tahap penanaman dan pemeliharaan bonsai.', '2025-05-19 15:27:07', '2025-05-19 15:27:07', 'book_682b4dcb9f285.jpg'),
	(48, 'Kecil-Kecil Kaya Raya', 'Ira Puspitorini', '978-602-0869-16-2', 'Motivasi', '2020', 'Indoliterasi', 15, 15, '70 kisah inspiratif anak-anak sukses menjadi pengusaha.', '2025-05-19 15:35:00', '2025-05-19 15:35:00', 'book_682b4fa4315a8.jpg'),
	(49, 'Instalasi Penerangan', 'Nanda Siregar', '978-623-147-216-8', 'Teknologi', '2019', 'Istana Media', 18, 18, '', '2025-05-19 15:45:12', '2025-05-19 15:45:12', 'book_682b5208937c5.jpg'),
	(50, 'Mengawetkan sayur-sayuran', 'Hartanto', '978-623-340-348-1', 'Perkebunan', '2021', 'Angkasa', 14, 14, 'Buku tentang cara mengawetkan sayur-sayuran dengan bahan sederhana.', '2025-05-19 15:46:36', '2025-05-30 14:57:26', 'book_682b525c41dc3.JPG'),
	(51, 'MAKANAN SEHAT DAN BERGIZI BAGI TUBUH', 'Nur Wahyuningsih, Sri Tutur Martaningsih, Agus Supriyanto', '9786233162050', 'Kesehatan', '2021', 'K-Media', 16, 16, 'Buku yang bertujuan membantu guru dalam mempelajari konsep materi terkait makanan bergizi bagi tubuh.', '2025-05-19 15:49:09', '2025-05-19 15:49:09', 'book_682b52f50bf17.jpeg'),
	(52, 'Belajar Python untuk Pemula', 'Andi Susanto', '123-456-789-012-3', 'Pemrograman', '2021', 'Informatika Bandung', 5, 10, 'Buku ini dirancang untuk membantu pemula memahami dasar-dasar pemrograman Python dengan mudah dan cepat.', '2025-05-30 15:49:40', '2025-05-30 15:57:39', NULL),
	(53, 'Petualangan di Negeri Awan', 'Rina Kartika', '234-567-890-123-4', 'Fiksi Anak', '2022', 'Gramedia Pustaka Utama', 8, 8, 'Ikuti petualangan seru Mia dan teman-temannya di negeri awan yang ajaib dan penuh misteri.', '2025-05-30 15:49:40', '2025-05-30 15:57:39', NULL),
	(54, 'Manajemen Proyek Efektif', 'Budi Santoso', '345-678-901-234-5', 'Bisnis', '2020', 'Elex Media Komputindo', 3, 5, 'Panduan praktis untuk mengelola proyek dari awal hingga akhir dengan hasil yang maksimal.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(55, 'Kumpulan Cerpen Senja', 'Dewi Lestari', '456-789-012-345-6', 'Sastra', '2019', 'Bentang Pustaka', 10, 12, 'Sebuah antologi cerpen yang merangkai kisah-kisah senja dengan berbagai nuansa emosi.', '2025-05-30 15:49:40', '2025-05-30 15:57:37', NULL),
	(56, 'Dasar-Dasar Jaringan Komputer', 'Agus Purnomo', '567-890-123-456-7', 'Teknologi', '2023', 'Andi Offset', 7, 7, 'Materi lengkap mengenai konsep dasar jaringan komputer, topologi, dan protokol.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(57, 'Sejarah Nusantara Kuno', 'Prof. Dr. Suryono', '678-901-234-567-8', 'Sejarah', '2018', 'Penerbit Buku Kompas', 4, 6, 'Mengungkap fakta dan mitos peradaban kuno di Nusantara sebelum era kolonial.', '2025-05-30 15:49:40', '2025-05-30 15:57:36', NULL),
	(58, 'Memasak Lezat Setiap Hari', 'Chef Renata', '789-012-345-678-9', 'Kuliner', '2022', 'Femina Group', 12, 15, 'Kumpulan resep masakan praktis dan lezat untuk hidangan keluarga sehari-hari.', '2025-05-30 15:49:40', '2025-05-30 15:57:35', NULL),
	(59, 'Algoritma dan Struktur Data', 'Indra Wijaya', '890-123-456-789-0', 'Pemrograman', '2021', 'Gava Media', 6, 9, 'Pembahasan mendalam tentang algoritma penting dan struktur data yang efisien.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(60, 'Dongeng Sebelum Tidur Terbaik', 'Kak Seto', '901-234-567-890-1', 'Fiksi Anak', '2020', 'Mizan Pustaka', 9, 10, 'Cerita-cerita pengantar tidur yang mendidik dan menghibur untuk anak-anak.', '2025-05-30 15:49:40', '2025-05-30 15:57:34', NULL),
	(61, 'Strategi Pemasaran Digital', 'Rahmat Hidayat', '012-345-678-901-2', 'Bisnis', '2023', 'Salemba Empat', 5, 5, 'Teknik dan strategi terbaru dalam pemasaran digital untuk meningkatkan penjualan.', '2025-05-30 15:49:40', '2025-05-30 15:57:32', NULL),
	(62, 'Puisi Hujan Bulan Juni', 'Sapardi Djoko Damono', '112-233-445-566-7', 'Sastra', '1994', 'Grasindo', 15, 20, 'Kumpulan puisi legendaris yang tak lekang oleh waktu.', '2025-05-30 15:49:40', '2025-05-30 15:57:32', NULL),
	(63, 'Pengantar Kecerdasan Buatan', 'Dr. Fitriani', '223-344-556-677-8', 'Teknologi', '2022', 'Deepublish', 3, 5, 'Konsep dasar, aplikasi, dan perkembangan terkini dalam bidang kecerdasan buatan.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(64, 'Atlas Dunia untuk Pelajar', 'Tim Geografi', '334-455-667-788-9', 'Pendidikan', '2021', 'Erlangga', 10, 10, 'Peta dan informasi lengkap mengenai negara-negara di seluruh dunia.', '2025-05-30 15:49:40', '2025-05-30 15:57:31', NULL),
	(65, 'Kisah Inspiratif Pengusaha Sukses', 'Merry Riana', '445-566-778-899-0', 'Motivasi', '2019', 'MD Publishing', 7, 8, 'Belajar dari kisah nyata para pengusaha yang merintis bisnis dari nol hingga sukses.', '2025-05-30 15:49:40', '2025-05-30 15:57:30', NULL),
	(66, 'Pemrograman Web dengan PHP dan MySQL', 'Lukman Hakim', '556-677-889-900-1', 'Pemrograman', '2020', 'Lokomedia', 4, 7, 'Tutorial lengkap membangun aplikasi web dinamis menggunakan PHP dan MySQL.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(67, 'Legenda Sangkuriang', 'Anonim (Adaptasi)', '667-788-990-011-2', 'Folklor', '2021', 'Balai Pustaka', 9, 12, 'Cerita rakyat terkenal dari Jawa Barat tentang asal-usul Gunung Tangkuban Perahu.', '2025-05-30 15:49:40', '2025-05-30 15:57:29', NULL),
	(68, 'Investasi Saham untuk Pemula', 'Ryan Filbert', '778-899-001-122-3', 'Keuangan', '2022', 'Media Kita', 6, 6, 'Panduan mudah memahami dunia investasi saham dan memulai investasi pertama Anda.', '2025-05-30 15:49:40', '2025-05-30 15:57:29', NULL),
	(69, 'Kritik Sastra Kontemporer Indonesia', 'Prof. Dr. Melani Budianta', '889-900-112-233-4', 'Kritik Sastra', '2018', 'Yayasan Obor Indonesia', 2, 4, 'Analisis mendalam terhadap karya-karya sastra Indonesia modern dan kontemporer.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(70, 'Cloud Computing: Konsep dan Implementasi', 'Bambang Hariyanto', '990-011-223-344-5', 'Teknologi', '2023', 'Penerbit Andi', 5, 8, 'Penjelasan komprehensif mengenai teknologi cloud computing dan cara implementasinya.', '2025-05-30 15:49:40', '2025-05-30 15:57:28', NULL),
	(71, 'Misteri Pulau Hantu', 'Enid Blyton (Terjemahan)', '001-122-334-455-6', 'Fiksi Remaja', '2019', 'Nourah Books', 11, 11, 'Petualangan Lima Sekawan memecahkan misteri di sebuah pulau terpencil.', '2025-05-30 15:49:40', '2025-05-30 15:57:27', NULL),
	(72, 'Filosofi Teras', 'Henry Manampiring', '121-232-343-454-5', 'Filsafat', '2018', 'Penerbit Buku Kompas', 20, 25, 'Mengadaptasi filsafat Stoa untuk kehidupan modern yang lebih tenang dan bahagia.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(73, 'Tata Bahasa Baku Bahasa Indonesia', 'Tim Penyusun Kemdikbud', '232-343-454-565-6', 'Bahasa', '2017', 'Kementerian Pendidikan dan Kebudayaan', 10, 15, 'Panduan resmi mengenai tata bahasa Indonesia yang baik dan benar.', '2025-05-30 15:49:40', '2025-05-30 15:57:26', NULL),
	(74, 'Bumi Manusia', 'Pramoedya Ananta Toer', '343-454-565-676-7', 'Sastra Klasik', '1980', 'Hasta Mitra', 18, 20, 'Novel epik yang menggambarkan perjuangan di era kolonial Hindia Belanda.', '2025-05-30 15:49:40', '2025-05-30 15:57:25', NULL),
	(75, 'Seni Berbicara di Depan Umum', 'Dale Carnegie (Terjemahan)', '454-565-676-787-8', 'Pengembangan Diri', '2020', 'Bhuana Ilmu Populer', 9, 10, 'Teknik-teknik efektif untuk meningkatkan kepercayaan diri dan kemampuan berbicara di depan publik.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(76, 'Matematika Diskrit dan Aplikasinya', 'Rinaldi Munir', '565-676-787-898-9', 'Matematika', '2019', 'Informatika', 5, 7, 'Konsep matematika diskrit yang fundamental untuk ilmu komputer dan informatika.', '2025-05-30 15:49:40', '2025-05-30 15:57:24', NULL),
	(77, 'Jejak Langkah', 'Pramoedya Ananta Toer', '676-787-898-909-0', 'Sastra Klasik', '1985', 'Hasta Mitra', 12, 15, 'Kelanjutan kisah Minke dalam Tetralogi Buru.', '2025-05-30 15:49:40', '2025-05-30 15:57:23', NULL),
	(78, 'The Hobbit', 'J.R.R. Tolkien (Terjemahan)', '787-898-909-010-1', 'Fantasi', '2015', 'Gramedia Pustaka Utama', 14, 14, 'Petualangan Bilbo Baggins menuju Gunung Sunyi.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(79, 'Pengantar Ekonomi Makro', 'Dr. Sadono Sukirno', '898-909-010-121-2', 'Ekonomi', '2016', 'Rajawali Pers', 7, 9, 'Dasar-dasar teori ekonomi makro dan analisisnya terhadap perekonomian.', '2025-05-30 15:49:40', '2025-05-30 15:57:21', NULL),
	(80, 'Rumah Kaca', 'Pramoedya Ananta Toer', '909-010-121-232-3', 'Sastra Klasik', '1988', 'Hasta Mitra', 10, 12, 'Bagian terakhir dari Tetralogi Buru.', '2025-05-30 15:49:40', '2025-05-30 15:57:20', NULL),
	(81, 'Laut Bercerita', 'Leila S. Chudori', '010-121-232-343-4', 'Fiksi Sejarah', '2017', 'Kepustakaan Populer Gramedia', 15, 18, 'Kisah tentang aktivis mahasiswa di era Orde Baru.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(82, 'Statistika untuk Penelitian', 'Prof. Dr. Sugiyono', '120-231-342-453-5', 'Statistika', '2019', 'Alfabeta', 6, 8, 'Metode statistika yang aplikatif untuk berbagai jenis penelitian.', '2025-05-30 15:49:40', '2025-05-30 15:57:18', NULL),
	(83, 'Laskar Pelangi', 'Andrea Hirata', '231-342-453-564-6', 'Novel Inspiratif', '2005', 'Bentang Pustaka', 25, 30, 'Kisah inspiratif anak-anak Belitung yang berjuang meraih mimpi.', '2025-05-30 15:49:40', '2025-05-30 15:57:18', NULL),
	(84, 'Sapiens: Riwayat Singkat Umat Manusia', 'Yuval Noah Harari (Terjemahan)', '342-453-564-675-7', 'Non-Fiksi Populer', '2018', 'Kepustakaan Populer Gramedia', 12, 15, 'Perjalanan evolusi manusia dari zaman batu hingga era modern.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(85, 'Cantik itu Luka', 'Eka Kurniawan', '453-564-675-786-8', 'Sastra Kontemporer', '2002', 'Gramedia Pustaka Utama', 9, 11, 'Novel magis realis yang penuh dengan kisah tragis dan ironi.', '2025-05-30 15:49:40', '2025-05-30 15:57:17', NULL),
	(86, 'Atomic Habits', 'James Clear (Terjemahan)', '564-675-786-897-9', 'Pengembangan Diri', '2019', 'Gramedia Pustaka Utama', 22, 25, 'Cara mudah membangun kebiasaan baik dan menghilangkan kebiasaan buruk.', '2025-05-30 15:49:40', '2025-05-30 15:57:16', NULL),
	(87, 'Gadis Kretek', 'Ratih Kumala', '675-786-897-908-0', 'Fiksi Sejarah', '2012', 'Gramedia Pustaka Utama', 10, 12, 'Kisah cinta dan industri kretek di Indonesia.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(88, 'Teknik Fotografi Digital', 'Wahyu Dharsito', '786-897-908-019-1', 'Fotografi', '2021', 'Elex Media Komputindo', 7, 9, 'Panduan lengkap menguasai teknik fotografi menggunakan kamera digital.', '2025-05-30 15:49:40', '2025-05-30 15:57:16', NULL),
	(89, 'Negeri 5 Menara', 'Ahmad Fuadi', '897-908-019-120-2', 'Novel Inspiratif', '2009', 'Gramedia Pustaka Utama', 18, 20, 'Perjuangan Alif meraih impian di Pondok Madani.', '2025-05-30 15:49:40', '2025-05-30 15:57:15', NULL),
	(90, 'Filosofi Kopi', 'Dewi Lestari', '908-019-120-231-3', 'Kumpulan Cerita', '2006', 'Truedee Books & GagasMedia', 11, 14, 'Kumpulan cerita pendek yang bertemakan kopi dan filosofi di baliknya.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(91, 'Ayat-Ayat Cinta', 'Habiburrahman El Shirazy', '019-120-231-342-4', 'Novel Religi', '2004', 'Republika Penerbit & Pesantren Karya', 16, 20, 'Kisah cinta Islami yang berlatar di Mesir.', '2025-05-30 15:49:40', '2025-05-30 15:57:14', NULL),
	(92, 'Perahu Kertas', 'Dewi Lestari', '129-230-341-452-5', 'Novel Populer', '2009', 'Bentang Pustaka & Truedee Books', 14, 18, 'Kisah Keenan dan Kugy dalam mengejar mimpi dan cinta.', '2025-05-30 15:49:40', '2025-05-30 15:57:14', NULL),
	(93, 'Manusia Setengah Salmon', 'Raditya Dika', '239-340-451-562-6', 'Komedi', '2011', 'GagasMedia', 10, 12, 'Kumpulan tulisan komedi khas Raditya Dika.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(94, 'Supernova: Ksatria, Puteri, dan Bintang Jatuh', 'Dewi Lestari', '349-450-561-672-7', 'Fiksi Ilmiah', '2001', 'Truedee Books', 8, 10, 'Seri pertama dari heptalogi Supernova.', '2025-05-30 15:49:40', '2025-05-30 15:57:13', NULL),
	(95, 'Marmut Merah Jambu', 'Raditya Dika', '459-560-671-782-8', 'Komedi', '2010', 'GagasMedia', 9, 11, 'Kisah-kisah lucu dari pengalaman pribadi Raditya Dika.', '2025-05-30 15:49:40', '2025-05-30 15:57:12', NULL),
	(96, 'Ronggeng Dukuh Paruk', 'Ahmad Tohari', '569-670-781-892-9', 'Sastra Indonesia', '1982', 'Gramedia Pustaka Utama', 7, 9, 'Trilogi novel yang mengangkat kehidupan penari ronggeng.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(97, 'Kambing Jantan: Sebuah Catatan Harian Pelajar Bodoh', 'Raditya Dika', '679-780-891-902-0', 'Komedi', '2005', 'GagasMedia', 12, 15, 'Buku pertama Raditya Dika yang berisi catatan hariannya.', '2025-05-30 15:49:40', '2025-05-30 15:57:11', NULL),
	(98, 'Mockingjay (The Hunger Games, #3)', 'Suzanne Collins (Terjemahan)', '789-890-901-012-1', 'Fiksi Ilmiah Remaja', '2014', 'Gramedia Pustaka Utama', 10, 13, 'Bagian akhir dari trilogi The Hunger Games.', '2025-05-30 15:49:40', '2025-05-30 15:57:11', NULL),
	(99, 'Harry Potter dan Batu Bertuah', 'J.K. Rowling (Terjemahan)', '899-900-011-122-2', 'Fantasi Anak', '2000', 'Gramedia Pustaka Utama', 20, 25, 'Awal mula petualangan Harry Potter di Hogwarts.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(100, 'Koala Kumal', 'Raditya Dika', '900-011-122-233-3', 'Komedi', '2015', 'GagasMedia', 11, 14, 'Kisah patah hati dan perjalanan move on Raditya Dika.', '2025-05-30 15:49:40', '2025-05-30 15:57:09', NULL),
	(101, 'Sebuah Seni untuk Bersikap Bodo Amat', 'Mark Manson (Terjemahan)', '011-122-233-344-4', 'Pengembangan Diri', '2018', 'Gramedia Widiasarana Indonesia', 25, 30, 'Pendekatan yang berlawanan dengan intuisi untuk menjalani kehidupan yang baik.', '2025-05-30 15:49:40', '2025-05-30 15:57:09', NULL),
	(102, 'Belajar JavaScript Modern', 'Alex Chandra', '111-222-333-444-0', 'Pemrograman', '2023', 'Penerbit Informatika', 9, 12, 'Panduan komprehensif untuk menguasai JavaScript ES6+', '2025-05-30 15:49:40', '2025-05-30 15:57:08', NULL),
	(103, 'Fisika Dasar untuk Universitas', 'Prof. Yohanes Surya', '222-333-444-555-1', 'Sains', '2020', 'Kandel', 6, 10, 'Materi fisika dasar yang disajikan secara sistematis dan mudah dipahami.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(104, 'Biologi Sel dan Molekuler', 'Dr. Anisa Rahman', '333-444-555-666-2', 'Biologi', '2021', 'Erlangga Medika', 4, 7, 'Pembahasan mendalam tentang struktur dan fungsi sel serta proses molekuler.', '2025-05-30 15:49:40', '2025-05-30 15:57:07', NULL),
	(105, 'Kimia Organik Lanjutan', 'Prof. Siti Aminah', '444-555-666-777-3', 'Kimia', '2019', 'ITB Press', 3, 5, 'Materi lanjutan kimia organik mencakup mekanisme reaksi dan sintesis.', '2025-05-30 15:49:40', '2025-05-30 15:57:07', NULL),
	(106, 'Pengantar Ilmu Politik', 'Miriam Budiardjo', '555-666-777-888-4', 'Ilmu Sosial', '2008', 'Gramedia Pustaka Utama', 12, 15, 'Buku teks klasik yang membahas konsep-konsep dasar ilmu politik.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(107, 'Sosiologi: Suatu Pengantar', 'Soerjono Soekanto', '666-777-888-999-5', 'Sosiologi', '2012', 'Rajawali Pers', 8, 10, 'Dasar-dasar ilmu sosiologi, teori, dan metode penelitian sosial.', '2025-05-30 15:49:40', '2025-05-30 15:57:06', NULL),
	(108, 'Antropologi Budaya Indonesia', 'Koentjaraningrat', '777-888-999-000-6', 'Antropologi', '1990', 'Djambatan', 7, 9, 'Kajian mendalam tentang keragaman budaya suku-suku bangsa di Indonesia.', '2025-05-30 15:49:40', '2025-05-30 15:57:05', NULL),
	(109, 'Hukum Tata Negara', 'Prof. Jimly Asshiddiqie', '888-999-000-111-7', 'Hukum', '2010', 'Sinar Grafika', 5, 8, 'Analisis komprehensif mengenai sistem ketatanegaraan Indonesia.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(110, 'Psikologi Perkembangan Anak', 'Dr. Elizabeth Hurlock (Terjemahan)', '999-000-111-222-8', 'Psikologi', '2015', 'Erlangga', 10, 12, 'Tahapan perkembangan anak dari masa bayi hingga remaja.', '2025-05-30 15:49:40', '2025-05-30 15:57:01', NULL),
	(111, 'Dasar Akuntansi Keuangan', 'Sofyan Syafri Harahap', '000-111-222-333-9', 'Akuntansi', '2018', 'Salemba Empat', 11, 14, 'Prinsip-prinsip dasar akuntansi dan penyusunan laporan keuangan.', '2025-05-30 15:49:40', '2025-05-30 15:57:01', NULL),
	(112, 'Manajemen Sumber Daya Manusia Strategik', 'Prof. Veithzal Rivai', '101-212-323-434-0', 'Manajemen', '2017', 'Rajawali Pers', 6, 9, 'Pendekatan strategis dalam mengelola sumber daya manusia di organisasi.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(113, 'Ekonomi Pembangunan', 'Michael P. Todaro (Terjemahan)', '212-323-434-545-1', 'Ekonomi', '2011', 'Erlangga', 4, 6, 'Teori dan isu-isu terkini dalam ekonomi pembangunan di negara berkembang.', '2025-05-30 15:49:40', '2025-05-30 15:57:00', NULL),
	(114, 'Komunikasi Antar Pribadi', 'Joseph A. DeVito (Terjemahan)', '323-434-545-656-2', 'Komunikasi', '2013', 'Karisma Publishing Group', 9, 11, 'Keterampilan dan konsep dasar dalam komunikasi interpersonal yang efektif.', '2025-05-30 15:49:40', '2025-05-30 15:56:59', NULL),
	(115, 'Sejarah Pemikiran Ekonomi', 'Prof. Deliarnov', '434-545-656-767-3', 'Sejarah Ekonomi', '2016', 'Erlangga', 3, 5, 'Perkembangan pemikiran ekonomi dari era klasik hingga modern.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(116, 'Desain Grafis dengan Adobe Illustrator', 'Madcoms', '545-656-767-878-4', 'Desain', '2022', 'Andi Publisher', 8, 10, 'Tutorial praktis menggunakan Adobe Illustrator untuk berbagai keperluan desain.', '2025-05-30 15:49:40', '2025-05-30 15:56:58', NULL),
	(117, 'Pengantar Studi Hubungan Internasional', 'Aleksius Jemadu', '656-767-878-989-5', 'Hubungan Internasional', '2008', 'Graha Ilmu', 5, 7, 'Konsep, teori, dan aktor dalam studi hubungan internasional.', '2025-05-30 15:49:40', '2025-05-30 15:56:58', NULL),
	(118, 'Teknik Menulis Karya Ilmiah', 'Dr. Madyo Ekosusilo', '767-878-989-090-6', 'Pendidikan', '2019', 'Remaja Rosdakarya', 10, 13, 'Panduan langkah demi langkah dalam menyusun skripsi, tesis, dan disertasi.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(119, 'Kewirausahaan: Teori dan Praktik', 'Geoffrey G. Meredith (Terjemahan)', '878-989-090-101-7', 'Kewirausahaan', '2000', 'Pustaka Binaman Pressindo', 7, 9, 'Dasar-dasar kewirausahaan dan strategi memulai bisnis.', '2025-05-30 15:49:40', '2025-05-30 15:56:57', NULL),
	(120, 'Budidaya Tanaman Hidroponik', 'Ir. Agus Kardinan', '989-090-101-212-8', 'Pertanian', '2021', 'Penebar Swadaya', 12, 15, 'Teknik modern budidaya tanaman tanpa tanah menggunakan larutan nutrisi.', '2025-05-30 15:49:40', '2025-05-30 15:56:56', NULL),
	(121, 'Dasar-Dasar Jurnalistik', 'Asep Syamsul M. Romli', '090-101-212-323-9', 'Jurnalistik', '2010', 'Simbiosa Rekatama Media', 6, 8, 'Pengantar dunia jurnalistik, teknik peliputan, dan penulisan berita.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(122, 'Manajemen Pemasaran Global', 'Warren J. Keegan (Terjemahan)', '191-202-313-424-0', 'Pemasaran', '2014', 'Indeks', 4, 6, 'Strategi dan tantangan dalam pemasaran produk di pasar global.', '2025-05-30 15:49:40', '2025-05-30 15:56:55', NULL),
	(123, 'Arsitektur Komputer Modern', 'William Stallings (Terjemahan)', '292-303-414-525-1', 'Teknik Komputer', '2018', 'Salemba Teknika', 3, 5, 'Organisasi dan arsitektur sistem komputer kontemporer.', '2025-05-30 15:49:40', '2025-05-30 15:56:55', NULL),
	(124, 'Sistem Operasi: Konsep dan Desain', 'Abraham Silberschatz (Terjemahan)', '393-404-515-626-2', 'Sistem Operasi', '2016', 'Andi Publisher & Wiley', 9, 12, 'Prinsip-prinsip dasar sistem operasi modern.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(125, 'Pemrograman Berorientasi Objek dengan Java', 'Bjarne Stroustrup (Adaptasi)', '494-505-616-727-3', 'Pemrograman Java', '2022', 'Penerbit ITB', 7, 10, 'Konsep PBO dan implementasinya menggunakan bahasa Java.', '2025-05-30 15:49:40', '2025-05-30 15:56:54', NULL),
	(126, 'Belajar Data Science dari Nol', 'Dr. Kevin Pratama', '595-606-717-828-4', 'Data Science', '2023', 'Gramedia Digital', 10, 10, 'Panduan bagi pemula untuk memulai karir di bidang data science.', '2025-05-30 15:49:40', '2025-05-30 15:56:53', NULL),
	(127, 'Keamanan Siber untuk Semua', 'Eva Maulina', '696-707-818-929-5', 'Keamanan Siber', '2021', 'Kompas Ilmu', 5, 8, 'Tips praktis menjaga keamanan data dan privasi di dunia digital.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(128, 'Sejarah Filsafat Barat', 'Bertrand Russell (Terjemahan)', '797-808-919-030-6', 'Filsafat Sejarah', '2007', 'Pustaka Pelajar', 4, 6, 'Uraian komprehensif mengenai perkembangan filsafat di dunia Barat.', '2025-05-30 15:49:40', '2025-05-30 15:56:52', NULL),
	(129, 'Pengantar Teori Sastra', 'Terry Eagleton (Terjemahan)', '898-909-010-141-7', 'Teori Sastra', '2011', 'Jalasutra', 3, 5, 'Pengenalan berbagai pendekatan teoretis dalam studi sastra.', '2025-05-30 15:49:40', '2025-05-30 15:56:52', NULL),
	(130, 'Revolusi Industri 4.0 dan Dampaknya', 'Prof. Rhenald Kasali', '909-010-121-252-8', 'Teknologi Industri', '2019', 'Gramedia Pustaka Utama', 9, 12, 'Analisis mengenai perubahan besar akibat Revolusi Industri Keempat.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(131, 'Panduan Praktis SEO untuk Website', 'Deni Iskandar', '010-121-232-363-9', 'SEO', '2022', 'Mediakita', 8, 10, 'Teknik optimasi mesin pencari agar website mudah ditemukan.', '2025-05-30 15:49:40', '2025-05-30 15:56:51', NULL),
	(132, 'Artificial Neural Network', 'Simon Haykin (Terjemahan)', '123-234-345-456-0', 'AI', '2018', 'Andi Publisher', 5, 7, 'Dasar-dasar Jaringan Saraf Tiruan dan aplikasinya.', '2025-05-30 15:49:40', '2025-05-30 15:56:50', NULL),
	(133, 'Deep Learning dengan Python', 'Ian Goodfellow (Adaptasi)', '234-345-456-567-1', 'Deep Learning', '2021', 'Penerbit Informatika', 6, 9, 'Implementasi berbagai model deep learning menggunakan Python.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(134, 'Blockchain: Teknologi Masa Depan', 'Don Tapscott (Terjemahan)', '345-456-567-678-2', 'Blockchain', '2020', 'Mizan Publika', 4, 6, 'Memahami konsep dan potensi teknologi blockchain.', '2025-05-30 15:49:40', '2025-05-30 15:56:49', NULL),
	(135, 'Quantum Computing: Pengantar Singkat', 'Prof. Ardian Saputra', '456-567-678-789-3', 'Komputasi Kuantum', '2023', 'UGM Press', 3, 5, 'Pengenalan prinsip dasar dan prospek komputasi kuantum.', '2025-05-30 15:49:40', '2025-05-30 15:56:49', NULL),
	(136, 'Bioinformatika untuk Pemula', 'Dr. Maria Lusiana', '567-678-789-890-4', 'Bioinformatika', '2022', 'IPB Press', 7, 10, 'Aplikasi ilmu komputer dalam analisis data biologi.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(137, 'Etika Profesi Teknologi Informasi', 'Assoc. Prof. Eko Indrajit', '678-789-890-901-5', 'Etika TI', '2019', 'Andi Publisher', 5, 8, 'Pembahasan mengenai etika dan tanggung jawab profesional di bidang TI.', '2025-05-30 15:49:40', '2025-05-30 15:56:44', NULL),
	(138, 'Manajemen Risiko Proyek IT', 'Rita Mulcahy (Adaptasi)', '789-890-901-012-6', 'Manajemen Proyek TI', '2021', 'Penerbit PPM', 6, 9, 'Strategi mengidentifikasi dan mengelola risiko dalam proyek teknologi informasi.', '2025-05-30 15:49:40', '2025-05-30 15:56:43', NULL),
	(139, 'Internet of Things (IoT): Konsep Dasar', 'Samuel Greengard (Terjemahan)', '890-901-012-123-7', 'IoT', '2020', 'Elex Media', 4, 7, 'Pengenalan teknologi Internet of Things dan berbagai aplikasinya.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(140, 'Cyber Law di Indonesia', 'Dr. Edmon Makarim', '901-012-123-234-8', 'Hukum Siber', '2018', 'Rajawali Pers', 3, 5, 'Aspek hukum terkait penggunaan teknologi informasi dan transaksi elektronik di Indonesia.', '2025-05-30 15:49:40', '2025-05-30 15:56:41', NULL),
	(141, 'Kriptografi Klasik dan Modern', 'William Stallings (Adaptasi)', '012-123-234-345-9', 'Kriptografi', '2022', 'Penerbit Erlangga', 8, 11, 'Teori dan praktik enkripsi data untuk keamanan informasi.', '2025-05-30 15:49:40', '2025-05-30 15:56:41', NULL),
	(142, 'Analisis Big Data dengan Hadoop', 'Tom White (Terjemahan)', '112-223-334-445-0', 'Big Data', '2019', 'O Reilly Media & Andi', 5, 7, 'Menggunakan ekosistem Hadoop untuk memproses dan menganalisis data skala besar.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(143, 'Machine Learning untuk Deteksi Anomali', 'Charu C. Aggarwal (Adaptasi)', '221-332-443-554-1', 'Machine Learning', '2023', 'Springer & Informatika', 6, 9, 'Penerapan teknik machine learning untuk mendeteksi pola data yang tidak biasa.', '2025-05-30 15:49:40', '2025-05-30 15:56:40', NULL),
	(144, 'Pemrosesan Bahasa Alami (NLP)', 'Daniel Jurafsky (Terjemahan)', '331-442-553-664-2', 'NLP', '2020', 'Prentice Hall & Andi', 4, 6, 'Konsep dasar dan teknik dalam Natural Language Processing.', '2025-05-30 15:49:40', '2025-05-30 15:56:39', NULL),
	(145, 'Visi Komputer: Teori dan Aplikasi', 'Richard Szeliski (Terjemahan)', '441-552-663-774-3', 'Computer Vision', '2021', 'Cambridge University Press & Erlangga', 3, 5, 'Pengantar komprehensif untuk bidang visi komputer.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(146, 'Robotika Cerdas', 'Prof. Djoko Purwanto', '551-662-773-884-14', 'Robotika', '2018', 'Graha Ilmu', 7, 10, 'Pengembangan robot yang mampu berinteraksi dan belajar dari lingkungannya.', '2025-05-30 15:49:40', '2025-05-30 15:56:38', NULL),
	(147, 'Game Development dengan Unity', 'Jeremy Gibson Bond', '661-772-883-994-15', 'Game Development', '2022', 'Packt Publishing & Elex Media', 9, 12, 'Membangun game 2D dan 3D menggunakan game engine Unity.', '2025-05-30 15:49:40', '2025-05-30 15:56:38', NULL),
	(148, 'Realitas Virtual dan Augmented Reality', 'Steven M. LaValle (Adaptasi)', '771-882-993-004-16', 'VR/AR', '2020', 'Penerbit Andi', 5, 8, 'Konsep dan teknologi di balik Virtual Reality dan Augmented Reality.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(149, 'Sistem Pendukung Keputusan Cerdas', 'Efraim Turban (Terjemahan)', '8181-992-003-114-7', 'Sistem Pendukung Keputusan', '2019', 'Pearson & Salemba Infotek', 6, 9, 'Membangun sistem yang membantu pengambilan keputusan berbasis data dan AI.', '2025-05-30 15:49:40', '2025-05-30 15:56:37', NULL),
	(150, 'E-commerce: Strategi dan Implementasi', 'Kenneth C. Laudon (Terjemahan)', '991-002-113-2124-8', 'E-commerce', '2021', 'Prentice Hall & Andi', 4, 7, 'Panduan membangun dan mengelola bisnis e-commerce yang sukses.', '2025-05-30 15:49:40', '2025-05-30 15:56:36', NULL),
	(151, 'Manajemen Basis Data Lanjutan', 'Carlos Coronel (Terjemahan)', '002-113-224-335-9', 'Basis Data', '2018', 'Cengage Learning & Salemba Teknika', 7, 10, 'Topik lanjutan dalam desain, implementasi, dan manajemen basis data.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(152, 'Pengantar Ilmu Komunikasi Visual', 'Danton Sihombing', '113-224-335-446-0', 'Desain Komunikasi Visual', '2015', 'Gramedia Pustaka Utama', 9, 12, 'Dasar-dasar teori dan praktik dalam desain komunikasi visual.', '2025-05-30 15:49:40', '2025-05-30 15:56:36', NULL),
	(153, 'Psikologi Kognitif: Proses Mental', 'Robert L. Solso (Terjemahan)', '224-335-446-557-1', 'Psikologi Kognitif', '2017', 'Erlangga', 5, 8, 'Studi tentang bagaimana manusia memproses informasi, belajar, dan berpikir.', '2025-05-30 15:49:40', '2025-05-30 15:56:35', NULL),
	(154, 'Filsafat Ilmu: Sebuah Pengantar Populer', 'Jujun S. Suriasumantri', '335-446-557-668-2', 'Filsafat Ilmu', '1982', 'Pustaka Sinar Harapan', 12, 15, 'Pengenalan yang mudah dicerna mengenai hakikat ilmu pengetahuan.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(155, 'Teori Akuntansi Keuangan', 'William R. Scott (Terjemahan)', '446-557-668-779-3', 'Teori Akuntansi', '2019', 'Salemba Empat', 6, 9, 'Kerangka teoretis yang mendasari praktik akuntansi keuangan.', '2025-05-30 15:49:40', '2025-05-30 15:56:34', NULL),
	(156, 'Kalkulus Lanjut untuk Teknik', 'Erwin Kreyszig (Terjemahan)', '557-668-779-880-4', 'Kalkulus', '2016', 'Penerbit Erlangga', 4, 7, 'Materi kalkulus lanjutan yang relevan untuk mahasiswa teknik.', '2025-05-30 15:49:40', '2025-05-30 15:56:33', NULL),
	(157, 'Mekanika Fluida Dasar', 'Bruce R. Munson (Terjemahan)', '668-779-880-991-5', 'Teknik Mesin', '2018', 'Andi Publisher & Wiley', 7, 10, 'Prinsip-prinsip dasar mekanika fluida dan aplikasinya.', '2025-05-30 15:49:40', '2025-05-30 15:49:40', NULL),
	(158, 'Termodinamika Teknik', 'Yunus A. Cengel (Terjemahan)', '779-880-991-002-6', 'Teknik Mesin', '2020', 'McGraw-Hill & Salemba Teknika', 5, 8, 'Konsep dasar termodinamika dan penerapannya dalam sistem rekayasa.', '2025-05-30 15:49:40', '2025-05-30 15:56:32', NULL),
	(159, 'Rangkaian Listrik Lanjutan', 'Charles K. Alexander (Terjemahan)', '880-991-002-113-7', 'Teknik Elektro', '2017', 'Penerbit Andi', 6, 9, 'Analisis rangkaian listrik AC dan DC yang lebih kompleks.', '2025-05-30 15:49:40', '2025-05-30 15:56:22', NULL),
	(160, 'Elektronika Daya: Konverter dan Aplikasi', 'Muhammad H. Rashid (Terjemahan)', '991-002-113-224-8', 'Teknik Elektro', '2021', 'Pearson & Erlangga', 4, 7, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:20', NULL),
	(161, 'Sistem Kontrol Otomatis', 'Katsuhiko Ogata (Terjemahan)', '003-114-225-336-9', 'Sistem Kontrol', '2019', 'Prentice Hall & Andi', 8, 11, 'Desain dan analisis sistem kontrol loop terbuka dan tertutup.', '2025-05-30 15:49:40', '2025-05-30 15:56:19', NULL),
	(162, 'Pengolahan Sinyal Digital', 'John G. Proakis (Terjemahan)', '114-225-336-447-0', 'Pengolahan Sinyal', '2018', 'Pearson & Salemba Teknika', 5, 7, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:17', NULL),
	(163, 'Antena dan Propagasi Gelombang', 'Constantine A. Balanis (Terjemahan)', '225-336-447-558-1', 'Telekomunikasi', '2022', 'Wiley & Andi Publisher', 6, 9, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:12', NULL),
	(164, 'Jaringan Nirkabel dan Komunikasi Bergerak', 'Theodore S. Rappaport (Terjemahan)', '336-447-558-669-2', 'Jaringan Nirkabel', '2020', 'Prentice Hall & Erlangga', 4, 7, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:11', NULL),
	(165, 'Teknik Pondasi Bangunan', 'Braja M. Das (Terjemahan)', '447-558-669-770-3', 'Teknik Sipil', '2017', 'Cengage Learning & Andi', 7, 10, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:10', NULL),
	(166, 'Struktur Beton Bertulang', 'Jack C. McCormac (Terjemahan)', '558-669-770-881-4', 'Teknik Sipil', '2021', 'Wiley & Erlangga', 5, 8, '', '2025-05-30 15:49:40', '2025-05-30 15:56:10', NULL),
	(167, 'Manajemen Konstruksi Proyek', 'Frederick E. Gould (Terjemahan)', '669-770-881-992-5', 'Manajemen Konstruksi', '2019', 'Pearson & Andi Publisher', 9, 12, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:09', NULL),
	(168, 'Ilmu Ukur Tanah untuk Teknik Sipil', 'Russell C. Brinker (Adaptasi)', '770-881-992-003-6', 'Geodesi', '2018', 'Penerbit Erlangga', 6, 9, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:09', NULL),
	(169, 'Hidrologi dan Pengelolaan Daerah Aliran Sungai', 'Ven Te Chow (Terjemahan)', '881-992-003-114-7', 'Sumber Daya Air', '2022', 'McGraw-Hill & UGM Press', 4, 7, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:08', NULL),
	(170, 'Teknik Lingkungan: Pengelolaan Limbah', 'Howard S. Peavy (Terjemahan)', '992-003-114-225-8', 'Teknik Lingkungan', '2020', 'John Wiley & Sons & ITB Press', 7, 10, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:08', NULL),
	(171, 'Energi Terbarukan: Sumber dan Teknologi', 'Godfrey Boyle (Terjemahan)', '004-115-226-337-9', 'Energi', '2023', 'Oxford University Press & Penerbit Andi', 10, 10, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:07', NULL),
	(172, 'Pemodelan dan Simulasi Sistem Dinamis', 'William J. Palm III (Terjemahan)', '115-226-337-448-0', 'Pemodelan Sistem', '2019', 'McGraw-Hill & Salemba Teknika', 5, 8, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:06', NULL),
	(173, 'Tata Kota dan Perencanaan Wilayah', 'Arthur B. Gallion (Adaptasi)', '226-337-448-559-1', 'Perencanaan Kota', '2017', 'Penerbit Erlangga', 6, 9, '', '2025-05-30 15:49:40', '2025-05-30 15:56:05', NULL),
	(174, 'Sistem Informasi Geografis (SIG)', 'Kang-tsung Chang', '337-448-559-660-2', 'SIG', '2021', 'McGraw-Hill Education & Informatika', 8, 11, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:04', NULL),
	(175, 'Kesehatan Masyarakat: Teori dan Aplikasi', 'Prof. Dr. Soekidjo Notoatmodjo', '448-559-660-771-3', 'Kesehatan Masyarakat', '2012', 'Rineka Cipta', 10, 13, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:03', NULL),
	(176, 'Epidemiologi Penyakit Menular', 'Leon Gordis', '559-660-771-882-4', 'Epidemiologi', '2014', 'Elsevier & EGC', 5, 7, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:01', NULL),
	(177, 'Gizi Kesehatan Masyarakat', 'Michael J. Gibney (Terjemahan)', '660-771-882-993-5', 'Gizi', '2018', 'Blackwell Publishing & EGC', 7, 10, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:01', NULL),
	(178, 'Manajemen Rumah Sakit Modern', 'Dr. dr. Adib Abdullah Yahya, MARS', '771-882-993-004-6', 'Manajemen Kesehatan', '2020', 'Sagung Seto', 6, 9, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:01', NULL),
	(179, 'Farmakologi dan Terapi', 'Tim Editor FKUI', '882-993-004-115-7', 'Farmakologi', '2016', 'Departemen Farmakologi dan Terapeutik FKUI', 9, 12, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:00', NULL),
	(180, 'Ilmu Penyakit Dalam', 'Prof. Dr. Aru W. Sudoyo, SpPD-KEMD', '993-004-115-226-8', 'Kedokteran', '2017', 'InternaPublishing', 4, 7, NULL, '2025-05-30 15:49:40', '2025-05-30 15:56:00', NULL),
	(181, 'Bedah Umum: Kumpulan Kuliah', 'Prof. Dr. R. Sjamsuhidajat, SpB-KBD', '005-116-227-338-9', 'Bedah', '2010', 'EGC', 8, 11, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:59', NULL),
	(182, 'Obstetri dan Ginekologi Panduan Praktik', 'Prof. Dr. Ida Bagus Gde Manuaba, SpOG(K)', '116-227-338-449-0', 'Obstetri Ginekologi', '2012', 'EGC', 5, 8, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:59', NULL),
	(183, 'Ilmu Kesehatan Anak Nelson', 'Waldo E. Nelson (Terjemahan)', '227-338-449-550-1', 'Pediatri', '2019', 'Elsevier & EGC', 7, 10, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:59', NULL),
	(184, 'Psikiatri Klinis Edisi Komprehensif', 'Benjamin J. Sadock (Terjemahan)', '338-449-550-661-2', 'Psikiatri', '2015', 'Binarupa Aksara Publisher', 6, 9, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:58', NULL),
	(185, 'Kedokteran Forensik dan Medikolegal', 'Prof. Dr. Abdul Mun\'im Idries, SpF', '449-550-661-772-3', 'Forensik', '2018', 'Sagung Seto', 4, 6, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:58', NULL),
	(186, 'Anatomi dan Fisiologi Manusia', 'Elaine N. Marieb (Terjemahan)', '550-661-772-883-4', 'Anatomi Fisiologi', '2016', 'Pearson Education & EGC', 10, 13, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:57', NULL),
	(187, 'Mikrobiologi Kedokteran Jawetz', 'Geo. F. Brooks (Terjemahan)', '661-772-883-994-5', 'Mikrobiologi', '2017', 'McGraw-Hill & EGC', 5, 8, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:57', NULL),
	(188, 'Parasitologi Kedokteran', 'Staf Pengajar Departemen Parasitologi FKUI', '772-883-994-005-6', 'Parasitologi', '2019', 'Balai Penerbit FKUI', 7, 10, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:56', NULL),
	(189, 'Patologi Klinik', 'Frances K. Widmann (Terjemahan)', '883-994-005-116-7', 'Patologi Klinik', '2014', 'EGC', 6, 9, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:56', NULL),
	(190, 'Radiologi Diagnostik', 'Charles E. Putman (Terjemahan)', '994-005-116-227-8', 'Radiologi', '2018', 'Elsevier & EGC', 4, 7, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:55', NULL),
	(191, 'Ilmu Gizi Olahraga', 'Asker Jeukendrup (Terjemahan)', '006-117-228-339-9', 'Gizi Olahraga', '2020', 'Human Kinetics & Rajawali Pers', 8, 11, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:55', NULL),
	(192, 'Fisioterapi pada Cedera Olahraga', 'Peter Brukner (Terjemahan)', '117-228-339-440-0', 'Fisioterapi', '2019', 'McGraw-Hill & EGC', 5, 8, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:54', NULL),
	(193, 'Kesehatan dan Keselamatan Kerja (K3)', 'Suma\'mur P.K.', '228-339-440-551-1', 'K3', '2013', 'Sagung Seto', 10, 13, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:33', NULL),
	(194, 'Hukum Bisnis di Era Digital', 'Prof. Dr. Nindyo Pramono, S.H., M.S.', '339-440-551-662-2', 'Hukum Bisnis', '2021', 'Andi Publisher', 7, 10, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:47', NULL),
	(195, 'Perbankan Syariah: Konsep dan Aplikasi', 'Adiwarman A. Karim', '440-551-662-773-3', 'Perbankan Syariah', '2016', 'Rajawali Pers', 6, 9, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:48', NULL),
	(196, 'Pasar Modal dan Investasi Syariah', 'Dr. Nurul Huda', '551-662-773-884-4', 'Pasar Modal Syariah', '2018', 'Kencana Prenada Media', 4, 7, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:53', NULL),
	(197, 'Asuransi Syariah: Prinsip dan Praktik', 'Muhammad Syafii Antonio', '662-773-884-995-5', 'Asuransi Syariah', '2017', 'Gema Insani Press', 8, 11, NULL, '2025-05-30 15:49:40', '2025-05-30 15:55:42', NULL),
	(198, 'Ekonomi Islam: Analisis Mikro dan Makro', 'Dr. Umer Chapra (Terjemahan)', '773-884-995-006-6', 'Ekonomi Islam', '2000', 'Gema Insani Press & IIIT', 5, 8, '', '2025-05-30 15:49:40', '2025-05-30 15:55:40', NULL),
	(199, 'Sejarah Peradaban Islam', 'Prof. Dr. Badri Yatim, M.A.', '884-995-006-117-7', 'Sejarah Islam', '2008', 'Rajawali Pers', 10, 13, '', '2025-05-30 15:49:40', '2025-05-30 15:55:19', NULL),
	(200, 'Studi Al-Quran Kontemporer', 'Prof. Dr. M. Quraish Shihab, M.A.', '995-006-117-228-8', 'Studi Quran', '2013', 'Lentera Hati', 7, 10, '', '2025-05-30 15:49:40', '2025-05-30 15:55:38', NULL);

-- Dumping structure for table library_db.loans
DROP TABLE IF EXISTS `loans`;
CREATE TABLE IF NOT EXISTS `loans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `book_id` int NOT NULL,
  `borrow_date` date NOT NULL,
  `due_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `fine_amount` decimal(10,2) DEFAULT '0.00',
  `status` enum('dipinjam','dikembalikan','terlambat') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'dipinjam',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_loans_user_id` (`user_id`),
  KEY `idx_loans_book_id` (`book_id`),
  KEY `idx_loans_status` (`status`),
  CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`book_id`) REFERENCES `books` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table library_db.loans: ~12 rows (approximately)
REPLACE INTO `loans` (`id`, `user_id`, `book_id`, `borrow_date`, `due_date`, `return_date`, `fine_amount`, `status`, `created_at`, `updated_at`) VALUES
	(1, 2, 30, '2025-05-30', '2025-06-13', '2025-05-30', 0.00, 'dikembalikan', '2025-05-30 15:24:54', '2025-05-30 15:25:19'),
	(2, 2, 30, '2025-05-30', '2025-06-13', '2025-05-30', 0.00, 'dikembalikan', '2025-05-30 15:24:56', '2025-05-30 15:25:20'),
	(3, 2, 30, '2025-05-30', '2025-06-13', '2025-05-30', 0.00, 'dikembalikan', '2025-05-30 15:24:58', '2025-05-30 15:25:20'),
	(4, 2, 30, '2025-05-30', '2025-06-13', '2025-05-30', 0.00, 'dikembalikan', '2025-05-30 15:25:00', '2025-05-30 15:25:21'),
	(5, 2, 30, '2025-05-30', '2025-06-13', '2025-05-30', 0.00, 'dikembalikan', '2025-05-30 15:25:02', '2025-05-30 15:25:21'),
	(6, 2, 8, '2025-05-30', '2025-06-13', NULL, 0.00, 'dipinjam', '2025-05-30 15:25:30', '2025-05-30 15:25:30'),
	(7, 2, 10, '2025-05-30', '2025-06-13', NULL, 0.00, 'dipinjam', '2025-05-30 15:25:36', '2025-05-30 15:25:36'),
	(8, 2, 11, '2025-05-30', '2025-06-13', NULL, 0.00, 'dipinjam', '2025-05-30 15:25:40', '2025-05-30 15:25:40'),
	(9, 2, 2, '2025-05-30', '2025-06-13', NULL, 0.00, 'dipinjam', '2025-05-30 15:25:46', '2025-05-30 15:25:46'),
	(10, 2, 5, '2025-05-30', '2025-06-13', NULL, 0.00, 'dipinjam', '2025-05-30 15:25:58', '2025-05-30 15:25:58'),
	(11, 2, 39, '2025-05-30', '2025-06-13', NULL, 0.00, 'dipinjam', '2025-05-30 15:26:11', '2025-05-30 15:26:11'),
	(12, 2, 2, '2025-05-30', '2025-06-13', NULL, 0.00, 'dipinjam', '2025-05-30 15:26:25', '2025-05-30 15:26:25'),
	(13, 2, 21, '2025-05-30', '2025-06-13', NULL, 0.00, 'dipinjam', '2025-05-30 15:26:32', '2025-05-30 15:26:32');

-- Dumping structure for table library_db.users
DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `full_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('member','admin') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'member',
  `phone` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table library_db.users: ~3 rows (approximately)
REPLACE INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `phone`, `address`, `created_at`, `updated_at`) VALUES
	(1, 'admin', '$2y$10$v581cWt.cop2HQ5Ekz/92etG.YMpSDv1Ezr.c1gb81BlCwhjjXMDe', 'Administrator', 'admin', '123456789', 'Library Office', '2025-03-10 12:07:31', '2025-05-20 13:54:28'),
	(2, 'arista', '$2y$10$v581cWt.cop2HQ5Ekz/92etG.YMpSDv1Ezr.c1gb81BlCwhjjXMDe', 'arista wiguna', 'member', '085693250574', 'JL. Merdekaa', '2025-05-20 12:47:48', '2025-05-20 12:47:48');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;

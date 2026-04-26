<?php
session_start();
include 'includes/koneksi.php';

// Ambil artikel terbaru
$artikel = mysqli_query($koneksi, "SELECT * FROM artikel ORDER BY tanggal_publish DESC LIMIT 3");

// Ambil harga pasar
$harga = mysqli_query($koneksi, "SELECT * FROM harga_pasar ORDER BY tanggal DESC LIMIT 6");

// Hitung statistik
$total_artikel = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM artikel"));
$total_harga   = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM harga_pasar"));
$total_user    = mysqli_num_rows(mysqli_query($koneksi, "SELECT id FROM users"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fresh Smart Farm</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/landing.css">
</head>
<body>

<!-- NAVBAR -->
<nav>
    <a class="nav-brand" href="index.php">
        <img src="assets/images/logo.png" alt="Logo" onerror="this.style.display='none'">
        <div class="brand-text">
            <span class="brand-name">Fresh <em>Smart Farm</em></span>
            <span class="brand-tagline">Platform Pertanian Digital</span>
        </div>
    </a>
    <div class="nav-center">
        <a href="#statistik">Tentang</a>
        <a href="#fitur">Fitur</a>
        <a href="#artikel">Artikel</a>
        <a href="#harga">Harga Pasar</a>
    </div>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a class="nav-cta" href="pages/dashboard.php">Dashboard →</a>
    <?php else: ?>
        <a class="nav-cta" href="login.php">Masuk</a>
    <?php endif; ?>
</nav>

<!-- ===== HERO — background_logo.jpg ===== -->
<section class="hero">
    <div class="hero-overlay"></div>

    <!-- Logo kecil mengambang di atas foto background -->
    <div class="hero-logo-float">
        <img src="assets/images/logo.png" alt="Fresh Smart Farm" onerror="this.style.display='none'">
        <span>Fresh Smart Farm</span>
    </div>

    <div class="hero-content">
        <div class="hero-badge">🌱 Platform Pertanian #1 Indonesia</div>
        <h1>Pertanian Cerdas<br><em>Dimulai dari Sini</em></h1>
        <p>Platform digital terpadu untuk petani Indonesia yang ingin mengelola lahan dengan lebih efisien, terstruktur, dan menguntungkan.</p>
        <div class="hero-actions">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="pages/dashboard.php" class="btn-primary">Buka Dashboard →</a>
            <?php else: ?>
                <a href="login.php" class="btn-primary">Mulai Gratis →</a>
            <?php endif; ?>
            <a href="#fitur" class="btn-ghost">Lihat Fitur</a>
        </div>
    </div>

    <div class="hero-scroll-hint">
        <span>Scroll ke bawah</span>
        <div class="scroll-arrow"></div>
    </div>
</section>

<!-- ===== SECTION 1: TENTANG + STATISTIK — background_landing_1.png ===== -->
<section class="section about-section" id="statistik">
    <div class="section-inner">

        <div class="about-grid">
            <div class="about-visual">
                <div class="about-img-wrap">
                    <img src="assets/images/logo.png" alt="Fresh Smart Farm Logo" onerror="this.src=''; this.alt='🌱'">
                </div>
                <p class="about-caption">Inovasi untuk Petani Indonesia</p>
            </div>
            <div class="about-body">
                <span class="section-label">Mengapa Kami</span>
                <h2>Solusi Digital untuk Pertanian yang Lebih Baik</h2>
                <p>Pertanian Indonesia memerlukan solusi digital yang tepat untuk meningkatkan produktivitas. <strong>Fresh Smart Farm</strong> hadir sebagai partner terpercaya bagi petani modern.</p>
                <p>Dari pencatatan jurnal harian hingga analisis harga pasar — semua dalam satu dashboard yang mudah digunakan.</p>
                <ul class="feature-list">
                    <li>Jurnal tanam digital dengan manajemen lengkap</li>
                    <li>Grafik & statistik panen interaktif</li>
                    <li>Monitor harga komoditas terkini setiap hari</li>
                    <li>Artikel edukasi dari para ahli pertanian</li>
                    <li>Sistem keamanan login terenkripsi</li>
                    <li>Akses optimal dari semua perangkat</li>
                </ul>
            </div>
        </div>

        <div class="stats-block">
            <div class="stats-header">
                <span class="section-label">Pencapaian</span>
                <h2>Platform yang Dipercaya Petani</h2>
            </div>
            <div class="stats-grid">
                <div class="stat-card scroll-reveal">
                    <div class="stat-icon">👨‍🌾</div>
                    <h3><?= $total_user ?>+</h3>
                    <p>Petani Aktif</p>
                </div>
                <div class="stat-card scroll-reveal">
                    <div class="stat-icon">📰</div>
                    <h3><?= $total_artikel ?>+</h3>
                    <p>Artikel Edukatif</p>
                </div>
                <div class="stat-card scroll-reveal">
                    <div class="stat-icon">📊</div>
                    <h3><?= $total_harga ?>+</h3>
                    <p>Data Harga Pasar</p>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- ===== SECTION 2: FITUR UNGGULAN — background_landing_2.png ===== -->
<section class="section fitur-section" id="fitur">
    <div class="section-inner">
        <div class="section-header center">
            <span class="section-label-light">Fitur Unggulan</span>
            <h2 class="heading-light">Semua yang Kamu Butuhkan</h2>
            <p class="section-desc-light">Dirancang khusus untuk memenuhi kebutuhan petani Indonesia modern.</p>
        </div>
        <div class="fitur-grid">
            <div class="fitur-card scroll-reveal">
                <div class="fitur-icon">📋</div>
                <h3>Jurnal Tanam Digital</h3>
                <p>Catat setiap aktivitas tanam, perawatan, dan hasil panen dengan sistem terstruktur dan mudah diakses kapan saja.</p>
            </div>
            <div class="fitur-card scroll-reveal">
                <div class="fitur-icon">📊</div>
                <h3>Grafik & Statistik</h3>
                <p>Visualisasi data panen dalam bentuk grafik interaktif untuk analisis dan perencanaan pertanian lebih baik.</p>
            </div>
            <div class="fitur-card scroll-reveal">
                <div class="fitur-icon">💰</div>
                <h3>Harga Pasar Terkini</h3>
                <p>Pantau harga komoditas pertanian langsung dari dashboard dan buat keputusan jual yang lebih strategis.</p>
            </div>
            <div class="fitur-card scroll-reveal">
                <div class="fitur-icon">📰</div>
                <h3>Artikel & Tips Pertanian</h3>
                <p>Baca tips, trik, dan informasi terpercaya dari para ahli untuk meningkatkan hasil panen kamu.</p>
            </div>
            <div class="fitur-card scroll-reveal">
                <div class="fitur-icon">🔐</div>
                <h3>Keamanan Terjamin</h3>
                <p>Login aman dengan enkripsi modern menjamin data pertanian kamu tetap pribadi dan terlindungi.</p>
            </div>
            <div class="fitur-card scroll-reveal">
                <div class="fitur-icon">📱</div>
                <h3>Responsif & Cepat</h3>
                <p>Akses dari desktop, tablet, atau smartphone dengan performa optimal dan antarmuka yang nyaman.</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== SECTION 3: ARTIKEL + HARGA PASAR — background_landing_3.png ===== -->
<section class="section konten-section" id="artikel">
    <div class="section-inner">

        <!-- ARTIKEL TERBARU -->
        <div class="konten-block">
            <div class="section-header">
                <span class="section-label">Konten Terbaru</span>
                <h2>Artikel & Tips Pertanian</h2>
            </div>
            <?php if (mysqli_num_rows($artikel) == 0): ?>
                <div class="empty-state">
                    <span>📚</span>
                    <p>Belum ada artikel tersedia. Segera kembali untuk membaca artikel edukatif terbaru!</p>
                </div>
            <?php else: ?>
                <div class="artikel-grid">
                    <?php while ($row = mysqli_fetch_assoc($artikel)): ?>
                        <div class="artikel-card scroll-reveal">
                            <div class="artikel-img-wrap">
                                <img src="<?= !empty($row['gambar']) ? $row['gambar'] : 'assets/images/artikel_default.png' ?>" alt="Thumbnail Artikel">
                            </div>
                            <div class="artikel-body">
                                <span class="artikel-date">📅 <?= date('d M Y', strtotime($row['tanggal_publish'])) ?></span>
                                <h3><?= htmlspecialchars($row['judul']) ?></h3>
                                <p><?= substr(htmlspecialchars($row['isi']), 0, 130) ?>…</p>
                                <a href="pages/detail_artikel.php?id=<?= $row['id'] ?>" class="artikel-link">Baca Selengkapnya →</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- HARGA PASAR -->
        <div class="konten-block" id="harga">
            <div class="section-header">
                <span class="section-label">Update Harian</span>
                <h2>Harga Pasar Komoditas</h2>
            </div>
            <?php if (mysqli_num_rows($harga) == 0): ?>
                <div class="empty-state">
                    <span>💹</span>
                    <p>Data harga pasar belum tersedia. Pantau terus untuk update harga terbaru!</p>
                </div>
            <?php else: ?>
                <div class="table-wrap scroll-reveal">
                    <table class="harga-table">
                        <thead>
                            <tr>
                                <th>Komoditas</th>
                                <th>Harga</th>
                                <th>Satuan</th>
                                <th>Tanggal Update</th>
                                <th>Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($harga)): ?>
                                <tr>
                                    <td class="td-name"><?= htmlspecialchars($row['nama_komoditas']) ?></td>
                                    <td class="td-price">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                    <td><?= htmlspecialchars($row['satuan']) ?></td>
                                    <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                                    <td><a href="pages/detail_harga.php?id=<?= $row['id'] ?>">Lihat →</a></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

<!-- FOOTER -->
<footer>
    <div class="footer-inner">
        <div class="footer-brand">
            <img src="assets/images/logo.png" alt="Logo" onerror="this.style.display='none'">
            <span>Fresh Smart Farm</span>
        </div>
        <p>&copy; 2025 Fresh Smart Farm — Platform Pertanian Cerdas Indonesia</p>
        <p class="footer-credit">Dibuat dengan hati-hati oleh siswa SMK Telkom Purwokerto</p>
    </div>
</footer>

<script>
// ===== SCROLL REVEAL — muncul satu-satu saat masuk viewport =====
(function () {
    var cards = document.querySelectorAll('.scroll-reveal');

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                // Hitung urutan di antara saudara .scroll-reveal dalam container yang sama
                var siblings = Array.from(entry.target.parentElement.querySelectorAll('.scroll-reveal'));
                var idx = siblings.indexOf(entry.target);
                entry.target.style.transitionDelay = (idx * 130) + 'ms';
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.10,
        rootMargin: '0px 0px -50px 0px'
    });

    cards.forEach(function (card) { observer.observe(card); });
})();

// ===== NAVBAR SHADOW =====
window.addEventListener('scroll', function () {
    document.querySelector('nav').classList.toggle('scrolled', window.scrollY > 20);
});
</script>

</body>
</html>
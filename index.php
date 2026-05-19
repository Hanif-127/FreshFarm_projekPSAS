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

function landing_artikel_gambar($gambar) {
    if (empty($gambar)) {
        return 'assets/images/artikel_default.png';
    }

    return 'assets/images/' . htmlspecialchars(basename($gambar), ENT_QUOTES, 'UTF-8');
}

function landing_ringkas_teks($teks, $batas = 130) {
    $bersih = trim(preg_replace('/\s+/', ' ', strip_tags($teks)));

    if (function_exists('mb_strlen') && mb_strlen($bersih) > $batas) {
        return mb_substr($bersih, 0, $batas) . '...';
    }

    if (strlen($bersih) > $batas) {
        return substr($bersih, 0, $batas) . '...';
    }

    return $bersih;
}

function landing_ui_icon(string $name): string
{
    $icons = [
        'petani' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="4"/><path d="M5 8h14"/></svg>',
        'artikel' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 4h12a2 2 0 0 1 2 2v12H8a2 2 0 0 0-2 2"/><path d="M6 4v16"/><path d="M10 8h6"/><path d="M10 12h6"/></svg>',
        'harga' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="8"/><path d="M12 8v8"/><path d="M9.5 10.5c0-1 1-1.8 2.5-1.8 1.3 0 2.2.6 2.2 1.6 0 2.5-4.7 1.2-4.7 3.8 0 1 1 1.8 2.6 1.8 1.3 0 2.4-.6 2.4-1.6"/></svg>',
        'jurnal' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="4" width="12" height="16" rx="2"/><path d="M9 4v16"/><path d="M11 9h4"/><path d="M11 13h4"/></svg>',
        'grafik' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h16"/><path d="M7 20V11"/><path d="M12 20V7"/><path d="M17 20v-5"/></svg>',
        'keamanan' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v6c0 4.4-2.9 7.8-7 9-4.1-1.2-7-4.6-7-9V6z"/><path d="m9.5 12.5 2 2 3-4"/></svg>',
        'responsif' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="13" height="10" rx="2"/><path d="M8 19h3"/><rect x="18" y="8" width="3" height="8" rx="1.2"/></svg>',
    ];

    return $icons[$name] ?? $icons['grafik'];
}
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
<body class="landing-page">

<?php include 'includes/header.php'; ?>

<main class="landing-main">

<!-- ===== HERO — background_logo.jpg ===== -->
<section class="hero">
    <div class="hero-overlay"></div>

    <!-- Logo kecil mengambang di atas foto background -->
    <div class="hero-logo-float">
        <img src="assets/images/logo.png" alt="Fresh Smart Farm" onerror="this.style.display='none'">
        <span>Fresh Smart Farm</span>
    </div>

    <div class="hero-content">
        <div class="hero-badge">Platform Pertanian #1 Indonesia</div>
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
                    <img src="assets/images/logo.png" alt="Fresh Smart Farm Logo" onerror="this.src=''; this.alt='Fresh Smart Farm'">
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
                    <div class="stat-icon" aria-hidden="true"><?= landing_ui_icon('petani') ?></div>
                    <h3><?= $total_user ?>+</h3>
                    <p>Petani Aktif</p>
                </div>
                <div class="stat-card scroll-reveal">
                    <div class="stat-icon" aria-hidden="true"><?= landing_ui_icon('artikel') ?></div>
                    <h3><?= $total_artikel ?>+</h3>
                    <p>Artikel Edukatif</p>
                </div>
                <div class="stat-card scroll-reveal">
                    <div class="stat-icon" aria-hidden="true"><?= landing_ui_icon('harga') ?></div>
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
                <div class="fitur-icon" aria-hidden="true"><?= landing_ui_icon('jurnal') ?></div>
                <h3>Jurnal Tanam Digital</h3>
                <p>Catat setiap aktivitas tanam, perawatan, dan hasil panen dengan sistem terstruktur dan mudah diakses kapan saja.</p>
            </div>
            <div class="fitur-card scroll-reveal">
                <div class="fitur-icon" aria-hidden="true"><?= landing_ui_icon('grafik') ?></div>
                <h3>Grafik & Statistik</h3>
                <p>Visualisasi data panen dalam bentuk grafik interaktif untuk analisis dan perencanaan pertanian lebih baik.</p>
            </div>
            <div class="fitur-card scroll-reveal">
                <div class="fitur-icon" aria-hidden="true"><?= landing_ui_icon('harga') ?></div>
                <h3>Harga Pasar Terkini</h3>
                <p>Pantau harga komoditas pertanian langsung dari dashboard dan buat keputusan jual yang lebih strategis.</p>
            </div>
            <div class="fitur-card scroll-reveal">
                <div class="fitur-icon" aria-hidden="true"><?= landing_ui_icon('artikel') ?></div>
                <h3>Artikel & Tips Pertanian</h3>
                <p>Baca tips, trik, dan informasi terpercaya dari para ahli untuk meningkatkan hasil panen kamu.</p>
            </div>
            <div class="fitur-card scroll-reveal">
                <div class="fitur-icon" aria-hidden="true"><?= landing_ui_icon('keamanan') ?></div>
                <h3>Keamanan Terjamin</h3>
                <p>Login aman dengan enkripsi modern menjamin data pertanian kamu tetap pribadi dan terlindungi.</p>
            </div>
            <div class="fitur-card scroll-reveal">
                <div class="fitur-icon" aria-hidden="true"><?= landing_ui_icon('responsif') ?></div>
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
                                <img src="<?= landing_artikel_gambar($row['gambar']) ?>" alt="Thumbnail Artikel">
                            </div>
                            <div class="artikel-body">
                                <span class="artikel-date">📅 <?= date('d M Y', strtotime($row['tanggal_publish'])) ?></span>
                                <h3><?= htmlspecialchars($row['judul']) ?></h3>
                                <p><?= htmlspecialchars(landing_ringkas_teks($row['isi'])) ?></p>
                                <a href="pages/detail_artikel.php?id=<?= $row['id'] ?>" class="artikel-link">Baca Selengkapnya →</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                <div class="konten-more">
                    <a href="pages/artikel.php" class="btn-more">Lihat lainnya</a>
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
                <div class="konten-more">
                    <a href="pages/harga_pasar.php" class="btn-more">Lihat lainnya</a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</section>

</main>

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

// ===== TRANSISI HALUS UNTUK LINK "LIHAT LAINNYA" =====
(function () {
    var links = document.querySelectorAll('.btn-more[href]');

    links.forEach(function (link) {
        link.addEventListener('click', function (event) {
            var href = link.getAttribute('href');

            if (!href || href.charAt(0) === '#') return;

            event.preventDefault();
            document.body.classList.add('is-leaving');
            window.dispatchEvent(new CustomEvent('landing:leave'));

            window.setTimeout(function () {
                window.location.href = href;
            }, 180);
        });
    });
})();

</script>

</body>
</html>

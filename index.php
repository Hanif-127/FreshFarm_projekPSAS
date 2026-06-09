<?php
session_start();
include 'includes/koneksi.php';

function landing_count(mysqli $koneksi, string $table): int
{
    $allowed = ['artikel', 'harga_pasar', 'users'];

    if (!in_array($table, $allowed, true)) {
        return 0;
    }

    $result = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM {$table}");
    $row = $result ? mysqli_fetch_assoc($result) : null;

    return (int) ($row['total'] ?? 0);
}

function landing_artikel_gambar($gambar): string
{
    $nama_file = basename(trim((string) $gambar));

    if ($nama_file !== '') {
        $path_artikel = __DIR__ . '/assets/images_artikel/' . $nama_file;
        $path_umum = __DIR__ . '/assets/images/' . $nama_file;

        if (is_file($path_artikel)) {
            return 'assets/images_artikel/' . htmlspecialchars($nama_file, ENT_QUOTES, 'UTF-8');
        }

        if (is_file($path_umum)) {
            return 'assets/images/' . htmlspecialchars($nama_file, ENT_QUOTES, 'UTF-8');
        }
    }

    return 'assets/images/artikel_default.png';
}

function landing_ringkas_teks($teks, $batas = 118): string
{
    $bersih = trim(preg_replace('/\s+/', ' ', strip_tags((string) $teks)));

    if (function_exists('mb_strlen') && mb_strlen($bersih) > $batas) {
        return mb_substr($bersih, 0, $batas) . '...';
    }

    if (strlen($bersih) > $batas) {
        return substr($bersih, 0, $batas) . '...';
    }

    return $bersih;
}

function landing_tanggal($tanggal, $format = 'd M Y'): string
{
    $waktu = strtotime((string) $tanggal);

    if (!$waktu) {
        return '-';
    }

    return date($format, $waktu);
}

function landing_ui_icon(string $name): string
{
    $icons = [
        'petani' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="4"/><path d="M5 8h14"/></svg>',
        'artikel' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 4h12a2 2 0 0 1 2 2v12H8a2 2 0 0 0-2 2"/><path d="M6 4v16"/><path d="M10 8h6"/><path d="M10 12h6"/></svg>',
        'harga' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="8"/><path d="M12 8v8"/><path d="M9.5 10.5c0-1 1-1.8 2.5-1.8 1.3 0 2.2.6 2.2 1.6 0 2.5-4.7 1.2-4.7 3.8 0 1 1 1.8 2.6 1.8 1.3 0 2.4-.6 2.4-1.6"/></svg>',
        'jurnal' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="6" y="4" width="12" height="16" rx="2"/><path d="M9 4v16"/><path d="M11 9h4"/><path d="M11 13h4"/></svg>',
        'grafik' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 20h16"/><path d="M7 20V11"/><path d="M12 20V7"/><path d="M17 20v-5"/></svg>',
        'kalender' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4"/><path d="M16 3v4"/><path d="M4 10h16"/></svg>',
        'stok' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 8l8-4 8 4-8 4z"/><path d="M4 8v8l8 4 8-4V8"/><path d="M12 12v8"/></svg>',
        'aman' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.85" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v6c0 4.4-2.9 7.8-7 9-4.1-1.2-7-4.6-7-9V6z"/><path d="m9.5 12.5 2 2 3-4"/></svg>',
    ];

    return $icons[$name] ?? $icons['grafik'];
}

$artikel_result = mysqli_query($koneksi, "SELECT * FROM artikel ORDER BY tanggal_publish DESC, id DESC LIMIT 8");
$artikel_items = [];

if ($artikel_result) {
    while ($row = mysqli_fetch_assoc($artikel_result)) {
        $artikel_items[] = [
            'id' => (int) $row['id'],
            'judul' => $row['judul'],
            'isi' => $row['isi'],
            'gambar' => $row['gambar'],
            'tanggal_publish' => $row['tanggal_publish'],
            'tag' => 'Artikel',
        ];
    }
}

$harga_result = mysqli_query($koneksi, "SELECT * FROM harga_pasar ORDER BY tanggal DESC, nama_komoditas ASC LIMIT 6");
$harga_items = [];

if ($harga_result) {
    while ($row = mysqli_fetch_assoc($harga_result)) {
        $harga_items[] = $row;
    }
}

$total_artikel = landing_count($koneksi, 'artikel');
$total_harga = landing_count($koneksi, 'harga_pasar');
$total_user = landing_count($koneksi, 'users');
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
    <link rel="stylesheet" href="assets/css/index.css?v=<?= filemtime(__DIR__ . '/assets/css/index.css') ?>">
</head>
<body class="landing-page">

<?php include 'includes/header.php'; ?>

<main class="landing-main">
    <section class="hero" id="beranda" aria-labelledby="hero-title">
        <div class="hero-backgrounds" aria-hidden="true">
            <span class="hero-background-slide hero-background-slide--1"></span>
            <span class="hero-background-slide hero-background-slide--2"></span>
            <span class="hero-background-slide hero-background-slide--3"></span>
            <span class="hero-background-slide hero-background-slide--4"></span>
            <span class="hero-background-slide hero-background-slide--5"></span>
        </div>
        <div class="hero-inner">
            <div class="hero-logo-mark" aria-label="Fresh Smart Farm">
                <img src="assets/images/logo.png" alt="Fresh Smart Farm" onerror="this.style.display='none'">
                <span>Fresh Smart Farm</span>
            </div>
            <h1 id="hero-title">Rawat Kebun dengan Tenang, Tumbuhkan Hasil Terbaik</h1>
            <p>Fresh Smart Farm membantu Anda memahami kondisi kebun, merawat tanaman tepat waktu, dan mengambil keputusan dengan lebih yakin agar setiap usaha di lahan tumbuh menjadi hasil yang membanggakan.</p>

            <div class="hero-actions" aria-label="Aksi utama">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="pages/dashboard.php" class="btn btn-primary">Buka Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary">Mulai Sekarang</a>
                <?php endif; ?>
                <a href="#artikel" class="btn btn-secondary">Lihat Insight</a>
            </div>

            <div class="hero-metrics" aria-label="Ringkasan Fresh Smart Farm">
                <div>
                    <strong><?= $total_artikel ?>+</strong>
                    <span>Insight pertanian</span>
                </div>
                <div>
                    <strong><?= $total_harga ?>+</strong>
                    <span>Data harga pasar</span>
                </div>
                <div>
                    <strong><?= $total_user ?>+</strong>
                    <span>Pengguna terdaftar</span>
                </div>
            </div>
        </div>
        <a class="hero-scroll" href="#statistik" aria-label="Lanjut ke bagian statistik">
            <span></span>
        </a>
    </section>

    <div class="landing-bg-group landing-bg-group--farm">
        <section class="section about-section" id="statistik">
            <div class="section-shell about-layout">
                <div class="section-copy">
                    <span class="section-kicker">Platform kebun digital</span>
                    <h2>Semua keputusan kebun lebih mudah saat datanya terlihat.</h2>
                    <p>Fresh Smart Farm merapikan aktivitas harian, stok, jadwal, harga pasar, dan insight pertanian agar petani bisa bergerak lebih cepat tanpa kehilangan konteks.</p>

                    <div class="stats-grid" aria-label="Statistik platform">
                        <div class="stat-card scroll-reveal">
                            <div class="icon-box" aria-hidden="true"><img class="stat-icon-petani" src="assets/icons/petani.png" alt=""></div>
                            <strong><?= $total_user ?>+</strong>
                            <span>Petani aktif</span>
                        </div>
                        <div class="stat-card scroll-reveal">
                            <div class="icon-box" aria-hidden="true"><img src="assets/icons/insight.png" alt=""></div>
                            <strong><?= $total_artikel ?>+</strong>
                            <span>Insight tersedia</span>
                        </div>
                        <div class="stat-card scroll-reveal">
                            <div class="icon-box" aria-hidden="true"><img src="assets/icons/update_harga.png" alt=""></div>
                            <strong><?= $total_harga ?>+</strong>
                            <span>Update harga</span>
                        </div>
                    </div>
                </div>

                <div class="workflow-panel scroll-reveal" aria-label="Alur kerja Fresh Smart Farm">
                    <span class="panel-label">Alur kerja</span>
                    <h3>Dari lahan ke keputusan jual</h3>
                    <div class="workflow-list">
                        <div>
                            <strong>01</strong>
                            <span>Catat tanam, perawatan, dan panen harian.</span>
                        </div>
                        <div>
                            <strong>02</strong>
                            <span>Pantau jadwal, stok, grafik, dan kondisi kebun.</span>
                        </div>
                        <div>
                            <strong>03</strong>
                            <span>Bandingkan harga pasar sebelum menjual hasil panen.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section fitur-section" id="fitur">
            <div class="section-shell">
                <div class="section-top">
                    <div>
                        <span class="section-kicker">Fitur utama</span>
                        <h2>Tool yang dipakai berulang, bukan cuma dilihat sekali.</h2>
                    </div>
                    <p>Setiap modul dibuat ringkas agar nyaman untuk penggunaan harian di kebun, gudang, maupun saat mengecek harga dari ponsel.</p>
                </div>

                <div class="feature-grid">
                    <article class="feature-card scroll-reveal">
                        <div class="icon-box" aria-hidden="true"><?= landing_ui_icon('jurnal') ?></div>
                        <h3>Jurnal Tanam</h3>
                        <p>Rekam tanggal tanam, jumlah, status, dan hasil panen dengan alur yang mudah ditelusuri kembali.</p>
                    </article>
                    <article class="feature-card scroll-reveal">
                        <div class="icon-box" aria-hidden="true"><?= landing_ui_icon('kalender') ?></div>
                        <h3>Kalender Kebun</h3>
                        <p>Susun agenda siram, pupuk, tanam, dan panen supaya kegiatan penting tidak tercecer.</p>
                    </article>
                    <article class="feature-card scroll-reveal">
                        <div class="icon-box" aria-hidden="true"><?= landing_ui_icon('grafik') ?></div>
                        <h3>Grafik Produksi</h3>
                        <p>Baca pola tanam dan panen lewat visual yang membantu evaluasi tiap komoditas.</p>
                    </article>
                    <article class="feature-card scroll-reveal">
                        <div class="icon-box" aria-hidden="true"><?= landing_ui_icon('stok') ?></div>
                        <h3>Inventaris</h3>
                        <p>Pantau stok pupuk, benih, alat, dan kebutuhan minimum agar operasional kebun tetap siap.</p>
                    </article>
                    <article class="feature-card scroll-reveal">
                        <div class="icon-box" aria-hidden="true"><?= landing_ui_icon('harga') ?></div>
                        <h3>Harga Pasar</h3>
                        <p>Lihat harga komoditas terbaru untuk membantu menentukan waktu dan tujuan penjualan.</p>
                    </article>
                    <article class="feature-card scroll-reveal">
                        <div class="icon-box" aria-hidden="true"><?= landing_ui_icon('aman') ?></div>
                        <h3>Akses Aman</h3>
                        <p>Data pengguna dan aktivitas kebun disimpan melalui sistem login yang lebih tertata.</p>
                    </article>
                </div>
            </div>
        </section>
    </div>

    <div class="landing-bg-group landing-bg-group--content">
        <section class="section insight-section" id="artikel">
            <div class="section-shell">
                <div class="section-top">
                    <div>
                        <span class="section-kicker">Insight pertanian</span>
                        <h2>Artikel dan tips yang lebih padat, lebih banyak, dan lebih enak dipindai.</h2>
                    </div>
                    <a href="pages/artikel.php" class="btn btn-compact js-page-transition">Lihat Semua Artikel</a>
                </div>

                <?php if (count($artikel_items) === 0): ?>
                    <div class="empty-state">
                        <strong>Belum ada artikel tersedia.</strong>
                        <p>Insight baru akan muncul di sini setelah data artikel ditambahkan.</p>
                    </div>
                <?php else: ?>
                    <div class="insight-grid">
                        <?php foreach ($artikel_items as $index => $row): ?>
                            <?php
                                $artikel_href = 'pages/detail_artikel.php?id=' . (int) $row['id'];
                            ?>
                            <article class="insight-card scroll-reveal">
                                <a class="insight-image" href="<?= $artikel_href ?>">
                                    <img src="<?= landing_artikel_gambar($row['gambar']) ?>" alt="<?= htmlspecialchars($row['judul']) ?>">
                                </a>
                                <div class="insight-body">
                                    <div class="insight-meta">
                                        <span><?= htmlspecialchars($row['tag'] ?? 'Tips') ?></span>
                                        <time datetime="<?= htmlspecialchars($row['tanggal_publish']) ?>"><?= landing_tanggal($row['tanggal_publish']) ?></time>
                                    </div>
                                    <h3><?= htmlspecialchars($row['judul']) ?></h3>
                                    <p><?= htmlspecialchars(landing_ringkas_teks($row['isi'])) ?></p>
                                    <a href="<?= $artikel_href ?>" class="text-link">Baca selengkapnya</a>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="section market-section" id="harga">
            <div class="section-shell market-layout">
                <div class="market-copy">
                    <span class="section-kicker">Harga pasar</span>
                    <h2>Bandingkan harga komoditas sebelum panen bergerak ke pasar.</h2>
                    <p>Preview harga dibuat lebih ringkas agar mudah dibaca. Halaman harga pasar tetap menjadi tempat untuk melihat semua data dan detail tiap komoditas.</p>
                    <a href="pages/harga_pasar.php" class="btn btn-compact js-page-transition">Lihat Semua Harga</a>
                </div>

                <div class="market-panel scroll-reveal">
                    <?php if (count($harga_items) === 0): ?>
                        <div class="empty-state">
                            <strong>Belum ada data harga.</strong>
                            <p>Data harga komoditas akan muncul setelah ditambahkan ke sistem.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table class="harga-table">
                                <thead>
                                    <tr>
                                        <th>Komoditas</th>
                                        <th>Harga</th>
                                        <th>Satuan</th>
                                        <th>Update</th>
                                        <th>Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($harga_items as $row): ?>
                                        <tr>
                                            <td class="td-name"><?= htmlspecialchars($row['nama_komoditas']) ?></td>
                                            <td class="td-price">Rp <?= number_format((float) $row['harga'], 0, ',', '.') ?></td>
                                            <td><?= htmlspecialchars($row['satuan']) ?></td>
                                            <td><?= landing_tanggal($row['tanggal']) ?></td>
                                            <td><a href="pages/detail_harga.php?id=<?= (int) $row['id'] ?>">Lihat</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>
</main>

<footer>
    <div class="footer-inner">
        <a class="footer-brand" href="#beranda" aria-label="Kembali ke atas">
            <img src="assets/images/logo.png" alt="Fresh Smart Farm" onerror="this.style.display='none'">
            <span>Fresh Smart Farm</span>
        </a>
        <p>&copy; 2026 Fresh Smart Farm. Platform pertanian cerdas untuk petani Indonesia.</p>
    </div>
</footer>

<script>
(function () {
    var cards = document.querySelectorAll('.scroll-reveal');

    if (!('IntersectionObserver' in window)) {
        cards.forEach(function (card) { card.classList.add('revealed'); });
        return;
    }

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                var siblings = Array.from(entry.target.parentElement.querySelectorAll('.scroll-reveal'));
                var idx = siblings.indexOf(entry.target);
                entry.target.style.transitionDelay = (idx * 70) + 'ms';
                entry.target.classList.add('revealed');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -40px 0px'
    });

    cards.forEach(function (card) { observer.observe(card); });
})();

(function () {
    var links = document.querySelectorAll('.js-page-transition[href]');

    links.forEach(function (link) {
        link.addEventListener('click', function (event) {
            var href = link.getAttribute('href');

            if (!href || href.charAt(0) === '#') return;

            event.preventDefault();
            document.body.classList.add('is-leaving');
            window.dispatchEvent(new CustomEvent('landing:leave'));

            window.setTimeout(function () {
                window.location.href = href;
            }, 160);
        });
    });
})();
</script>

</body>
</html>

USE db_fresh_farm;

UPDATE artikel SET
    judul = 'Strategi Rotasi Tanam untuk Menjaga Kesuburan Tanah',
    isi = 'Rotasi tanam adalah cara sederhana untuk menjaga tanah tetap produktif dari musim ke musim. Jangan menanam keluarga tanaman yang sama terus-menerus di bedengan yang sama, karena unsur hara tertentu akan cepat terkuras dan risiko penyakit tanah meningkat.\n\nMulailah dengan membagi lahan menjadi beberapa petak. Setelah menanam cabai atau tomat, pindahkan ke tanaman daun seperti sawi, bayam, atau kangkung. Musim berikutnya bisa diselingi kacang panjang atau legum untuk membantu memperbaiki nitrogen tanah.\n\nCatat tanggal tanam, jenis tanaman, pupuk yang digunakan, dan hasil panen. Dari catatan itu, petani bisa melihat pola petak mana yang paling subur, tanaman mana yang cocok setelah panen sebelumnya, dan kapan tanah perlu diberi kompos tambahan.',
    gambar = 'rotasi_tanam.jpg',
    tanggal_publish = '2026-05-16'
WHERE id = 1;

UPDATE artikel SET
    judul = 'Teknik Irigasi Tetes untuk Lahan Cabai',
    isi = 'Irigasi tetes membantu air langsung masuk ke area perakaran sehingga pemakaian air lebih hemat dan kelembapan tanah lebih stabil. Pada tanaman cabai, sistem ini juga mengurangi percikan air ke daun yang sering memicu penyakit jamur.\n\nPemasangan dasar bisa dimulai dari tangki air, filter sederhana, pipa utama, lalu selang drip di setiap bedengan. Pastikan lubang tetes berada dekat pangkal tanaman, tetapi tidak menempel langsung pada batang. Jalankan penyiraman lebih singkat namun rutin, terutama saat fase pembungaan dan pembesaran buah.\n\nPeriksa filter, sambungan, dan lubang tetes setiap minggu. Jika ada aliran tersumbat, bersihkan sebelum tanaman mengalami stres air. Catatan jadwal penyiraman akan membantu menentukan pola terbaik sesuai cuaca dan jenis tanah.',
    gambar = 'irigasi_tetes.jpg',
    tanggal_publish = '2026-05-15'
WHERE id = 2;

UPDATE artikel SET
    judul = 'Membuat Kompos Organik yang Siap Pakai di Kebun',
    isi = 'Kompos organik memperbaiki struktur tanah, meningkatkan aktivitas mikroba, dan membantu tanaman menyerap nutrisi dengan lebih stabil. Bahan yang bisa digunakan antara lain daun kering, sisa sayuran, rumput, sekam, kotoran ternak matang, dan sedikit tanah kebun.\n\nSusun bahan cokelat seperti daun kering dan sekam bergantian dengan bahan hijau seperti sisa sayuran. Jaga kelembapan seperti spons yang diperas, tidak terlalu basah dan tidak terlalu kering. Balik tumpukan setiap satu sampai dua minggu agar proses penguraian merata.\n\nKompos siap dipakai ketika warnanya gelap, remah, tidak panas, dan berbau tanah segar. Gunakan sebagai pupuk dasar sebelum tanam atau taburan tipis di sekitar tanaman yang sedang tumbuh.',
    gambar = 'kompos_organik.jpg',
    tanggal_publish = '2026-05-14'
WHERE id = 3;

UPDATE artikel SET
    judul = 'Pengendalian Hama Terpadu pada Sayuran',
    isi = 'Pengendalian hama terpadu tidak langsung mengandalkan pestisida. Prinsip utamanya adalah memantau tanaman secara rutin, mengenali gejala sejak awal, lalu memilih tindakan paling ringan yang tetap efektif.\n\nPeriksa bagian bawah daun, pucuk muda, dan area sekitar bunga. Gunakan perangkap kuning untuk memantau serangga kecil, bersihkan gulma, dan jaga jarak tanam agar sirkulasi udara baik. Tanaman yang sehat biasanya lebih tahan terhadap serangan ringan.\n\nJika populasi hama meningkat, gunakan pengendalian mekanis atau bahan hayati terlebih dahulu. Pestisida kimia sebaiknya menjadi pilihan terakhir, dipakai sesuai dosis, waktu aplikasi, dan masa tunggu panen.',
    gambar = 'pengendalian_hama.jpg',
    tanggal_publish = '2026-05-13'
WHERE id = 4;

UPDATE artikel SET
    judul = 'Checklist Panen dan Pascapanen Sayuran Segar',
    isi = 'Panen yang baik dimulai sebelum pisau menyentuh tanaman. Siapkan keranjang bersih, area sortasi teduh, air bersih bila diperlukan, dan catatan jumlah panen. Panen pada pagi hari membantu sayuran tetap segar karena suhu belum terlalu tinggi.\n\nPisahkan hasil panen berdasarkan ukuran, kondisi fisik, dan tingkat kematangan. Buang daun rusak atau bagian yang terlalu tua. Hindari menumpuk sayuran terlalu tinggi karena tekanan dapat membuat hasil panen cepat layu dan memar.\n\nSetelah sortasi, simpan di tempat teduh dan segera kirim ke pasar atau pembeli. Catat volume panen, harga jual, dan pembeli utama agar keputusan tanam berikutnya lebih berbasis data.',
    gambar = 'panen_pascapanen.jpg',
    tanggal_publish = '2026-05-12'
WHERE id = 5;

UPDATE artikel SET
    judul = 'Membaca Harga Pasar Sebelum Menjual Hasil Panen',
    isi = 'Harga pasar dapat berubah cepat, terutama untuk komoditas seperti cabai, bawang, dan sayuran segar. Petani perlu memantau harga beberapa hari sebelum panen agar bisa menentukan waktu jual, tujuan pasar, dan strategi sortasi.\n\nBandingkan harga dari pasar lokal, pengepul, dan sumber informasi nasional. Jangan hanya melihat harga tertinggi, tetapi perhatikan juga biaya transportasi, risiko susut, volume yang diminta, dan kecepatan pembayaran.\n\nData harga yang dicatat secara rutin akan membantu membaca pola musiman. Saat harga mulai naik dan kualitas panen baik, petani bisa memprioritaskan komoditas yang paling cepat memberi margin. Saat harga turun, sortasi dan pengemasan yang rapi bisa membantu menjaga nilai jual.',
    gambar = 'harga_pasar_petani.jpg',
    tanggal_publish = '2026-05-11'
WHERE id = 6;

DELETE FROM harga_pasar
WHERE tanggal = '2026-05-15'
  AND nama_komoditas IN (
    'Cabai Rawit Merah',
    'Cabai Merah Besar',
    'Cabai Merah Keriting',
    'Cabai Rawit Hijau',
    'Bawang Merah',
    'Bawang Putih',
    'Beras Medium I',
    'Beras Medium II',
    'Beras Super I',
    'Beras Super II',
    'Gula Pasir Lokal',
    'Minyak Goreng Curah'
  );

INSERT INTO harga_pasar (nama_komoditas, harga, satuan, tanggal) VALUES
('Cabai Rawit Merah', 77750, 'kg', '2026-05-15'),
('Cabai Merah Besar', 61400, 'kg', '2026-05-15'),
('Cabai Merah Keriting', 56950, 'kg', '2026-05-15'),
('Cabai Rawit Hijau', 47400, 'kg', '2026-05-15'),
('Bawang Merah', 52500, 'kg', '2026-05-15'),
('Bawang Putih', 42950, 'kg', '2026-05-15'),
('Beras Medium I', 16000, 'kg', '2026-05-15'),
('Beras Medium II', 15800, 'kg', '2026-05-15'),
('Beras Super I', 17250, 'kg', '2026-05-15'),
('Beras Super II', 17000, 'kg', '2026-05-15'),
('Gula Pasir Lokal', 19500, 'kg', '2026-05-15'),
('Minyak Goreng Curah', 20000, 'liter', '2026-05-15');

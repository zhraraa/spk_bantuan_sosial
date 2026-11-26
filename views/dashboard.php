<?php
require_once __DIR__ . '/../config/koneksi.php';
require_once __DIR__ . '/../fuzzy/mamdani.php';

$pesan = "";

// 1. Fitur Isi Sampel Data Otomatis (Jika Database Kosong)
if (isset($_POST['isi_sampel'])) {
    $samples = [
        ['Budi Santoso', 2000000, 5, 15, 15, 15, 45, 30], // Kasus Pak Budi di PDF
        ['Slamet Riyadi', 800000, 5, 5, 5, 10, 20, 20],   // Layak
        ['M. Kasim', 1200000, 4, 10, 10, 15, 35, 30],      // Layak
        ['Wagiman', 1800000, 3, 15, 20, 20, 55, 45],       // Dipertimbangkan
        ['Hendro Gunawan', 3800000, 1, 25, 25, 25, 75, 85] // Tidak Layak
    ];

    foreach ($samples as $s) {
        $nama = $s[0];
        $cek = mysqli_query($conn, "SELECT id FROM input_kriteria WHERE nama = '$nama'");
        if (mysqli_num_rows($cek) == 0) {
            mysqli_query($conn, "INSERT INTO input_kriteria (nama, pendapatan, tanggungan, dinding, lantai, atap, total_kelayakan_rumah, bekerja)
                                 VALUES ('$s[0]', $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7])");
        }
    }
    $pesan = "Data sampel warga berhasil dimasukkan.";
}

// 2. Hitung & Sinkronisasi Fuzzy Mamdani
if (isset($_POST['proses_semua'])) {
    $semua_hasil = prosesFuzzyDenganSkor($conn, null, 'titik_tengah');

    foreach ($semua_hasil as $h) {
        $id_calon = $h['id'];
        $nama     = $h['nama'];
        $skor     = $h['skor_akhir'];
        $kategori = $h['kategori'];
        $metode   = $h['metode_defuzzifikasi'];
        $agregasi = json_encode($h['agregasi']);
        $detail   = json_encode($h['aturan_aktif']);

        $cek = mysqli_query($conn, "SELECT id FROM hasil_penilaian WHERE id_calon = '$id_calon'");
        if (mysqli_num_rows($cek) > 0) {
            mysqli_query($conn, "UPDATE hasil_penilaian SET 
                                 skor_akhir = '$skor', kategori = '$kategori', metode_centroid = '$metode',
                                 agregasi_json = '$agregasi', detail_json = '$detail'
                                 WHERE id_calon = '$id_calon'");
        } else {
            mysqli_query($conn, "INSERT INTO hasil_penilaian (id_calon, nama_calon, skor_akhir, kategori, metode_centroid, agregasi_json, detail_json)
                                 VALUES ('$id_calon', '$nama', '$skor', '$kategori', '$metode', '$agregasi', '$detail')");
        }
    }
    $pesan = "Perhitungan Fuzzy Mamdani berhasil disinkronkan ke database.";
}

// Ambil Statistik
$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM input_kriteria");
$total_warga = mysqli_fetch_assoc($q_total)['total'] ?? 0;

$q_layak = mysqli_query($conn, "SELECT COUNT(*) as total FROM hasil_penilaian WHERE kategori = 'Layak'");
$total_layak = mysqli_fetch_assoc($q_layak)['total'] ?? 0;

$q_cadangan = mysqli_query($conn, "SELECT COUNT(*) as total FROM hasil_penilaian WHERE kategori = 'Dipertimbangkan'");
$total_cadangan = mysqli_fetch_assoc($q_cadangan)['total'] ?? 0;

$q_tidak = mysqli_query($conn, "SELECT COUNT(*) as total FROM hasil_penilaian WHERE kategori = 'Tidak Layak'");
$total_tidak = mysqli_fetch_assoc($q_tidak)['total'] ?? 0;

// Ambil Ranking
$q_ranking = mysqli_query($conn, "
    SELECT h.nama_calon, h.skor_akhir, h.kategori, i.pendapatan, i.tanggungan
    FROM hasil_penilaian h
    JOIN input_kriteria i ON h.id_calon = i.id
    ORDER BY h.skor_akhir DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPK Bantuan Sosial - Fuzzy Mamdani</title>
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="navbar">
    <span class="brand">SPK Bantuan Sosial</span>
    <span class="subtitle">Metode Fuzzy Mamdani</span>
</div>

<div class="container">

    <div class="header">
        <div>
            <h1>Dashboard Penerima Bansos</h1>
            <p>Penilaian kelayakan dengan 54 aturan Fuzzy Mamdani dan metode Centroid</p>
        </div>
        <form method="POST" class="header-actions">
            <button type="submit" name="isi_sampel" class="btn btn-garis">Isi Data Sampel</button>
            <button type="submit" name="proses_semua" class="btn btn-utama">Hitung &amp; Sinkronkan</button>
        </form>
    </div>

    <?php if ($pesan): ?>
        <div class="alert" id="alert"><?= $pesan ?></div>
    <?php endif; ?>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="label"><i class="dot d-navy"></i>Total calon</div>
            <div class="angka"><?= $total_warga ?><small>KK</small></div>
        </div>
        <div class="stat-card">
            <div class="label"><i class="dot d-hijau"></i>Sangat layak</div>
            <div class="angka"><?= $total_layak ?><small>KK</small></div>
        </div>
        <div class="stat-card">
            <div class="label"><i class="dot d-kuning"></i>Dipertimbangkan</div>
            <div class="angka"><?= $total_cadangan ?><small>KK</small></div>
        </div>
        <div class="stat-card">
            <div class="label"><i class="dot d-merah"></i>Tidak layak</div>
            <div class="angka"><?= $total_tidak ?><small>KK</small></div>
        </div>
    </div>

    <div class="grid-utama">
        <div class="card">
            <h3>Proporsi keputusan</h3>
            <div class="chart-wrap">
                <canvas id="chartKelayakan"></canvas>
            </div>
        </div>

        <div class="card">
            <h3>Peringkat prioritas</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama calon</th>
                        <th>Pendapatan</th>
                        <th>Tanggungan</th>
                        <th>Skor</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($q_ranking) > 0): ?>
                        <?php $rank = 1; while ($row = mysqli_fetch_assoc($q_ranking)):
                            if ($row['kategori'] == 'Layak')               $kelas = 'layak';
                            elseif ($row['kategori'] == 'Dipertimbangkan') $kelas = 'timbang';
                            else                                           $kelas = 'tidak';
                        ?>
                            <tr>
                                <td class="rank"><?= $rank++ ?></td>
                                <td class="nama"><?= htmlspecialchars($row['nama_calon']) ?></td>
                                <td>Rp <?= number_format($row['pendapatan'], 0, ',', '.') ?></td>
                                <td><?= $row['tanggungan'] ?> orang</td>
                                <td class="skor"><?= number_format($row['skor_akhir'], 2) ?></td>
                                <td><span class="badge <?= $kelas ?>"><?= htmlspecialchars($row['kategori']) ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-empty">
                                Data belum terisi. Klik <b>"Isi Data Sampel"</b> lalu <b>"Hitung &amp; Sinkronkan"</b>.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
    const ctx = document.getElementById('chartKelayakan').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Layak', 'Dipertimbangkan', 'Tidak Layak'],
            datasets: [{
                data: [<?= $total_layak ?>, <?= $total_cadangan ?>, <?= $total_tidak ?>],
                backgroundColor: ['#3f8f5b', '#e0a030', '#c8453f'],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } }
            }
        }
    });

    // Pesan sukses hilang sendiri setelah 4 detik
    const alertBox = document.getElementById('alert');
    if (alertBox) setTimeout(() => alertBox.style.display = 'none', 4000);
</script>
</body>
</html>
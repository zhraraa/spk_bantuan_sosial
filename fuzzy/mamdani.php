<?php
// Mesin fuzzy Mamdani (bagian: pengambilan data, fuzzifikasi, evaluasi aturan, inferensi, agregasi)
// Defuzzifikasi (centroid) dan perangkingan dikerjakan di tahap berikutnya
// memakai keluaran fungsi prosesFuzzy() di bawah ini.

require_once __DIR__ . '/membership.php';

// Daftar label output (urutan tetap, dipakai agar kunci array selalu lengkap)
const LABEL_KEPUTUSAN = ['Layak', 'Dipertimbangkan', 'Tidak Layak'];


// BAGIAN 1: PENGAMBILAN DATA
// Mengambil calon penerima dari tabel input_kriteria.
// $id = null -> semua data, $id = angka -> satu data saja.
function ambilDataPenerima($conn, $id = null) {
    $sql = "SELECT id, nama, pendapatan, tanggungan, dinding, lantai, atap,
                   total_kelayakan_rumah, bekerja
            FROM input_kriteria";

    if ($id !== null) {
        $sql .= " WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);
    } else {
        $sql .= " ORDER BY id ASC";
        $hasil = mysqli_query($conn, $sql);
    }

    if (!$hasil) {
        die("Gagal mengambil data penerima: " . mysqli_error($conn));
    }

    $daftar_warga = [];
    while ($baris = mysqli_fetch_assoc($hasil)) {
        // skor rumah: pakai total_kelayakan_rumah, kalau kosong (0) hitung ulang dari dinding + lantai + atap
        $skor_rumah = (int) $baris['total_kelayakan_rumah'];
        if ($skor_rumah == 0) {
            $skor_rumah = (int) $baris['dinding'] + (int) $baris['lantai'] + (int) $baris['atap'];
        }

        $daftar_warga[] = [
            'id'            => (int) $baris['id'],
            'nama'          => $baris['nama'],
            'pendapatan'    => (int) $baris['pendapatan'],
            'tanggungan'    => (int) $baris['tanggungan'],
            'skor_rumah'    => $skor_rumah,
            'skor_pekerjaan'=> (int) $baris['bekerja'],
        ];
    }

    return $daftar_warga;
}


// BAGIAN 2: FUZZIFIKASI
// Satu warga -> paket derajat keanggotaan untuk 4 kriteria (setiap kategori dicek).
function fuzzifikasiSemua($warga) {
    return [
        'pendapatan' => fuzzifikasiPendapatan($warga['pendapatan']),
        'tanggungan' => fuzzifikasiTanggungan($warga['tanggungan']),
        'rumah'      => fuzzifikasiRumah($warga['skor_rumah']),
        'pekerjaan'  => fuzzifikasiPekerjaan($warga['skor_pekerjaan']),
    ];
}


// BAGIAN 3: MEMUAT BASIS ATURAN
// knowledge_based.php mendefinisikan $daftar_aturan (R1 - R54).
function muatAturan() {
    require __DIR__ . '/knowledge_based.php';
    return $daftar_aturan;
}


// BAGIAN 4: EVALUASI ATURAN & INFERENSI (operator AND = min)
// Mengembalikan hanya aturan AKTIF (kekuatan > 0).
function evaluasiAturan($derajat, $daftar_aturan) {
    $aturan_aktif = [];

    foreach ($daftar_aturan as $aturan) {
        $nilai_pendapatan = $derajat['pendapatan'][$aturan['pendapatan']];
        $nilai_tanggungan = $derajat['tanggungan'][$aturan['tanggungan']];
        $nilai_rumah      = $derajat['rumah'][$aturan['rumah']];
        $nilai_pekerjaan  = $derajat['pekerjaan'][$aturan['pekerjaan']];

        // kekuatan aturan = nilai terkecil dari keempat syarat
        $kekuatan = min($nilai_pendapatan, $nilai_tanggungan, $nilai_rumah, $nilai_pekerjaan);

        // aturan mati (0) diabaikan
        if ($kekuatan > 0) {
            $aturan_aktif[] = [
                'kode'      => $aturan['kode'],
                'kondisi'   => [
                    'pendapatan' => $aturan['pendapatan'],
                    'tanggungan' => $aturan['tanggungan'],
                    'rumah'      => $aturan['rumah'],
                    'pekerjaan'  => $aturan['pekerjaan'],
                ],
                'kekuatan'  => $kekuatan,
                'keputusan' => $aturan['keputusan'],
            ];
        }
    }

    return $aturan_aktif;
}


// BAGIAN 5: AGREGASI (max per label keputusan)
function agregasiAturan($aturan_aktif) {
    $agregasi = [];
    foreach (LABEL_KEPUTUSAN as $label) {
        $agregasi[$label] = 0;
    }

    foreach ($aturan_aktif as $aturan) {
        $label = $aturan['keputusan'];
        if ($aturan['kekuatan'] > $agregasi[$label]) {
            $agregasi[$label] = $aturan['kekuatan'];
        }
    }

    return $agregasi;
}


// BAGIAN 6: PROSES UTAMA
// Fuzzifikasi -> evaluasi aturan -> agregasi untuk satu warga (tanpa database).
function prosesFuzzyWarga($warga, $daftar_aturan) {
    $derajat      = fuzzifikasiSemua($warga);
    $aturan_aktif = evaluasiAturan($derajat, $daftar_aturan);
    $agregasi     = agregasiAturan($aturan_aktif);

    return [
        'id'           => $warga['id'] ?? null,
        'nama'         => $warga['nama'] ?? null,
        'input'        => $warga,
        'derajat'      => $derajat,
        'aturan_aktif' => $aturan_aktif,
        'agregasi'     => $agregasi,
    ];
}

// Fuzzifikasi -> evaluasi aturan -> agregasi untuk semua warga di database.
// Hasilnya dipakai tahap defuzzifikasi (centroid) pada $hasil[i]['agregasi'].
function prosesFuzzy($conn, $id = null) {
    $daftar_aturan = muatAturan();
    $daftar_warga  = ambilDataPenerima($conn, $id);

    $hasil = [];
    foreach ($daftar_warga as $warga) {
        $hasil[] = prosesFuzzyWarga($warga, $daftar_aturan);
    }

    return $hasil;
}
?>
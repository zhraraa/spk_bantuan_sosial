<?php

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

function normalisasiAgregasiOutput($agregasi) {
    if (!is_array($agregasi)) {
        throw new InvalidArgumentException('Data agregasi harus berupa array.');
    }

    $hasil = [
        'Layak' => 0.0,
        'Dipertimbangkan' => 0.0,
        'Tidak Layak' => 0.0,
    ];

    foreach ($hasil as $label => $nilaiAwal) {
        if (array_key_exists($label, $agregasi)) {
            if (!is_numeric($agregasi[$label])) {
                throw new InvalidArgumentException('Derajat agregasi ' . $label . ' harus berupa angka.');
            }

            $nilai = (float) $agregasi[$label];
            if ($nilai < -0.000001 || $nilai > 1.000001) {
                throw new InvalidArgumentException('Derajat agregasi ' . $label . ' harus berada pada rentang 0 sampai 1.');
            }

            $hasil[$label] = max(0.0, min(1.0, $nilai));
        }
    }

    return $hasil;
}

function defuzzifikasiCentroidTitikTengah($agregasi) {
    $agregasi = normalisasiAgregasiOutput($agregasi);

    $titikTengah = [
        'Tidak Layak' => 20.0,
        'Dipertimbangkan' => 50.0,
        'Layak' => 80.0,
    ];

    $jumlahBerbobot = 0.0;
    $jumlahDerajat = 0.0;

    foreach ($titikTengah as $label => $titik) {
        $derajat = $agregasi[$label];
        $jumlahBerbobot += $derajat * $titik;
        $jumlahDerajat += $derajat;
    }

    if ($jumlahDerajat <= 0.0) {
        return null;
    }

    return $jumlahBerbobot / $jumlahDerajat;
}

function keanggotaanOutputKelayakan($label, $z) {
    $z = (float) $z;

    if ($label === 'Tidak Layak') {
        if ($z <= 30.0) {
            return 1.0;
        }
        if ($z < 50.0) {
            return (50.0 - $z) / 20.0;
        }
        return 0.0;
    }

    if ($label === 'Dipertimbangkan') {
        if ($z <= 30.0 || $z >= 70.0) {
            return 0.0;
        }
        if ($z <= 50.0) {
            return ($z - 30.0) / 20.0;
        }
        return (70.0 - $z) / 20.0;
    }

    if ($label === 'Layak') {
        if ($z <= 50.0) {
            return 0.0;
        }
        if ($z < 70.0) {
            return ($z - 50.0) / 20.0;
        }
        return 1.0;
    }

    throw new InvalidArgumentException('Label output tidak dikenal: ' . $label);
}

function defuzzifikasiCentroidKurvaMamdani($agregasi, $jumlahInterval = 10000) {
    $agregasi = normalisasiAgregasiOutput($agregasi);
    $jumlahInterval = (int) $jumlahInterval;

    if ($jumlahInterval < 100 || $jumlahInterval > 1000000) {
        throw new InvalidArgumentException('Jumlah interval harus berada pada rentang 100 sampai 1.000.000.');
    }

    $dx = 100.0 / $jumlahInterval;
    $luas = 0.0;
    $momen = 0.0;
    $zSebelumnya = 0.0;

    $muSebelumnya = max(
        min(keanggotaanOutputKelayakan('Tidak Layak', 0.0), $agregasi['Tidak Layak']),
        min(keanggotaanOutputKelayakan('Dipertimbangkan', 0.0), $agregasi['Dipertimbangkan']),
        min(keanggotaanOutputKelayakan('Layak', 0.0), $agregasi['Layak'])
    );

    for ($i = 1; $i <= $jumlahInterval; $i++) {
        $z = $i * $dx;

        $muTidakLayak = min(keanggotaanOutputKelayakan('Tidak Layak', $z), $agregasi['Tidak Layak']);
        $muDipertimbangkan = min(keanggotaanOutputKelayakan('Dipertimbangkan', $z), $agregasi['Dipertimbangkan']);
        $muLayak = min(keanggotaanOutputKelayakan('Layak', $z), $agregasi['Layak']);
        $mu = max($muTidakLayak, $muDipertimbangkan, $muLayak);

        // Aturan trapesium untuk luas dan momen pertama.
        $luas += (($muSebelumnya + $mu) / 2.0) * $dx;
        $momen += ((($zSebelumnya * $muSebelumnya) + ($z * $mu)) / 2.0) * $dx;

        $zSebelumnya = $z;
        $muSebelumnya = $mu;
    }

    if ($luas <= 0.0) {
        return null;
    }

    return $momen / $luas;
}

// Fungsi utama defuzzifikasi. //
function defuzzifikasiCentroid($agregasi, $metode = 'titik_tengah') {
    if ($metode === 'titik_tengah') {
        return defuzzifikasiCentroidTitikTengah($agregasi);
    }

    if ($metode === 'kurva_mamdani') {
        return defuzzifikasiCentroidKurvaMamdani($agregasi);
    }

    throw new InvalidArgumentException('Metode defuzzifikasi tidak dikenal. Gunakan titik_tengah atau kurva_mamdani.');
}

// Mengubah skor akhir menjadi kategori keputusan sesuai halaman 13 PDF. Z < 50: Tidak Layak; 50 <= Z < 70: Dipertimbangkan; Z >= 70: Layak.
function tentukanKategoriPrioritas($skorAkhir) {
    if (!is_numeric($skorAkhir)) {
        return 'Belum Dinilai';
    }

    $skorAkhir = (float) $skorAkhir;

    if ($skorAkhir < 50.0) {
        return 'Tidak Layak';
    }
    if ($skorAkhir < 70.0) {
        return 'Dipertimbangkan';
    }
    return 'Layak';
}

// Menambahkan skor akhir, metode, dan kategori ke satu hasil prosesFuzzyWarga(). //
function tambahkanHasilDefuzzifikasi($hasilFuzzy, $metode = 'titik_tengah') {
    if (!isset($hasilFuzzy['agregasi'])) {
        throw new InvalidArgumentException('Hasil fuzzy tidak memiliki data agregasi.');
    }

    $skorAkhir = defuzzifikasiCentroid($hasilFuzzy['agregasi'], $metode);
    $kategori = ($skorAkhir === null)
        ? 'Belum Dinilai'
        : tentukanKategoriPrioritas($skorAkhir);

    $hasilFuzzy['skor_akhir'] = ($skorAkhir === null) ? null : round($skorAkhir, 2);
    $hasilFuzzy['kategori'] = $kategori;
    $hasilFuzzy['metode_defuzzifikasi'] = $metode;

    return $hasilFuzzy;
}

// Proses lengkap untuk satu atau semua calon penerima: fuzzifikasi -> inferensi -> agregasi -> defuzzifikasi -> kategori //
function prosesFuzzyDenganSkor($conn, $id = null, $metode = 'titik_tengah') {
    $hasilFuzzy = prosesFuzzy($conn, $id);
    $hasilLengkap = [];

    foreach ($hasilFuzzy as $hasil) {
        $hasilLengkap[] = tambahkanHasilDefuzzifikasi($hasil, $metode);
    }

    return $hasilLengkap;
}
?>
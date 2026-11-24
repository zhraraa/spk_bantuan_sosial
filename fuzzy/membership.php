<?php
// mengubah angka mentah menjadi nilai derajat keanggotaan (0 - 1)

// 1. KRITERIA PENDAPATAN
// kategori: rendah, sedang, tinggi
// batas: 1.500.000 - 2.500.000 - 4.000.000
function fuzzifikasiPendapatan($pendapatan) {
    $derajat_pendapatan = [
        'Rendah' => 0,
        'Sedang' => 0,
        'Tinggi' => 0
    ];

    // kategori RENDAH (tinggi kalo <= 1.5jt, lalu kurva turun sampai 2.5jt)
    if ($pendapatan <= 1500000) {
        $derajat_pendapatan['Rendah'] = 1;
    } elseif ($pendapatan > 1500000 && $pendapatan < 2500000) {
        $derajat_pendapatan['Rendah'] = (2500000 - $pendapatan) / (2500000 - 1500000);
    } else {
        $derajat_pendapatan['Rendah'] = 0;
    }

    // kategori SEDANG (kurva naik 1.5jt - 2.5jt, puncak di 2.5jt, kurva turun 2.5jt - 4jt)
    if ($pendapatan > 1500000 && $pendapatan < 2500000) {
        $derajat_pendapatan['Sedang'] = ($pendapatan - 1500000) / (2500000 - 1500000);
    } elseif ($pendapatan == 2500000) {
        $derajat_pendapatan['Sedang'] = 1;
    } elseif ($pendapatan > 2500000 && $pendapatan < 4000000) {
        $derajat_pendapatan['Sedang'] = (4000000 - $pendapatan) / (4000000 - 2500000);
    } else {
        $derajat_pendapatan['Sedang'] = 0;
    }

    // kategori TINGGI (kurva naik mulai dari 2.5jt, kurva tertinggi >= 4jt)
    if ($pendapatan <= 2500000) {
        $derajat_pendapatan['Tinggi'] = 0;
    } elseif ($pendapatan > 2500000 && $pendapatan < 4000000) {
        $derajat_pendapatan['Tinggi'] = ($pendapatan - 2500000) / (4000000 - 2500000);
    } else {
        $derajat_pendapatan['Tinggi'] = 1;
    }

    return $derajat_pendapatan;
}

// 2. KRITERIA TANGGUNGAN
// kategori: sedikit, sedang, banyak
// batas: 2 - 4 - 6
function fuzzifikasiTanggungan($tanggungan) {
    $derajat_tanggungan = [
        'Sedikit' => 0,
        'Sedang'  => 0,
        'Banyak'  => 0
    ];

    // kategori SEDIKIT (kurva tinggi <= 2 orang, kurva turun semakin dekat ke 4 orang)
    if ($tanggungan <= 2) {
        $derajat_tanggungan['Sedikit'] = 1;
    } elseif ($tanggungan > 2 && $tanggungan < 4) {
        $derajat_tanggungan['Sedikit'] = (4 - $tanggungan) / (4 - 2);
    } else {
        $derajat_tanggungan['Sedikit'] = 0;
    }

    // kategori SEDANG (kurva naik 2 - 4, puncak di 4, turun 4 - 6)
    if ($tanggungan > 2 && $tanggungan < 4) {
        $derajat_tanggungan['Sedang'] = ($tanggungan - 2) / (4 - 2);
    } elseif ($tanggungan == 4) {
        $derajat_tanggungan['Sedang'] = 1;
    } elseif ($tanggungan > 4 && $tanggungan < 6) {
        $derajat_tanggungan['Sedang'] = (6 - $tanggungan) / (6 - 4);
    } else {
        $derajat_tanggungan['Sedang'] = 0;
    }

    // kategori BANYAK (kurva naik mulai dari 4 orang, paling tinggi pas >= 6 orang)
    if ($tanggungan <= 4) {
        $derajat_tanggungan['Banyak'] = 0;
    } elseif ($tanggungan > 4 && $tanggungan < 6) {
        $derajat_tanggungan['Banyak'] = ($tanggungan - 4) / (6 - 4);
    } else {
        $derajat_tanggungan['Banyak'] = 1;
    }

    return $derajat_tanggungan;
}

// 3. KRITERIA KONDISI RUMAH (Skor Komposit: 0 - 100)
// kategori: buruk, sedang, baik
// batas: 35 - 55 - 75
function fuzzifikasiRumah($skor_rumah) {
    $derajat_rumah = [
        'Buruk'  => 0,
        'Sedang' => 0,
        'Baik'   => 0
    ];

    // kategori BURUK (kurva tertinggi <= 35, kurva menurun sampai 55)
    if ($skor_rumah <= 35) {
        $derajat_rumah['Buruk'] = 1;
    } elseif ($skor_rumah > 35 && $skor_rumah < 55) {
        $derajat_rumah['Buruk'] = (55 - $skor_rumah) / (55 - 35);
    } else {
        $derajat_rumah['Buruk'] = 0;
    }

    // kategori SEDANG (kurva naik 35 - 55, puncak di 55, turun 55 - 75)
    if ($skor_rumah > 35 && $skor_rumah < 55) {
        $derajat_rumah['Sedang'] = ($skor_rumah - 35) / (55 - 35);
    } elseif ($skor_rumah == 55) {
        $derajat_rumah['Sedang'] = 1;
    } elseif ($skor_rumah > 55 && $skor_rumah < 75) {
        $derajat_rumah['Sedang'] = (75 - $skor_rumah) / (75 - 55);
    } else {
        $derajat_rumah['Sedang'] = 0;
    }

    // kategori BAIK (kurva naik mulai dari 55, kurva paling tinggi kalo >= 75)
    if ($skor_rumah <= 55) {
        $derajat_rumah['Baik'] = 0;
    } elseif ($skor_rumah > 55 && $skor_rumah < 75) {
        $derajat_rumah['Baik'] = ($skor_rumah - 55) / (75 - 55);
    } else {
        $derajat_rumah['Baik'] = 1;
    }

    return $derajat_rumah;
}

// 4. KRITERIA PEKERJAAN (skor Kategori: 0 - 100)
// kategori: tidak tetap, tetap
// batas: 35 - 65
function fuzzifikasiPekerjaan($skor_pekerjaan) {
    $derajat_pekerjaan = [
        'Tidak Tetap' => 0,
        'Tetap'       => 0
    ];

    // kategori TIDAK TETAP (kurva paling tinggi pas <= 35, kurva menurun sampai 65)
    if ($skor_pekerjaan <= 35) {
        $derajat_pekerjaan['Tidak Tetap'] = 1;
    } elseif ($skor_pekerjaan > 35 && $skor_pekerjaan < 65) {
        $derajat_pekerjaan['Tidak Tetap'] = (65 - $skor_pekerjaan) / (65 - 35);
    } else {
        $derajat_pekerjaan['Tidak Tetap'] = 0;
    }

    // kategori TETAP (kurva naik mulai dari 35, paling tinggi pas >= 65)
    if ($skor_pekerjaan <= 35) {
        $derajat_pekerjaan['Tetap'] = 0;
    } elseif ($skor_pekerjaan > 35 && $skor_pekerjaan < 65) {
        $derajat_pekerjaan['Tetap'] = ($skor_pekerjaan - 35) / (65 - 35);
    } else {
        $derajat_pekerjaan['Tetap'] = 1;
    }

    return $derajat_pekerjaan;
}
?>
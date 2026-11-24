<?php
// kombinasi: 3 Pendapatan x 3 Tanggungan x 3 Rumah x 2 Pekerjaan = 54 Aturan

$daftar_aturan = [
    // KELOMPOK 1: PENDAPATAN = RENDAH (R1 - R18)
    ['kode' => 'R1',  'pendapatan' => 'Rendah', 'tanggungan' => 'Banyak',  'rumah' => 'Buruk',  'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Layak'],
    ['kode' => 'R2',  'pendapatan' => 'Rendah', 'tanggungan' => 'Banyak',  'rumah' => 'Buruk',  'pekerjaan' => 'Tetap',       'keputusan' => 'Layak'],
    ['kode' => 'R3',  'pendapatan' => 'Rendah', 'tanggungan' => 'Banyak',  'rumah' => 'Sedang', 'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Layak'],
    ['kode' => 'R4',  'pendapatan' => 'Rendah', 'tanggungan' => 'Banyak',  'rumah' => 'Sedang', 'pekerjaan' => 'Tetap',       'keputusan' => 'Layak'],
    ['kode' => 'R5',  'pendapatan' => 'Rendah', 'tanggungan' => 'Banyak',  'rumah' => 'Baik',   'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Layak'],
    ['kode' => 'R6',  'pendapatan' => 'Rendah', 'tanggungan' => 'Banyak',  'rumah' => 'Baik',   'pekerjaan' => 'Tetap',       'keputusan' => 'Dipertimbangkan'],

    ['kode' => 'R7',  'pendapatan' => 'Rendah', 'tanggungan' => 'Sedang',  'rumah' => 'Buruk',  'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Layak'],
    ['kode' => 'R8',  'pendapatan' => 'Rendah', 'tanggungan' => 'Sedang',  'rumah' => 'Buruk',  'pekerjaan' => 'Tetap',       'keputusan' => 'Layak'],
    ['kode' => 'R9',  'pendapatan' => 'Rendah', 'tanggungan' => 'Sedang',  'rumah' => 'Sedang', 'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Layak'],
    ['kode' => 'R10', 'pendapatan' => 'Rendah', 'tanggungan' => 'Sedang',  'rumah' => 'Sedang', 'pekerjaan' => 'Tetap',       'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R11', 'pendapatan' => 'Rendah', 'tanggungan' => 'Sedang',  'rumah' => 'Baik',   'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R12', 'pendapatan' => 'Rendah', 'tanggungan' => 'Sedang',  'rumah' => 'Baik',   'pekerjaan' => 'Tetap',       'keputusan' => 'Dipertimbangkan'],

    ['kode' => 'R13', 'pendapatan' => 'Rendah', 'tanggungan' => 'Sedikit', 'rumah' => 'Buruk',  'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Layak'],
    ['kode' => 'R14', 'pendapatan' => 'Rendah', 'tanggungan' => 'Sedikit', 'rumah' => 'Buruk',  'pekerjaan' => 'Tetap',       'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R15', 'pendapatan' => 'Rendah', 'tanggungan' => 'Sedikit', 'rumah' => 'Sedang', 'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R16', 'pendapatan' => 'Rendah', 'tanggungan' => 'Sedikit', 'rumah' => 'Sedang', 'pekerjaan' => 'Tetap',       'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R17', 'pendapatan' => 'Rendah', 'tanggungan' => 'Sedikit', 'rumah' => 'Baik',   'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R18', 'pendapatan' => 'Rendah', 'tanggungan' => 'Sedikit', 'rumah' => 'Baik',   'pekerjaan' => 'Tetap',       'keputusan' => 'Tidak Layak'],

    // KELOMPOK 2: PENDAPATAN = SEDANG (R19 - R36)
    ['kode' => 'R19', 'pendapatan' => 'Sedang', 'tanggungan' => 'Banyak',  'rumah' => 'Buruk',  'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Layak'],
    ['kode' => 'R20', 'pendapatan' => 'Sedang', 'tanggungan' => 'Banyak',  'rumah' => 'Buruk',  'pekerjaan' => 'Tetap',       'keputusan' => 'Layak'],
    ['kode' => 'R21', 'pendapatan' => 'Sedang', 'tanggungan' => 'Banyak',  'rumah' => 'Sedang', 'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Layak'],
    ['kode' => 'R22', 'pendapatan' => 'Sedang', 'tanggungan' => 'Banyak',  'rumah' => 'Sedang', 'pekerjaan' => 'Tetap',       'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R23', 'pendapatan' => 'Sedang', 'tanggungan' => 'Banyak',  'rumah' => 'Baik',   'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R24', 'pendapatan' => 'Sedang', 'tanggungan' => 'Banyak',  'rumah' => 'Baik',   'pekerjaan' => 'Tetap',       'keputusan' => 'Dipertimbangkan'],

    ['kode' => 'R25', 'pendapatan' => 'Sedang', 'tanggungan' => 'Sedang',  'rumah' => 'Buruk',  'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Layak'],
    ['kode' => 'R26', 'pendapatan' => 'Sedang', 'tanggungan' => 'Sedang',  'rumah' => 'Buruk',  'pekerjaan' => 'Tetap',       'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R27', 'pendapatan' => 'Sedang', 'tanggungan' => 'Sedang',  'rumah' => 'Sedang', 'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R28', 'pendapatan' => 'Sedang', 'tanggungan' => 'Sedang',  'rumah' => 'Sedang', 'pekerjaan' => 'Tetap',       'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R29', 'pendapatan' => 'Sedang', 'tanggungan' => 'Sedang',  'rumah' => 'Baik',   'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Tidak Layak'],
    ['kode' => 'R30', 'pendapatan' => 'Sedang', 'tanggungan' => 'Sedang',  'rumah' => 'Baik',   'pekerjaan' => 'Tetap',       'keputusan' => 'Tidak Layak'],

    ['kode' => 'R31', 'pendapatan' => 'Sedang', 'tanggungan' => 'Sedikit', 'rumah' => 'Buruk',  'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R32', 'pendapatan' => 'Sedang', 'tanggungan' => 'Sedikit', 'rumah' => 'Buruk',  'pekerjaan' => 'Tetap',       'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R33', 'pendapatan' => 'Sedang', 'tanggungan' => 'Sedikit', 'rumah' => 'Sedang', 'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Tidak Layak'],
    ['kode' => 'R34', 'pendapatan' => 'Sedang', 'tanggungan' => 'Sedikit', 'rumah' => 'Sedang', 'pekerjaan' => 'Tetap',       'keputusan' => 'Tidak Layak'],
    ['kode' => 'R35', 'pendapatan' => 'Sedang', 'tanggungan' => 'Sedikit', 'rumah' => 'Baik',   'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Tidak Layak'],
    ['kode' => 'R36', 'pendapatan' => 'Sedang', 'tanggungan' => 'Sedikit', 'rumah' => 'Baik',   'pekerjaan' => 'Tetap',       'keputusan' => 'Tidak Layak'],

    // KELOMPOK 3: PENDAPATAN = TINGGI (R37 - R54)
    ['kode' => 'R37', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Banyak',  'rumah' => 'Buruk',  'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R38', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Banyak',  'rumah' => 'Buruk',  'pekerjaan' => 'Tetap',       'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R39', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Banyak',  'rumah' => 'Sedang', 'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R40', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Banyak',  'rumah' => 'Sedang', 'pekerjaan' => 'Tetap',       'keputusan' => 'Tidak Layak'],
    ['kode' => 'R41', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Banyak',  'rumah' => 'Baik',   'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Tidak Layak'],
    ['kode' => 'R42', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Banyak',  'rumah' => 'Baik',   'pekerjaan' => 'Tetap',       'keputusan' => 'Tidak Layak'],

    ['kode' => 'R43', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Sedang',  'rumah' => 'Buruk',  'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Dipertimbangkan'],
    ['kode' => 'R44', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Sedang',  'rumah' => 'Buruk',  'pekerjaan' => 'Tetap',       'keputusan' => 'Tidak Layak'],
    ['kode' => 'R45', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Sedang',  'rumah' => 'Sedang', 'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Tidak Layak'],
    ['kode' => 'R46', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Sedang',  'rumah' => 'Sedang', 'pekerjaan' => 'Tetap',       'keputusan' => 'Tidak Layak'],
    ['kode' => 'R47', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Sedang',  'rumah' => 'Baik',   'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Tidak Layak'],
    ['kode' => 'R48', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Sedang',  'rumah' => 'Baik',   'pekerjaan' => 'Tetap',       'keputusan' => 'Tidak Layak'],

    ['kode' => 'R49', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Sedikit', 'rumah' => 'Buruk',  'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Tidak Layak'],
    ['kode' => 'R50', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Sedikit', 'rumah' => 'Buruk',  'pekerjaan' => 'Tetap',       'keputusan' => 'Tidak Layak'],
    ['kode' => 'R51', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Sedikit', 'rumah' => 'Sedang', 'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Tidak Layak'],
    ['kode' => 'R52', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Sedikit', 'rumah' => 'Sedang', 'pekerjaan' => 'Tetap',       'keputusan' => 'Tidak Layak'],
    ['kode' => 'R53', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Sedikit', 'rumah' => 'Baik',   'pekerjaan' => 'Tidak Tetap', 'keputusan' => 'Tidak Layak'],
    ['kode' => 'R54', 'pendapatan' => 'Tinggi', 'tanggungan' => 'Sedikit', 'rumah' => 'Baik',   'pekerjaan' => 'Tetap',       'keputusan' => 'Tidak Layak'],
];
?>
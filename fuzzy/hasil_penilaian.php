<?php

function simpanBatchHasilPenilaian($conn, $daftarHasil) {
    if (!is_array($daftarHasil)) {
        throw new InvalidArgumentException('Daftar hasil penilaian harus berupa array.');
    }

    if (count($daftarHasil) === 0) {
        return 0;
    }

    $sqlUpdate = "UPDATE input_kriteria
                  SET skor_kelayakan = ?, output_kelayakan_bansos = ?
                  WHERE id = ?";

    // Nama kolom mengikuti skema hasil_penilaian
    $sqlInsert = "INSERT INTO hasil_penilaian
                  (id_calon, nama_calon, skor_akhir, kategori,
                   metode_centroid, agregasi_json, detail_json)
                  VALUES (?, ?, ?, ?, ?, ?, ?)";

    $conn->begin_transaction();

    try {
        $stmtUpdate = $conn->prepare($sqlUpdate);
        if (!$stmtUpdate) {
            throw new RuntimeException('Gagal menyiapkan update input_kriteria: ' . $conn->error);
        }

        $stmtInsert = $conn->prepare($sqlInsert);
        if (!$stmtInsert) {
            throw new RuntimeException('Gagal menyiapkan insert hasil_penilaian: ' . $conn->error);
        }

        $jumlahTersimpan = 0;

        foreach ($daftarHasil as $hasil) {

            $kolomWajib = [
                'id',
                'nama',
                'kategori',
                'agregasi',
                'metode_defuzzifikasi'
            ];

            foreach ($kolomWajib as $kolom) {
                if (!array_key_exists($kolom, $hasil)) {
                    throw new RuntimeException(
                        "Data hasil fuzzy tidak memiliki key: $kolom"
                    );
                }
            }

            // skor_akhir wajib tersedia, tetapi boleh bernilai NULL.
            if (!array_key_exists('skor_akhir', $hasil)) {
                throw new RuntimeException(
                    'Data hasil fuzzy tidak memiliki skor_akhir.'
                );
            }

            $idCalon = (int) $hasil['id'];
            $namaCalon = (string) $hasil['nama'];
            $metode = (string) $hasil['metode_defuzzifikasi'];

            if ($hasil['skor_akhir'] === null) {

                $skorAkhir = null;
                $kategori = 'Belum Dinilai';

            } else {

                if (!is_numeric($hasil['skor_akhir'])) {
                    throw new RuntimeException('Skor akhir harus berupa angka.');
                }

                $nilaiSkor = (float) $hasil['skor_akhir'];

                if ($nilaiSkor < 0 || $nilaiSkor > 100) {
                    throw new RuntimeException('Skor akhir harus berada pada rentang 0–100.');
                }

                $skorAkhir = number_format($nilaiSkor, 2, '.', '');
                $kategori = (string) $hasil['kategori'];
            }

            $agregasi = normalisasiAgregasiOutput($hasil['agregasi']);

            $agregasiJson = json_encode(
                $agregasi,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            $detailJson = json_encode([
                'input' => $hasil['input'] ?? [],
                'derajat_keanggotaan' => $hasil['derajat'] ?? [],
                'aturan_aktif' => $hasil['aturan_aktif'] ?? [],
                'jumlah_aturan_aktif' => count($hasil['aturan_aktif'] ?? []),
                'agregasi' => $agregasi,
                'skor_akhir' => $skorAkhir,
                'kategori' => $kategori,
                'metode_defuzzifikasi' => $metode
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            if ($agregasiJson === false || $detailJson === false) {
                throw new RuntimeException('Gagal mengubah detail perhitungan menjadi JSON.');
            }

            // Perbarui hasil terbaru di input_kriteria.
            $stmtUpdate->bind_param(
                'ssi',
                $skorAkhir,
                $kategori,
                $idCalon
            );

            if (!$stmtUpdate->execute()) {
                throw new RuntimeException(
                    'Gagal memperbarui data calon: ' . $stmtUpdate->error
                );
            }

            // Simpan snapshot hasil penilaian ke tabel riwayat.
            $stmtInsert->bind_param(
                'issssss',
                $idCalon,
                $namaCalon,
                $skorAkhir,
                $kategori,
                $metode,
                $agregasiJson,
                $detailJson
            );

            if (!$stmtInsert->execute()) {
                throw new RuntimeException(
                    'Gagal menyimpan riwayat: ' . $stmtInsert->error
                );
            }

            $jumlahTersimpan++;
        }

        $stmtUpdate->close();
        $stmtInsert->close();
        $conn->commit();

        return $jumlahTersimpan;
    } catch (Throwable $e) {
        $conn->rollback();
        throw $e;
    }
}
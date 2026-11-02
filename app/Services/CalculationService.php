<?php
namespace App\Services;

use PDO;

class CalculationService {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Predikat mapping sesuai master Excel sheet 'Predikat Penilaian'
     */
    public function getPredikat(float $nilai): string {
        if ($nilai < 50.0) return 'Kurang';
        if ($nilai < 51.0) return 'Kurang';
        if ($nilai < 61.0) return 'Sedang';
        if ($nilai < 76.0) return 'Cukup';
        if ($nilai < 91.0) return 'Baik';
        return 'Amat Baik';
    }

    /**
     * Hitung perolehan individual penilai
     */
    public function calculateIndividualScore(int $penilaianId): array {
        // Ambil data periode & jenis periode
        $pStmt = $this->pdo->prepare("
            SELECT p.id, p.periode_id, p.status, per.jenis_penilaian 
            FROM penilaian p 
            JOIN periode_penilaian per ON p.periode_id = per.id 
            WHERE p.id = ?
        ");
        $pStmt->execute([$penilaianId]);
        $penilaian = $pStmt->fetch();
        if (!$penilaian) return [];

        $isTahunan = ($penilaian['jenis_penilaian'] === 'tahunan');
        // Total skor maks: Tahunan (1-4) = 380, 4-Tahunan (1-5) = 432
        $skorMaksimal = $isTahunan ? 380.0 : 432.0;

        // Ambil perolehan per komponen
        $q = "
            SELECT 
                sk.nomor_komponen, 
                sk.nama_komponen, 
                COUNT(si.id) as total_indikator,
                SUM(COALESCE(dp.skor, 0)) as perolehan_skor,
                (COUNT(si.id) * 4) as skor_maks_komponen
            FROM snapshot_komponen sk
            JOIN snapshot_tugas_utama stu ON stu.snapshot_komponen_id = sk.id
            JOIN snapshot_indikator si ON si.snapshot_tugas_id = stu.id
            LEFT JOIN detail_penilaian dp ON dp.snapshot_indikator_id = si.id AND dp.penilaian_id = ?
            WHERE sk.periode_id = ?
            " . ($isTahunan ? "AND sk.nomor_komponen <= 4" : "") . "
            GROUP BY sk.nomor_komponen, sk.nama_komponen
            ORDER BY sk.nomor_komponen ASC
        ";
        $stmt = $this->pdo->prepare($q);
        $stmt->execute([$penilaianId, $penilaian['periode_id']]);
        $komponenScores = $stmt->fetchAll();

        $totalPerolehan = 0.0;
        foreach ($komponenScores as $ks) {
            $totalPerolehan += (float)$ks['perolehan_skor'];
        }

        // Skala 100: (Total / SkorMaks) * 100
        $nilaiAkhir = $skorMaksimal > 0 ? round(($totalPerolehan / $skorMaksimal) * 100, 2) : 0;
        $predikat = $this->getPredikat($nilaiAkhir);

        // Update database penilaian
        $up = $this->pdo->prepare("UPDATE penilaian SET total_skor = ?, nilai_akhir = ?, predikat = ? WHERE id = ?");
        $up->execute([$totalPerolehan, $nilaiAkhir, $predikat, $penilaianId]);

        return [
            'penilaian_id' => $penilaianId,
            'is_tahunan' => $isTahunan,
            'komponen' => $komponenScores,
            'total_skor' => $totalPerolehan,
            'skor_maksimal' => $skorMaksimal,
            'nilai_akhir' => $nilaiAkhir,
            'predikat' => $predikat
        ];
    }

    /**
     * Hitung rata-rata tiap kelompok penilai & Rekapitulasi NKTE 4 Tahunan
     * Rumus resmi Excel:
     * NKTE = (4*KS + 2*KY + PM + GR + TK + KM) / 10
     */
    public function calculatePeriodeRekap(int $periodeId): array {
        // Ambil seluruh kelompok
        $kelompoks = $this->pdo->query("SELECT * FROM kelompok_penilai ORDER BY id ASC")->fetchAll();
        $groupAverages = [];
        $groupDetails = [];

        foreach ($kelompoks as $k) {
            $kode = $k['kode_kelompok'];
            // Ambil semua penilai wajib di kelompok ini untuk periode ini
            $st = $this->pdo->prepare("
                SELECT pp.id, pp.status_penilaian, p.nilai_akhir, p.status as p_status, u.nama_lengkap
                FROM periode_penilai pp
                JOIN users u ON pp.user_id = u.id
                LEFT JOIN penilaian p ON p.periode_penilai_id = pp.id
                WHERE pp.periode_id = ? AND pp.kelompok_id = ?
            ");
            $st->execute([$periodeId, $k['id']]);
            $raters = $st->fetchAll();

            $totalRaters = count($raters);
            $submittedRaters = 0;
            $sumNilai = 0.0;

            foreach ($raters as $r) {
                if ($r['p_status'] === 'submitted' || $r['p_status'] === 'locked') {
                    $submittedRaters++;
                    $sumNilai += (float)($r['nilai_akhir'] ?? 0);
                }
            }

            $avg = $submittedRaters > 0 ? round($sumNilai / $submittedRaters, 2) : 0.0;
            $isComplete = ($totalRaters > 0 && $submittedRaters === $totalRaters);

            $groupAverages[$kode] = $avg;
            $groupDetails[$kode] = [
                'kelompok' => $k,
                'total_penilai' => $totalRaters,
                'penilai_selesai' => $submittedRaters,
                'is_lengkap' => $isComplete,
                'rata_rata' => $avg,
                'raters' => $raters
            ];
        }

        $ks = $groupAverages['KS'] ?? 0.0;
        $ky = $groupAverages['KY'] ?? 0.0;
        $pm = $groupAverages['WASMAD'] ?? 0.0;
        $gr = $groupAverages['GURU'] ?? 0.0;
        $tk = $groupAverages['TENDIK'] ?? 0.0;
        $km = $groupAverages['KOMITE'] ?? 0.0;

        // Rumus NKTE: (4*KS + 2*KY + WASMAD + GURU + TENDIK + KOMITE) / 10
        $nkte = round((4 * $ks + 2 * $ky + $pm + $gr + $tk + $km) / 10.0, 2);
        $predikat = $this->getPredikat($nkte);

        // Update or insert into rekapitulasi_penilaian
        $chk = $this->pdo->prepare("SELECT id FROM rekapitulasi_penilaian WHERE periode_id = ?");
        $chk->execute([$periodeId]);
        $rekapId = $chk->fetchColumn();

        if ($rekapId) {
            $up = $this->pdo->prepare("
                UPDATE rekapitulasi_penilaian SET 
                    skor_ks = ?, skor_ky = ?, skor_wasmad = ?, skor_guru = ?, skor_tendik = ?, skor_komite = ?,
                    nilai_akhir_nkte = ?, predikat = ?
                WHERE id = ?
            ");
            $up->execute([$ks, $ky, $pm, $gr, $tk, $km, $nkte, $predikat, $rekapId]);
        } else {
            $ins = $this->pdo->prepare("
                INSERT INTO rekapitulasi_penilaian 
                    (periode_id, skor_ks, skor_ky, skor_wasmad, skor_guru, skor_tendik, skor_komite, nilai_akhir_nkte, predikat)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $ins->execute([$periodeId, $ks, $ky, $pm, $gr, $tk, $km, $nkte, $predikat]);
        }

        return [
            'periode_id' => $periodeId,
            'groups' => $groupDetails,
            'nkte' => $nkte,
            'predikat' => $predikat,
            'is_all_complete' => !in_array(false, array_column($groupDetails, 'is_lengkap'))
        ];
    }
}

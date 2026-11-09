<?php
namespace App\Controllers;

use App\Core\Auth;
use PDO;

class FormPenilaianController {

    // ── Helper: query list form penilaian ────────────────────────
    private function queryList(\PDO $pdo, ?int $periodeId = null): array {
        $sql = "
            SELECT pen.id, pen.status, pen.nilai_akhir, pen.predikat, pen.total_skor, pen.submitted_at,
                   u.nama_lengkap as nama_penilai,
                   kp.kode_kelompok, kp.nama_kelompok, kp.bobot_default,
                   per.id as periode_id, per.nama_periode, per.tahun_penilaian, per.jenis_penilaian,
                   m.nama_madrasah, km.nama_lengkap as nama_kamad,
                   (SELECT COUNT(*) FROM snapshot_indikator si
                    WHERE si.periode_id = pen.periode_id) as total_indikator,
                   (SELECT COUNT(*) FROM detail_penilaian dp
                    WHERE dp.penilaian_id = pen.id AND dp.skor IS NOT NULL) as terisi_indikator,
                   (SELECT COUNT(DISTINCT sk.id) FROM snapshot_komponen sk
                    WHERE sk.periode_id = pen.periode_id) as total_komponen,
                   (SELECT COUNT(DISTINCT sk2.id)
                    FROM snapshot_komponen sk2
                    JOIN snapshot_tugas_utama stu2 ON stu2.snapshot_komponen_id = sk2.id
                    JOIN snapshot_indikator si2  ON si2.snapshot_tugas_id = stu2.id
                    JOIN detail_penilaian dp2    ON dp2.snapshot_indikator_id = si2.id
                                                AND dp2.penilaian_id = pen.id
                                                AND dp2.skor IS NOT NULL
                    WHERE sk2.periode_id = pen.periode_id) as komponen_terisi
            FROM penilaian pen
            JOIN periode_penilai pp  ON pp.id  = pen.periode_penilai_id
            JOIN kelompok_penilai kp ON kp.id  = pp.kelompok_id
            JOIN periode_penilaian per ON per.id = pen.periode_id
            JOIN madrasah m          ON m.id   = per.madrasah_id
            JOIN kepala_madrasah km  ON km.id  = per.kamad_id
            JOIN users u             ON u.id   = pen.user_id
        ";

        if ($periodeId) {
            $stmt = $pdo->prepare($sql . " WHERE pen.periode_id = ? ORDER BY kp.id ASC, u.nama_lengkap ASC");
            $stmt->execute([$periodeId]);
        } else {
            $stmt = $pdo->query($sql . " ORDER BY per.id DESC, kp.id ASC, u.nama_lengkap ASC");
        }
        return $stmt->fetchAll();
    }

    // ── Helper: ambil semua periode untuk filter ─────────────────
    private function getPeriodeList(\PDO $pdo, ?int $kamadId = null): array {
        if ($kamadId) {
            $stmt = $pdo->prepare("
                SELECT p.id, p.nama_periode, p.tahun_penilaian
                FROM periode_penilaian p
                WHERE p.kamad_id = ?
                ORDER BY p.id DESC
            ");
            $stmt->execute([$kamadId]);
        } else {
            $stmt = $pdo->query("
                SELECT p.id, p.nama_periode, p.tahun_penilaian
                FROM periode_penilaian p
                ORDER BY p.id DESC
            ");
        }
        return $stmt->fetchAll();
    }

    // ── Halaman daftar form penilaian (admin) ────────────────────
    public function indexAdmin() {
        $pdo = getDbConnection();

        $periodeList = $this->getPeriodeList($pdo);
        $periodeId   = (int)($_GET['periode_id'] ?? ($periodeList[0]['id'] ?? 0)) ?: null;

        $list = $periodeId ? $this->queryList($pdo, $periodeId) : [];

        // Periode aktif terpilih
        $periodeAktif = null;
        if ($periodeId) {
            $ps = $pdo->prepare("SELECT p.*, m.nama_madrasah, km.nama_lengkap as nama_kamad
                FROM periode_penilaian p
                JOIN madrasah m ON m.id = p.madrasah_id
                JOIN kepala_madrasah km ON km.id = p.kamad_id
                WHERE p.id = ?");
            $ps->execute([$periodeId]);
            $periodeAktif = $ps->fetch();
        }

        view('form_penilaian.index', [
            'title'        => 'Form Penilaian Penilai',
            'list'         => $list,
            'periodeList'  => $periodeList,
            'periodeId'    => $periodeId,
            'periodeAktif' => $periodeAktif,
        ]);
    }

    // ── Halaman daftar form penilaian (kamad) ────────────────────
    public function indexKamad() {
        $pdo = getDbConnection();

        $userId  = Auth::id();
        $kmStmt  = $pdo->prepare("SELECT id FROM kepala_madrasah WHERE user_id = ?");
        $kmStmt->execute([$userId]);
        $kamadId = (int)$kmStmt->fetchColumn();

        $periodeList = $this->getPeriodeList($pdo, $kamadId ?: null);
        $periodeId   = (int)($_GET['periode_id'] ?? ($periodeList[0]['id'] ?? 0)) ?: null;

        $list = $periodeId ? $this->queryList($pdo, $periodeId) : [];

        $periodeAktif = null;
        if ($periodeId) {
            $ps = $pdo->prepare("SELECT p.*, m.nama_madrasah, km.nama_lengkap as nama_kamad
                FROM periode_penilaian p
                JOIN madrasah m ON m.id = p.madrasah_id
                JOIN kepala_madrasah km ON km.id = p.kamad_id
                WHERE p.id = ? AND p.kamad_id = ?");
            $ps->execute([$periodeId, $kamadId]);
            $periodeAktif = $ps->fetch();
            if (!$periodeAktif) {
                http_response_code(403);
                echo "Akses ditolak.";
                return;
            }
        }

        view('form_penilaian.index', [
            'title'        => 'Form Penilaian Penilai',
            'list'         => $list,
            'periodeList'  => $periodeList,
            'periodeId'    => $periodeId,
            'periodeAktif' => $periodeAktif,
        ]);
    }

    // ── Detail satu form penilaian (read-only) ───────────────────
    public function detail(int $id) {
        $pdo = getDbConnection();

        // Validasi akses kamad
        if (Auth::hasRole('kamad') && !Auth::hasRole('admin')) {
            $userId = Auth::id();
            $kmStmt = $pdo->prepare("SELECT id FROM kepala_madrasah WHERE user_id = ?");
            $kmStmt->execute([$userId]);
            $kmId = (int)$kmStmt->fetchColumn();

            $chk = $pdo->prepare("
                SELECT COUNT(*) FROM penilaian pen
                JOIN periode_penilaian per ON per.id = pen.periode_id
                WHERE pen.id = ? AND per.kamad_id = ?
            ");
            $chk->execute([$id, $kmId]);
            if (!$chk->fetchColumn()) {
                http_response_code(403);
                echo "Akses ditolak.";
                return;
            }
        }

        $data = $this->getPenilaianData($pdo, $id);
        if (!$data) {
            http_response_code(404);
            echo "Penilaian tidak ditemukan.";
            return;
        }

        view('form_penilaian.detail', array_merge($data, [
            'title' => 'Detail Penilaian — ' . $data['penilaian']['nama_kelompok'],
        ]));
    }

    // ── Cetak (window.print) — tab baru, file standalone ────────
    public function print(int $id) {
        $pdo = getDbConnection();

        if (Auth::hasRole('kamad') && !Auth::hasRole('admin')) {
            $userId = Auth::id();
            $kmStmt = $pdo->prepare("SELECT id FROM kepala_madrasah WHERE user_id = ?");
            $kmStmt->execute([$userId]);
            $kmId = (int)$kmStmt->fetchColumn();

            $chk = $pdo->prepare("
                SELECT COUNT(*) FROM penilaian pen
                JOIN periode_penilaian per ON per.id = pen.periode_id
                WHERE pen.id = ? AND per.kamad_id = ?
            ");
            $chk->execute([$id, $kmId]);
            if (!$chk->fetchColumn()) {
                http_response_code(403);
                echo "Akses ditolak.";
                return;
            }
        }

        $data = $this->getPenilaianData($pdo, $id);
        if (!$data) {
            http_response_code(404);
            echo "Penilaian tidak ditemukan.";
            return;
        }

        // Render halaman cetak standalone (tidak pakai layout)
        extract($data);
        require __DIR__ . '/../../views/form_penilaian/print.php';
        exit;
    }

    // ── Ekspor Excel ─────────────────────────────────────────────
    public function exportExcel(int $id) {
        $pdo  = getDbConnection();
        $data = $this->getPenilaianData($pdo, $id);
        if (!$data) { http_response_code(404); echo "Tidak ditemukan."; return; }

        $p    = $data['penilaian'];
        $tree = $data['tree'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Form Penilaian');

        $aln  = \PhpOffice\PhpSpreadsheet\Style\Alignment::class;
        $fill = \PhpOffice\PhpSpreadsheet\Style\Fill::class;
        $brd  = \PhpOffice\PhpSpreadsheet\Style\Border::class;

        // Judul
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'FORMULIR PENILAIAN KINERJA KEPALA MADRASAH');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13],
            'alignment' => ['horizontal' => $aln::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A2:G2');
        $sheet->setCellValue('A2', 'Kelompok: ' . $p['kode_kelompok'] . ' — ' . $p['nama_kelompok']);
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1e6347']],
            'alignment' => ['horizontal' => $aln::HORIZONTAL_CENTER],
        ]);

        // Info
        $r = 4;
        foreach ([
            ['Madrasah',        $p['nama_madrasah']],
            ['Kepala Madrasah', $p['nama_kamad']],
            ['Penilai',         $p['nama_penilai']],
            ['Periode',         $p['nama_periode'] . ' (' . $p['tahun_penilaian'] . ')'],
            ['Status',          ucfirst($p['status'])],
            ['Nilai Akhir',     $p['nilai_akhir'] !== null ? number_format($p['nilai_akhir'], 2) : '—'],
            ['Predikat',        $p['predikat'] ?? '—'],
        ] as [$k, $v]) {
            $sheet->setCellValue("A{$r}", $k);
            $sheet->setCellValue("B{$r}", $v);
            $sheet->mergeCells("B{$r}:G{$r}");
            $sheet->getStyle("A{$r}")->getFont()->setBold(true);
            $r++;
        }
        $r++;

        // Header tabel
        $headers = ['No', 'Komponen / Tugas / Indikator', 'Kode', 'Data Kinerja', 'Bukti Autentik', 'Skor', 'Catatan'];
        $cols    = ['A','B','C','D','E','F','G'];
        foreach ($headers as $i => $h) {
            $sheet->setCellValue($cols[$i] . $r, $h);
        }
        $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => $fill::FILL_SOLID, 'startColor' => ['rgb' => '1e6347']],
            'alignment' => ['horizontal' => $aln::HORIZONTAL_CENTER, 'wrapText' => true],
        ]);
        $r++;

        $no = 1;
        $skorBg = ['1'=>'fef2f2','2'=>'fffbeb','3'=>'eff6ff','4'=>'ecfdf5'];

        foreach ($tree as $kData) {
            $k = $kData['komponen'];
            $pct = $kData['skor_maks'] > 0 ? round($kData['skor_perolehan']/$kData['skor_maks']*100) : 0;

            // Header komponen
            $sheet->mergeCells("A{$r}:G{$r}");
            $sheet->setCellValue("A{$r}",
                'KOMPONEN ' . $k['nomor_komponen'] . ': ' . strtoupper($k['nama_komponen']) .
                '   |   Skor: ' . $kData['skor_perolehan'] . '/' . $kData['skor_maks'] .
                '   Terisi: ' . $kData['terisi'] . '/' . $kData['total_ind'] . ' (' . $pct . '%)');
            $sheet->getStyle("A{$r}")->applyFromArray([
                'font' => ['bold' => true],
                'fill' => ['fillType' => $fill::FILL_SOLID, 'startColor' => ['rgb' => 'eef7f1']],
            ]);
            $r++;

            foreach ($kData['tugas'] as $tData) {
                $t = $tData['tugas'];
                $sheet->mergeCells("A{$r}:G{$r}");
                $sheet->setCellValue("A{$r}", 'Tugas ' . $t['kode_tugas'] . ': ' . $t['nama_tugas']);
                $sheet->getStyle("A{$r}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 9, 'italic' => true],
                    'fill' => ['fillType' => $fill::FILL_SOLID, 'startColor' => ['rgb' => 'f8fafc']],
                ]);
                $r++;

                foreach ($tData['indikator'] as $ind) {
                    $sheet->setCellValue("A{$r}", $no++);
                    $sheet->setCellValue("B{$r}", $ind['uraian_indikator']);
                    $sheet->setCellValue("C{$r}", $ind['kode_indikator']);
                    $sheet->setCellValue("D{$r}", $ind['data_kinerja_diharapkan']);
                    $sheet->setCellValue("E{$r}", $ind['bukti_autentik']);
                    $sheet->setCellValue("F{$r}", $ind['skor'] ?? '');
                    $sheet->setCellValue("G{$r}", $ind['catatan_penilai'] ?? '');

                    $sheet->getStyle("A{$r}:G{$r}")->getAlignment()->setWrapText(true);
                    $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal($aln::HORIZONTAL_CENTER);
                    $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal($aln::HORIZONTAL_CENTER);
                    $sheet->getStyle("A{$r}:G{$r}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => $brd::BORDER_THIN, 'color' => ['rgb' => 'e2e8f0']]],
                    ]);

                    if ($ind['skor'] !== null) {
                        $bg = $skorBg[(string)$ind['skor']] ?? 'ffffff';
                        $sheet->getStyle("F{$r}")->applyFromArray([
                            'fill' => ['fillType' => $fill::FILL_SOLID, 'startColor' => ['rgb' => $bg]],
                            'font' => ['bold' => true],
                        ]);
                    }
                    $r++;
                }
            }
        }

        // Baris total
        foreach ([
            ['TOTAL SKOR PEROLEHAN / MAKSIMAL', $data['total_skor_perolehan'] . ' / ' . $data['total_skor_maks']],
            ['NILAI AKHIR (Konversi 100)',       $p['nilai_akhir'] !== null ? number_format($p['nilai_akhir'],2) : '—'],
            ['PREDIKAT',                         $p['predikat'] ?? '—'],
        ] as [$label, $val]) {
            $sheet->mergeCells("A{$r}:E{$r}");
            $sheet->setCellValue("A{$r}", $label);
            $sheet->mergeCells("F{$r}:G{$r}");
            $sheet->setCellValue("F{$r}", $val);
            $sheet->getStyle("A{$r}:G{$r}")->getFont()->setBold(true);
            $r++;
        }

        // Lebar kolom
        foreach (['A'=>5,'B'=>40,'C'=>14,'D'=>35,'E'=>30,'F'=>8,'G'=>25] as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        $filename = 'FormPenilaian_' . $p['kode_kelompok'] . '_' . preg_replace('/[^A-Za-z0-9_-]/', '_', $p['nama_madrasah']) . '_' . $p['tahun_penilaian'] . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ── Helper: ambil data lengkap satu penilaian ────────────────
    private function getPenilaianData(\PDO $pdo, int $id): ?array {
        $stmt = $pdo->prepare("
            SELECT pen.*, pp.periode_id, pp.kelompok_id,
                   kp.kode_kelompok, kp.nama_kelompok, kp.bobot_default,
                   per.nama_periode, per.tahun_penilaian, per.jenis_penilaian,
                   m.nama_madrasah, km.nama_lengkap as nama_kamad,
                   u.nama_lengkap as nama_penilai
            FROM penilaian pen
            JOIN periode_penilai pp   ON pp.id  = pen.periode_penilai_id
            JOIN kelompok_penilai kp  ON kp.id  = pp.kelompok_id
            JOIN periode_penilaian per ON per.id = pen.periode_id
            JOIN madrasah m           ON m.id   = per.madrasah_id
            JOIN kepala_madrasah km   ON km.id  = per.kamad_id
            JOIN users u              ON u.id   = pen.user_id
            WHERE pen.id = ?
        ");
        $stmt->execute([$id]);
        $penilaian = $stmt->fetch();
        if (!$penilaian) return null;

        $isTahunan = ($penilaian['jenis_penilaian'] === 'tahunan');

        $kompStmt = $pdo->prepare("
            SELECT * FROM snapshot_komponen
            WHERE periode_id = ? " . ($isTahunan ? "AND nomor_komponen <= 4" : "") . "
            ORDER BY nomor_komponen ASC
        ");
        $kompStmt->execute([$penilaian['periode_id']]);

        $tree = [];
        $totalIndikator     = 0;
        $totalSkorPerolehan = 0;
        $totalSkorMaks      = 0;

        foreach ($kompStmt->fetchAll() as $k) {
            $tStmt = $pdo->prepare("SELECT * FROM snapshot_tugas_utama WHERE snapshot_komponen_id = ? ORDER BY urutan ASC");
            $tStmt->execute([$k['id']]);

            $tugasArr        = [];
            $kompPerolehan   = 0;
            $kompMaks        = 0;
            $kompTerisi      = 0;
            $kompTotal       = 0;

            foreach ($tStmt->fetchAll() as $t) {
                $iStmt = $pdo->prepare("
                    SELECT si.*, dp.skor, dp.catatan as catatan_penilai
                    FROM snapshot_indikator si
                    LEFT JOIN detail_penilaian dp
                           ON dp.snapshot_indikator_id = si.id AND dp.penilaian_id = ?
                    WHERE si.snapshot_tugas_id = ?
                    ORDER BY si.urutan ASC
                ");
                $iStmt->execute([$id, $t['id']]);
                $indList = $iStmt->fetchAll();

                foreach ($indList as $ind) {
                    $kompMaks += 4;
                    $kompTotal++;
                    if ($ind['skor'] !== null) {
                        $kompPerolehan += (int)$ind['skor'];
                        $kompTerisi++;
                    }
                }
                $tugasArr[] = ['tugas' => $t, 'indikator' => $indList];
            }

            $totalSkorPerolehan += $kompPerolehan;
            $totalSkorMaks      += $kompMaks;
            $totalIndikator     += $kompTotal;

            $tree[] = [
                'komponen'       => $k,
                'tugas'          => $tugasArr,
                'skor_perolehan' => $kompPerolehan,
                'skor_maks'      => $kompMaks,
                'terisi'         => $kompTerisi,
                'total_ind'      => $kompTotal,
            ];
        }

        $cStmt = $pdo->prepare("SELECT COUNT(*) FROM detail_penilaian WHERE penilaian_id = ? AND skor IS NOT NULL");
        $cStmt->execute([$id]);
        $terisiCount = (int)$cStmt->fetchColumn();

        return [
            'penilaian'            => $penilaian,
            'tree'                 => $tree,
            'total_indikator'      => $totalIndikator,
            'terisi_count'         => $terisiCount,
            'total_skor_perolehan' => $totalSkorPerolehan,
            'total_skor_maks'      => $totalSkorMaks,
        ];
    }
}

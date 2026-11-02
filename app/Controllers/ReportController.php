<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Services\CalculationService;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PDO;

class ReportController {
    public function index() {
        $pdo = getDbConnection();
        $periodeId = (int)($_GET['periode_id'] ?? 0);

        if (!$periodeId) {
            $pStmt = $pdo->query("SELECT id FROM periode_penilaian ORDER BY id DESC LIMIT 1");
            $periodeId = (int)$pStmt->fetchColumn();
        }

        $calc = new CalculationService($pdo);
        $rekap = $calc->calculatePeriodeRekap($periodeId);

        $pStmt = $pdo->prepare("
            SELECT p.*, m.*, km.nama_lengkap as nama_kamad, km.nip as kamad_nip, km.npk as kamad_npk, km.nuptk as kamad_nuptk, km.pangkat_golongan as kamad_gol
            FROM periode_penilaian p
            JOIN madrasah m ON p.madrasah_id = m.id
            JOIN kepala_madrasah km ON p.kamad_id = km.id
            WHERE p.id = ?
        ");
        $pStmt->execute([$periodeId]);
        $periode = $pStmt->fetch();

        view('report.index', [
            'title' => 'Laporan & Rekapitulasi PKKM',
            'periode' => $periode,
            'rekap' => $rekap
        ]);
    }

    public function exportPdf(int $periodeId) {
        $pdo = getDbConnection();
        $calc = new CalculationService($pdo);
        $rekap = $calc->calculatePeriodeRekap($periodeId);

        $pStmt = $pdo->prepare("
            SELECT p.*, m.*, km.nama_lengkap as nama_kamad, km.nip as kamad_nip, km.npk as kamad_npk, km.nuptk as kamad_nuptk, km.pangkat_golongan as kamad_gol
            FROM periode_penilaian p
            JOIN madrasah m ON p.madrasah_id = m.id
            JOIN kepala_madrasah km ON p.kamad_id = km.id
            WHERE p.id = ?
        ");
        $pStmt->execute([$periodeId]);
        $periode = $pStmt->fetch();

        ob_start();
        require __DIR__ . '/../../views/report/pdf_template.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("Laporan_PKKM_{$periode['nama_madrasah']}_{$periode['tahun_penilaian']}.pdf", [
            "Attachment" => false
        ]);
        exit;
    }

    public function exportExcel(int $periodeId) {
        $pdo = getDbConnection();
        $calc = new CalculationService($pdo);
        $rekap = $calc->calculatePeriodeRekap($periodeId);

        $pStmt = $pdo->prepare("
            SELECT p.*, m.*, km.nama_lengkap as nama_kamad, km.nip as kamad_nip, km.npk as kamad_npk, km.nuptk as kamad_nuptk
            FROM periode_penilaian p
            JOIN madrasah m ON p.madrasah_id = m.id
            JOIN kepala_madrasah km ON p.kamad_id = km.id
            WHERE p.id = ?
        ");
        $pStmt->execute([$periodeId]);
        $periode = $pStmt->fetch();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Penilai');

        // Header
        $sheet->setCellValue('A1', 'REKAPITULASI PENILAIAN KINERJA EMPAT TAHUNAN KEPALA MADRASAH');
        $sheet->setCellValue('A3', 'Nama Madrasah: ' . $periode['nama_madrasah']);
        $sheet->setCellValue('A4', 'Nama Kepala Madrasah: ' . $periode['nama_kamad']);
        $sheet->setCellValue('A5', 'Tahun Penilaian: ' . $periode['tahun_penilaian']);

        $sheet->setCellValue('A7', 'No');
        $sheet->setCellValue('B7', 'Kelompok Penilai');
        $sheet->setCellValue('C7', 'Bobot');
        $sheet->setCellValue('D7', 'Skor Rata-Rata');
        $sheet->setCellValue('E7', 'Hasil Pembobotan');

        $groups = [
            ['no' => 1, 'kode' => 'KS', 'nama' => 'Hasil Penilaian Kepala Seksi', 'bobot' => 4],
            ['no' => 2, 'kode' => 'KY', 'nama' => 'Hasil Penilaian Ketua Yayasan', 'bobot' => 2],
            ['no' => 3, 'kode' => 'WASMAD', 'nama' => 'Hasil Rata-Rata Penilaian Pengawas', 'bobot' => 1],
            ['no' => 4, 'kode' => 'GURU', 'nama' => 'Hasil Rata-Rata Penilaian Guru', 'bobot' => 1],
            ['no' => 5, 'kode' => 'TENDIK', 'nama' => 'Hasil Rata-Rata Penilaian Tenaga Kependidikan', 'bobot' => 1],
            ['no' => 6, 'kode' => 'KOMITE', 'nama' => 'Hasil Rata-Rata Penilaian Komite', 'bobot' => 1],
        ];

        $r = 8;
        foreach ($groups as $g) {
            $avg = $rekap['groups'][$g['kode']]['rata_rata'] ?? 0;
            $weighted = round(($avg * $g['bobot']) / 10.0, 2);
            $sheet->setCellValue("A$r", $g['no']);
            $sheet->setCellValue("B$r", $g['nama']);
            $sheet->setCellValue("C$r", $g['bobot']);
            $sheet->setCellValue("D$r", $avg);
            $sheet->setCellValue("E$r", $weighted);
            $r++;
        }

        $sheet->setCellValue("B$r", 'NILAI KINERJA TAHUN KE-4 (NKTE)');
        $sheet->setCellValue("E$r", $rekap['nkte']);
        $r++;
        $sheet->setCellValue("B$r", 'PREDIKAT KINERJA');
        $sheet->setCellValue("E$r", $rekap['predikat']);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"Rekap_PKKM_{$periode['tahun_penilaian']}.xlsx\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}

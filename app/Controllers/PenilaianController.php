<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Services\CalculationService;
use PDO;

class PenilaianController {
    public function index() {
        $pdo = getDbConnection();
        $userId = Auth::id();

        // Get all assessments for this penilai or all if admin
        $sql = "
            SELECT p.*, pp.periode_id, pp.kelompok_id, kp.kode_kelompok, kp.nama_kelompok, kp.bobot_default,
                   per.nama_periode, per.tahun_penilaian, per.jenis_penilaian, per.status as periode_status,
                   m.nama_madrasah, km.nama_lengkap as nama_kamad,
                   (
                       SELECT COUNT(*) FROM snapshot_indikator si 
                       WHERE si.periode_id = per.id
                   ) as total_indikator,
                   (
                       SELECT COUNT(*) FROM detail_penilaian dp 
                       WHERE dp.penilaian_id = p.id AND dp.skor IS NOT NULL
                   ) as terisi_indikator
            FROM penilaian p
            JOIN periode_penilai pp ON p.periode_penilai_id = pp.id
            JOIN kelompok_penilai kp ON pp.kelompok_id = kp.id
            JOIN periode_penilaian per ON p.periode_id = per.id
            JOIN madrasah m ON per.madrasah_id = m.id
            JOIN kepala_madrasah km ON per.kamad_id = km.id
        ";

        if (!Auth::hasRole('admin')) {
            $sql .= " WHERE p.user_id = ? ORDER BY per.id DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$userId]);
        } else {
            $sql .= " ORDER BY per.id DESC, kp.id ASC";
            $stmt = $pdo->query($sql);
        }

        $list = $stmt->fetchAll();

        view('penilaian.index', [
            'title' => 'Form Penilaian Kinerja',
            'list'  => $list,
        ]);
    }

    public function form(int $penilaianId) {
        $pdo = getDbConnection();
        $userId = Auth::id();

        // Ensure user is owner of this penilaian or admin
        $stmt = $pdo->prepare("
            SELECT p.*, pp.periode_id, pp.kelompok_id, kp.kode_kelompok, kp.nama_kelompok,
                   per.nama_periode, per.tahun_penilaian, per.jenis_penilaian,
                   m.nama_madrasah, km.nama_lengkap as nama_kamad
            FROM penilaian p
            JOIN periode_penilai pp ON p.periode_penilai_id = pp.id
            JOIN kelompok_penilai kp ON pp.kelompok_id = kp.id
            JOIN periode_penilaian per ON p.periode_id = per.id
            JOIN madrasah m ON per.madrasah_id = m.id
            JOIN kepala_madrasah km ON per.kamad_id = km.id
            WHERE p.id = ?
        ");
        $stmt->execute([$penilaianId]);
        $penilaian = $stmt->fetch();

        if (!$penilaian) {
            http_response_code(404);
            echo "Penilaian tidak ditemukan.";
            return;
        }

        if (!Auth::hasRole('admin') && $penilaian['user_id'] != $userId) {
            http_response_code(403);
            echo "Akses ditolak.";
            return;
        }

        $isTahunan = ($penilaian['jenis_penilaian'] === 'tahunan');

        // Fetch Snapshot Komponen, Tugas Utama, and Indikator
        $kompStmt = $pdo->prepare("
            SELECT * FROM snapshot_komponen 
            WHERE periode_id = ? " . ($isTahunan ? "AND nomor_komponen <= 4" : "") . "
            ORDER BY nomor_komponen ASC
        ");
        $kompStmt->execute([$penilaian['periode_id']]);
        $komponenList = $kompStmt->fetchAll();

        $tree = [];
        $totalIndikator = 0;

        foreach ($komponenList as $k) {
            $tStmt = $pdo->prepare("SELECT * FROM snapshot_tugas_utama WHERE snapshot_komponen_id = ? ORDER BY urutan ASC");
            $tStmt->execute([$k['id']]);
            $tugasList = $tStmt->fetchAll();

            $tugasArr = [];
            foreach ($tugasList as $t) {
                $iStmt = $pdo->prepare("
                    SELECT si.*, dp.skor, dp.catatan as catatan_penilai,
                           (SELECT COUNT(*) FROM bukti_dukung bd WHERE bd.snapshot_indikator_id = si.id) as total_bukti
                    FROM snapshot_indikator si
                    LEFT JOIN detail_penilaian dp ON dp.snapshot_indikator_id = si.id AND dp.penilaian_id = ?
                    WHERE si.snapshot_tugas_id = ?
                    ORDER BY si.urutan ASC
                ");
                $iStmt->execute([$penilaianId, $t['id']]);
                $indList = $iStmt->fetchAll();
                $totalIndikator += count($indList);

                $tugasArr[] = [
                    'tugas' => $t,
                    'indikator' => $indList
                ];
            }

            $tree[] = [
                'komponen' => $k,
                'tugas' => $tugasArr
            ];
        }

        // Count answered
        $cStmt = $pdo->prepare("SELECT COUNT(*) FROM detail_penilaian WHERE penilaian_id = ? AND skor IS NOT NULL");
        $cStmt->execute([$penilaianId]);
        $terisiCount = $cStmt->fetchColumn();

        view('penilaian.form', [
            'title' => 'Formulir Penilaian - ' . $penilaian['nama_kelompok'],
            'penilaian' => $penilaian,
            'tree' => $tree,
            'total_indikator' => $totalIndikator,
            'terisi_count' => $terisiCount
        ]);
    }

    public function saveScore() {
        $penilaianId = (int)($_POST['penilaian_id'] ?? 0);
        $indikatorId = (int)($_POST['indikator_id'] ?? 0);
        $skor = (int)($_POST['skor'] ?? 0);
        $catatan = trim($_POST['catatan'] ?? '');

        if ($skor < 1 || $skor > 4) {
            json_response(['success' => false, 'message' => 'Skor harus bernilai 1 sampai 4!'], 422);
        }

        $pdo = getDbConnection();
        $pStmt = $pdo->prepare("SELECT * FROM penilaian WHERE id = ?");
        $pStmt->execute([$penilaianId]);
        $penilaian = $pStmt->fetch();

        if (!$penilaian) {
            json_response(['success' => false, 'message' => 'Data penilaian tidak ditemukan!'], 404);
        }

        if ($penilaian['status'] === 'submitted' || $penilaian['status'] === 'locked') {
            json_response(['success' => false, 'message' => 'Penilaian sudah dikunci dan tidak dapat diubah!'], 403);
        }

        if (!Auth::hasRole('admin') && $penilaian['user_id'] != Auth::id()) {
            json_response(['success' => false, 'message' => 'Akses ditolak!'], 403);
        }

        // Upsert detail_penilaian
        $chk = $pdo->prepare("SELECT id FROM detail_penilaian WHERE penilaian_id = ? AND snapshot_indikator_id = ?");
        $chk->execute([$penilaianId, $indikatorId]);
        $detailId = $chk->fetchColumn();

        if ($detailId) {
            $up = $pdo->prepare("UPDATE detail_penilaian SET skor = ?, catatan = ?, updated_at = NOW() WHERE id = ?");
            $up->execute([$skor, $catatan, $detailId]);
        } else {
            $ins = $pdo->prepare("INSERT INTO detail_penilaian (penilaian_id, snapshot_indikator_id, skor, catatan) VALUES (?, ?, ?, ?)");
            $ins->execute([$penilaianId, $indikatorId, $skor, $catatan]);
        }

        // Re-calculate individual score & stats
        $calcService = new CalculationService($pdo);
        $calcResult = $calcService->calculateIndividualScore($penilaianId);

        $answered = $pdo->prepare("SELECT COUNT(*) FROM detail_penilaian WHERE penilaian_id = ? AND skor IS NOT NULL");
        $answered->execute([$penilaianId]);
        $answeredCount = $answered->fetchColumn();

        json_response([
            'success' => true,
            'message' => 'Skor berhasil disimpan.',
            'answered_count' => $answeredCount,
            'total_skor' => $calcResult['total_skor'],
            'nilai_akhir' => $calcResult['nilai_akhir'],
            'predikat' => $calcResult['predikat']
        ]);
    }

    public function submit() {
        $penilaianId = (int)($_POST['penilaian_id'] ?? 0);
        $pdo = getDbConnection();

        $stmt = $pdo->prepare("
            SELECT p.*, per.jenis_penilaian, per.id as per_id
            FROM penilaian p 
            JOIN periode_penilaian per ON p.periode_id = per.id 
            WHERE p.id = ?
        ");
        $stmt->execute([$penilaianId]);
        $penilaian = $stmt->fetch();

        if (!$penilaian) {
            json_response(['success' => false, 'message' => 'Penilaian tidak ditemukan!'], 404);
        }

        if (!Auth::hasRole('admin') && $penilaian['user_id'] != Auth::id()) {
            json_response(['success' => false, 'message' => 'Akses ditolak!'], 403);
        }

        $isTahunan = ($penilaian['jenis_penilaian'] === 'tahunan');
        $expectedCount = $isTahunan ? 95 : 108;

        $cStmt = $pdo->prepare("SELECT COUNT(*) FROM detail_penilaian WHERE penilaian_id = ? AND skor IS NOT NULL");
        $cStmt->execute([$penilaianId]);
        $actualCount = $cStmt->fetchColumn();

        if ($actualCount < $expectedCount) {
            $sisa = $expectedCount - $actualCount;
            json_response([
                'success' => false, 
                'message' => "Penilaian belum lengkap! Masih ada {$sisa} indikator yang belum dinilai."
            ], 422);
        }

        // Lock & submit
        $up = $pdo->prepare("UPDATE penilaian SET status = 'submitted', submitted_at = NOW() WHERE id = ?");
        $up->execute([$penilaianId]);

        $upPp = $pdo->prepare("UPDATE periode_penilai SET status_penilaian = 'selesai', submitted_at = NOW() WHERE id = ?");
        $upPp->execute([$penilaian['periode_penilai_id']]);

        // Calculate Final
        $calcService = new CalculationService($pdo);
        $calcService->calculateIndividualScore($penilaianId);
        $calcService->calculatePeriodeRekap($penilaian['per_id']);

        audit_log('SUBMIT_PENILAIAN', 'penilaian', "Penilaian #{$penilaianId} disubmit oleh " . Auth::user()['username']);

        json_response([
            'success' => true,
            'message' => 'Penilaian berhasil disubmit dan dikunci!',
            'redirect' => base_url('penilai/dashboard')
        ]);
    }

    public function unlock() {
        if (!Auth::hasRole('admin')) {
            json_response(['success' => false, 'message' => 'Hanya administrator yang dapat membuka kunci penilaian!'], 403);
        }

        $penilaianId = (int)($_POST['penilaian_id'] ?? 0);
        $alasan = trim($_POST['alasan'] ?? '');

        if (empty($alasan)) {
            json_response(['success' => false, 'message' => 'Alasan pembukaan kunci wajib diisi!'], 422);
        }

        $pdo = getDbConnection();
        $pStmt = $pdo->prepare("SELECT * FROM penilaian WHERE id = ?");
        $pStmt->execute([$penilaianId]);
        $penilaian = $pStmt->fetch();

        if (!$penilaian) {
            json_response(['success' => false, 'message' => 'Penilaian tidak ditemukan!'], 404);
        }

        $up = $pdo->prepare("UPDATE penilaian SET status = 'draft' WHERE id = ?");
        $up->execute([$penilaianId]);

        $upPp = $pdo->prepare("UPDATE periode_penilai SET status_penilaian = 'draft' WHERE id = ?");
        $upPp->execute([$penilaian['periode_penilai_id']]);

        audit_log('UNLOCK_PENILAIAN', 'unlock', "Penilaian #{$penilaianId} dibuka kuncinya oleh admin " . Auth::user()['username'] . ". Alasan: {$alasan}");

        json_response([
            'success' => true,
            'message' => 'Kunci penilaian berhasil dibuka kembali.'
        ]);
    }
}

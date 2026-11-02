<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Services\CalculationService;
use PDO;

class DashboardController {
    public function index() {
        if (Auth::hasRole('admin')) {
            $this->adminDashboard();
        } elseif (Auth::hasRole('kamad')) {
            $this->kamadDashboard();
        } else {
            $this->penilaiDashboard();
        }
    }

    public function adminDashboard() {
        $pdo = getDbConnection();
        $calcService = new CalculationService($pdo);

        // Active period
        $pStmt = $pdo->query("
            SELECT p.*, m.nama_madrasah, km.nama_lengkap as nama_kamad, v.nomor_versi
            FROM periode_penilaian p
            JOIN madrasah m ON p.madrasah_id = m.id
            JOIN kepala_madrasah km ON p.kamad_id = km.id
            JOIN instrumen_versi v ON p.instrumen_versi_id = v.id
            ORDER BY p.id DESC LIMIT 1
        ");
        $activePeriode = $pStmt->fetch();

        $stats = [
            'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'total_periode' => $pdo->query("SELECT COUNT(*) FROM periode_penilaian")->fetchColumn(),
            'total_penilai' => $pdo->query("SELECT COUNT(*) FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE r.kode_role = 'penilai'")->fetchColumn(),
        ];

        $rekapData = null;
        if ($activePeriode) {
            $rekapData = $calcService->calculatePeriodeRekap($activePeriode['id']);
        }

        view('dashboard.admin', [
            'title' => 'Dashboard Administrator',
            'periode' => $activePeriode,
            'stats' => $stats,
            'rekap' => $rekapData
        ]);
    }

    public function kamadDashboard() {
        $pdo = getDbConnection();
        $calcService = new CalculationService($pdo);

        $userId = Auth::id();
        $kmStmt = $pdo->prepare("SELECT * FROM kepala_madrasah WHERE user_id = ?");
        $kmStmt->execute([$userId]);
        $kamad = $kmStmt->fetch();

        $periode = null;
        $rekap = null;
        $isPublished = false;

        if ($kamad) {
            $pStmt = $pdo->prepare("
                SELECT p.*, m.nama_madrasah, v.nomor_versi
                FROM periode_penilaian p
                JOIN madrasah m ON p.madrasah_id = m.id
                JOIN instrumen_versi v ON p.instrumen_versi_id = v.id
                WHERE p.kamad_id = ?
                ORDER BY p.id DESC LIMIT 1
            ");
            $pStmt->execute([$kamad['id']]);
            $periode = $pStmt->fetch();

            if ($periode) {
                // Check publikasi
                $pubStmt = $pdo->prepare("SELECT is_published FROM publikasi_hasil WHERE periode_id = ?");
                $pubStmt->execute([$periode['id']]);
                $isPublished = (bool)$pubStmt->fetchColumn();

                $rekap = $calcService->calculatePeriodeRekap($periode['id']);
            }
        }

        view('dashboard.kamad', [
            'title' => 'Dashboard Kepala Madrasah',
            'kamad' => $kamad,
            'periode' => $periode,
            'rekap' => $rekap,
            'is_published' => $isPublished
        ]);
    }

    public function penilaiDashboard() {
        $pdo = getDbConnection();
        $userId = Auth::id();

        // Get penugasan aktif
        $stmt = $pdo->prepare("
            SELECT pp.*, p.nama_periode, p.tahun_penilaian, p.jenis_penilaian, p.status as periode_status,
                   kp.nama_kelompok, kp.kode_kelompok, m.nama_madrasah, km.nama_lengkap as nama_kamad,
                   pen.id as penilaian_id, pen.status as status_penilaian, pen.nilai_akhir, pen.predikat
            FROM periode_penilai pp
            JOIN periode_penilaian p ON pp.periode_id = p.id
            JOIN kelompok_penilai kp ON pp.kelompok_id = kp.id
            JOIN madrasah m ON p.madrasah_id = m.id
            JOIN kepala_madrasah km ON p.kamad_id = km.id
            LEFT JOIN penilaian pen ON pen.periode_penilai_id = pp.id
            WHERE pp.user_id = ?
            ORDER BY p.id DESC
        ");
        $stmt->execute([$userId]);
        $assignments = $stmt->fetchAll();

        view('dashboard.penilai', [
            'title' => 'Dashboard Penilai',
            'assignments' => $assignments
        ]);
    }
}

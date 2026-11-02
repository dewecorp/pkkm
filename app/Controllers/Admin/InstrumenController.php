<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use PDO;

class InstrumenController {
    public function index() {
        $pdo = getDbConnection();

        $versiList = $pdo->query("SELECT * FROM instrumen_versi ORDER BY id DESC")->fetchAll();
        $versiId = (int)($_GET['versi_id'] ?? ($versiList[0]['id'] ?? 1));

        // Get 5 components with count of tasks & indicators
        $stmtKomp = $pdo->prepare("
            SELECT kp.*, 
                   COUNT(DISTINCT tu.id) as total_tugas,
                   COUNT(DISTINCT ik.id) as total_indikator
            FROM komponen_penilaian kp
            LEFT JOIN tugas_utama tu ON tu.komponen_id = kp.id
            LEFT JOIN indikator_kerja ik ON ik.tugas_id = tu.id
            WHERE kp.versi_id = ?
            GROUP BY kp.id
            ORDER BY kp.nomor_komponen ASC
        ");
        $stmtKomp->execute([$versiId]);
        $komponenList = $stmtKomp->fetchAll();

        $activeKompId = (int)($_GET['komponen_id'] ?? ($komponenList[0]['id'] ?? 0));

        // Get tugas utama for the active component
        $stmtTugas = $pdo->prepare("SELECT * FROM tugas_utama WHERE komponen_id = ? ORDER BY urutan ASC");
        $stmtTugas->execute([$activeKompId]);
        $tugasList = $stmtTugas->fetchAll();

        // Get all indicators grouped by tugas_id
        $stmtIndikator = $pdo->prepare("
            SELECT ik.*, tu.nama_tugas, tu.kode_tugas
            FROM indikator_kerja ik
            JOIN tugas_utama tu ON ik.tugas_id = tu.id
            WHERE tu.komponen_id = ?
            ORDER BY tu.urutan ASC, ik.urutan ASC
        ");
        $stmtIndikator->execute([$activeKompId]);
        $allIndikator = $stmtIndikator->fetchAll();

        $indikatorByTugas = [];
        foreach ($allIndikator as $ind) {
            $indikatorByTugas[$ind['tugas_id']][] = $ind;
        }

        // Summary stats
        $totalIndikator = $pdo->prepare("
            SELECT COUNT(*) 
            FROM indikator_kerja ik 
            JOIN tugas_utama tu ON ik.tugas_id = tu.id 
            JOIN komponen_penilaian kp ON tu.komponen_id = kp.id 
            WHERE kp.versi_id = ?
        ");
        $totalIndikator->execute([$versiId]);
        $grandTotal = $totalIndikator->fetchColumn();

        view('admin.instrumen.index', [
            'title'            => 'Master Instrumen Penilaian',
            'versiList'        => $versiList,
            'currentVersiId'   => $versiId,
            'komponenList'     => $komponenList,
            'activeKompId'     => $activeKompId,
            'tugasList'        => $tugasList,
            'indikatorByTugas' => $indikatorByTugas,
            'grandTotal'       => $grandTotal,
        ]);
    }

    public function updateIndikator() {
        $d = $_POST;
        $id = (int)($d['id'] ?? 0);
        $pdo = getDbConnection();

        if (empty($d['uraian_indikator'])) {
            json_response(['success' => false, 'message' => 'Uraian indikator wajib diisi!'], 422);
        }

        $stmt = $pdo->prepare("
            UPDATE indikator_kerja SET
                uraian_indikator = ?,
                data_kinerja_diharapkan = ?,
                bukti_autentik = ?,
                skor_maksimal = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $d['uraian_indikator'],
            $d['data_kinerja_diharapkan'] ?? '',
            $d['bukti_autentik'] ?? '',
            $d['skor_maksimal'] ?? 4,
            $id
        ]);

        audit_log('UPDATE_INDIKATOR', 'instrumen', "Indikator #{$id} diperbarui oleh " . Auth::user()['username']);
        json_response(['success' => true, 'message' => 'Indikator berhasil diperbarui!']);
    }

    public function storeIndikator() {
        $d = $_POST;
        $pdo = getDbConnection();

        if (empty($d['tugas_id']) || empty($d['uraian_indikator']) || empty($d['kode_indikator'])) {
            json_response(['success' => false, 'message' => 'Kode, tugas utama, dan uraian indikator wajib diisi!'], 422);
        }

        $maxUrutan = (int)$pdo->prepare("SELECT MAX(urutan) FROM indikator_kerja WHERE tugas_id = ?")
            ->execute([$d['tugas_id']]);

        $stmt = $pdo->prepare("
            INSERT INTO indikator_kerja 
            (tugas_id, kode_indikator, nomor_sub, uraian_indikator, data_kinerja_diharapkan, bukti_autentik, skor_maksimal, urutan)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $d['tugas_id'],
            $d['kode_indikator'],
            $d['nomor_sub'] ?? 1,
            $d['uraian_indikator'],
            $d['data_kinerja_diharapkan'] ?? '',
            $d['bukti_autentik'] ?? '',
            $d['skor_maksimal'] ?? 4,
            $d['urutan'] ?? ($maxUrutan + 1)
        ]);

        audit_log('TAMBAH_INDIKATOR', 'instrumen', "Indikator baru {$d['kode_indikator']} ditambahkan oleh " . Auth::user()['username']);
        json_response(['success' => true, 'message' => 'Indikator baru berhasil ditambahkan!']);
    }

    public function destroyIndikator() {
        $id = (int)($_POST['id'] ?? 0);
        $pdo = getDbConnection();

        $pdo->prepare("DELETE FROM indikator_kerja WHERE id = ?")->execute([$id]);
        audit_log('HAPUS_INDIKATOR', 'instrumen', "Indikator #{$id} dihapus oleh " . Auth::user()['username']);
        json_response(['success' => true, 'message' => 'Indikator berhasil dihapus.']);
    }
}

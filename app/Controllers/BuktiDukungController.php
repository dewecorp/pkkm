<?php
namespace App\Controllers;

use App\Core\Auth;
use PDO;

class BuktiDukungController {
    public function index() {
        $pdo = getDbConnection();

        // Periode list
        $periodeList = $pdo->query("SELECT id, nama_periode, tahun_penilaian, status FROM periode_penilaian ORDER BY id DESC")->fetchAll();
        $periodeId = (int)($_GET['periode_id'] ?? ($periodeList[0]['id'] ?? 0));

        // Get snapshot components for this period with count of indicators & count of uploaded files
        $kompStmt = $pdo->prepare("
            SELECT sk.*,
                   COUNT(DISTINCT si.id) as total_indikator,
                   COUNT(DISTINCT bd.id) as total_bukti
            FROM snapshot_komponen sk
            LEFT JOIN snapshot_tugas_utama stu ON stu.snapshot_komponen_id = sk.id
            LEFT JOIN snapshot_indikator si ON si.snapshot_tugas_id = stu.id
            LEFT JOIN bukti_dukung bd ON bd.snapshot_indikator_id = si.id
            WHERE sk.periode_id = ?
            GROUP BY sk.id
            ORDER BY sk.nomor_komponen ASC
        ");
        $kompStmt->execute([$periodeId]);
        $komponenList = $kompStmt->fetchAll();

        $activeKompId = (int)($_GET['komponen_id'] ?? ($komponenList[0]['id'] ?? 0));

        // Tasks for active component
        $tugasStmt = $pdo->prepare("
            SELECT * FROM snapshot_tugas_utama
            WHERE snapshot_komponen_id = ?
            ORDER BY urutan ASC
        ");
        $tugasStmt->execute([$activeKompId]);
        $tugasList = $tugasStmt->fetchAll();

        // Indicators for active component
        $indStmt = $pdo->prepare("
            SELECT si.*, stu.kode_tugas, stu.nama_tugas
            FROM snapshot_indikator si
            JOIN snapshot_tugas_utama stu ON si.snapshot_tugas_id = stu.id
            WHERE stu.snapshot_komponen_id = ?
            ORDER BY stu.urutan ASC, si.urutan ASC
        ");
        $indStmt->execute([$activeKompId]);
        $indikatorRows = $indStmt->fetchAll();

        // Get all bukti dukung for this period and group by snapshot_indikator_id
        $buktiStmt = $pdo->prepare("
            SELECT bd.*, u.nama_lengkap as pengunggah
            FROM bukti_dukung bd
            JOIN users u ON bd.uploaded_by = u.id
            WHERE bd.periode_id = ?
            ORDER BY bd.id DESC
        ");
        $buktiStmt->execute([$periodeId]);
        $allBukti = $buktiStmt->fetchAll();

        $buktiByIndikator = [];
        foreach ($allBukti as $b) {
            if ($b['snapshot_indikator_id']) {
                $buktiByIndikator[$b['snapshot_indikator_id']][] = $b;
            }
        }

        // Group indicators by snapshot_tugas_id
        $indikatorByTugas = [];
        foreach ($indikatorRows as $row) {
            $row['files'] = $buktiByIndikator[$row['id']] ?? [];
            $indikatorByTugas[$row['snapshot_tugas_id']][] = $row;
        }

        $totalUploaded = count($allBukti);

        view('bukti.index', [
            'title'            => 'Bukti Dukung Kinerja',
            'periodeList'      => $periodeList,
            'currentPeriodeId' => $periodeId,
            'komponenList'     => $komponenList,
            'activeKompId'     => $activeKompId,
            'tugasList'        => $tugasList,
            'indikatorByTugas' => $indikatorByTugas,
            'totalUploaded'    => $totalUploaded,
        ]);
    }

    public function upload() {
        if (!Auth::hasAnyRole(['admin', 'kamad'])) {
            json_response(['success' => false, 'message' => 'Hanya Kamad dan Admin yang dapat mengunggah bukti dukung!'], 403);
        }

        $periodeId = (int)($_POST['periode_id'] ?? 0);
        $indikatorId = !empty($_POST['indikator_id']) ? (int)$_POST['indikator_id'] : null;
        $namaDokumen = trim($_POST['nama_dokumen'] ?? '');
        $kategori = trim($_POST['kategori'] ?? 'Umum');
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if (empty($namaDokumen) || !isset($_FILES['file'])) {
            json_response(['success' => false, 'message' => 'Nama dokumen dan file wajib diisi!'], 422);
        }

        $file = $_FILES['file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            json_response(['success' => false, 'message' => 'Gagal mengunggah file!'], 400);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowedExts)) {
            json_response(['success' => false, 'message' => 'Format file tidak diizinkan! (Hanya PDF, DOC, DOCX, XLS, XLSX, JPG, PNG)'], 422);
        }

        if ($file['size'] > 10 * 1024 * 1024) { // 10MB
            json_response(['success' => false, 'message' => 'Ukuran file melebihi batas 10MB!'], 422);
        }

        $uploadDir = __DIR__ . '/../../public/uploads/bukti/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $newFileName = 'bukti_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $uploadDir . $newFileName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            json_response(['success' => false, 'message' => 'Gagal memindahkan file ke direktori unggahan!'], 500);
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            INSERT INTO bukti_dukung 
                (periode_id, snapshot_indikator_id, nama_dokumen, kategori, deskripsi, file_path, file_name, file_size, file_ext, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $periodeId,
            $indikatorId,
            $namaDokumen,
            $kategori,
            $deskripsi,
            'uploads/bukti/' . $newFileName,
            $file['name'],
            $file['size'],
            $ext,
            Auth::id()
        ]);

        audit_log('UNGGAH_BUKTI', 'bukti', "Bukti dukung '{$namaDokumen}' diunggah untuk indikator #{$indikatorId} oleh " . Auth::user()['username']);

        json_response(['success' => true, 'message' => 'Bukti dukung berhasil diunggah!']);
    }

    public function delete($id) {
        if (!Auth::hasAnyRole(['admin', 'kamad'])) {
            json_response(['success' => false, 'message' => 'Hanya Kamad dan Admin yang berhak menghapus bukti!'], 403);
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT file_path, nama_dokumen FROM bukti_dukung WHERE id = ?");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();

        if ($row) {
            $filePath = __DIR__ . '/../../public/' . $row['file_path'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            $pdo->prepare("DELETE FROM bukti_dukung WHERE id = ?")->execute([(int)$id]);
            audit_log('HAPUS_BUKTI', 'bukti', "Bukti dukung '{$row['nama_dokumen']}' dihapus oleh " . Auth::user()['username']);

            json_response(['success' => true, 'message' => 'Bukti dukung berhasil dihapus!']);
        }

        json_response(['success' => false, 'message' => 'Berkas tidak ditemukan!'], 404);
    }
}

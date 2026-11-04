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

        $periodeId   = (int)($_POST['periode_id']   ?? 0);
        $indikatorId = !empty($_POST['indikator_id']) ? (int)$_POST['indikator_id'] : null;

        $namaDokumenList = $_POST['nama_dokumen'] ?? [];
        $kategoriList    = $_POST['kategori'] ?? [];
        $deskripsiList   = $_POST['deskripsi'] ?? [];

        // Parse items from multiple upload fields
        $items = [];
        if (!empty($_FILES['files']['name']) && is_array($_FILES['files']['name'])) {
            foreach ($_FILES['files']['name'] as $i => $filename) {
                if (empty($filename)) continue;
                $items[] = [
                    'nama_dokumen' => trim($namaDokumenList[$i] ?? ''),
                    'kategori'     => trim($kategoriList[$i] ?? 'Umum'),
                    'deskripsi'    => trim($deskripsiList[$i] ?? ''),
                    'name'         => $filename,
                    'tmp_name'     => $_FILES['files']['tmp_name'][$i],
                    'size'         => $_FILES['files']['size'][$i],
                    'error'        => $_FILES['files']['error'][$i],
                ];
            }
        } elseif (!empty($_FILES['file']['name'])) {
            // Fallback single file
            $items[] = [
                'nama_dokumen' => trim($_POST['nama_dokumen'] ?? ''),
                'kategori'     => trim($_POST['kategori'] ?? 'Umum'),
                'deskripsi'    => trim($_POST['deskripsi'] ?? ''),
                'name'         => $_FILES['file']['name'],
                'tmp_name'     => $_FILES['file']['tmp_name'],
                'size'         => $_FILES['file']['size'],
                'error'        => $_FILES['file']['error'],
            ];
        }

        if (empty($items)) {
            json_response(['success' => false, 'message' => 'Pilih minimal satu berkas pada baris upload!'], 422);
        }

        $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        $uploadDir   = __DIR__ . '/../../public/uploads/bukti/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $pdo      = getDbConnection();
        $uploaded = 0;
        $errors   = [];

        foreach ($items as $idx => $it) {
            $num = $idx + 1;
            if ($it['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Baris #{$num} ({$it['name']}): gagal diunggah";
                continue;
            }

            $ext = strtolower(pathinfo($it['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExts)) {
                $errors[] = "Baris #{$num} ({$it['name']}): format .{$ext} tidak diizinkan";
                continue;
            }

            if ($it['size'] > 10 * 1024 * 1024) {
                $errors[] = "Baris #{$num} ({$it['name']}): ukuran melebihi 10MB";
                continue;
            }

            $newFileName = 'bukti_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $targetPath  = $uploadDir . $newFileName;

            if (!move_uploaded_file($it['tmp_name'], $targetPath)) {
                $errors[] = "Baris #{$num} ({$it['name']}): gagal dipindahkan ke server";
                continue;
            }

            $namaFinal = !empty($it['nama_dokumen']) ? $it['nama_dokumen'] : pathinfo($it['name'], PATHINFO_FILENAME);

            $stmt = $pdo->prepare("
                INSERT INTO bukti_dukung 
                    (periode_id, snapshot_indikator_id, nama_dokumen, kategori, deskripsi, file_path, file_name, file_size, file_ext, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $periodeId,
                $indikatorId,
                $namaFinal,
                $it['kategori'],
                $it['deskripsi'],
                'uploads/bukti/' . $newFileName,
                $it['name'],
                $it['size'],
                $ext,
                Auth::id()
            ]);
            $uploaded++;
        }

        audit_log('UNGGAH_BUKTI', 'bukti', "{$uploaded} bukti diunggah untuk indikator #{$indikatorId} oleh " . Auth::user()['username']);

        if ($uploaded > 0) {
            $msg = "{$uploaded} berkas bukti dukung berhasil diunggah.";
            if (!empty($errors)) {
                $msg .= ' ' . implode('; ', $errors);
            }
            json_response(['success' => true, 'message' => $msg, 'uploaded' => $uploaded]);
        } else {
            json_response(['success' => false, 'message' => implode('; ', $errors)], 422);
        }
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
        json_response(['success' => false, 'message' => 'Data bukti tidak ditemukan!'], 404);
    }

    public function update($id) {
        if (!Auth::hasAnyRole(['admin', 'kamad'])) {
            json_response(['success' => false, 'message' => 'Hanya Kamad dan Admin yang berhak mengubah bukti!'], 403);
        }

        $id = (int)$id;
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM bukti_dukung WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if (!$row) {
            json_response(['success' => false, 'message' => 'Data bukti tidak ditemukan!'], 404);
        }

        $namaDokumen = trim($_POST['nama_dokumen'] ?? '');
        $kategori    = trim($_POST['kategori'] ?? 'Umum');
        $deskripsi   = trim($_POST['deskripsi'] ?? '');

        $filePath = $row['file_path'];
        $fileName = $row['file_name'];
        $fileSize = $row['file_size'];
        $fileExt  = $row['file_ext'];

        // If replacement file uploaded
        if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $allowedExts = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
            $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExts)) {
                json_response(['success' => false, 'message' => "Format berkas .{$ext} tidak diizinkan!"], 422);
            }

            if ($_FILES['file']['size'] > 10 * 1024 * 1024) {
                json_response(['success' => false, 'message' => 'Ukuran berkas melebihi 10MB!'], 422);
            }

            $uploadDir = __DIR__ . '/../../public/uploads/bukti/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newFileName = 'bukti_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $targetPath  = $uploadDir . $newFileName;

            if (!move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                json_response(['success' => false, 'message' => 'Gagal memindahkan berkas baru ke server!'], 500);
            }

            // Remove old file
            $oldPhysicalPath = __DIR__ . '/../../public/' . $row['file_path'];
            if (file_exists($oldPhysicalPath)) {
                @unlink($oldPhysicalPath);
            }

            $filePath = 'uploads/bukti/' . $newFileName;
            $fileName = $_FILES['file']['name'];
            $fileSize = $_FILES['file']['size'];
            $fileExt  = $ext;

            if (empty($namaDokumen)) {
                $namaDokumen = pathinfo($_FILES['file']['name'], PATHINFO_FILENAME);
            }
        }

        if (empty($namaDokumen)) {
            $namaDokumen = $row['nama_dokumen'];
        }

        $upStmt = $pdo->prepare("
            UPDATE bukti_dukung 
            SET nama_dokumen = ?, kategori = ?, deskripsi = ?, file_path = ?, file_name = ?, file_size = ?, file_ext = ?
            WHERE id = ?
        ");
        $upStmt->execute([
            $namaDokumen,
            $kategori,
            $deskripsi,
            $filePath,
            $fileName,
            $fileSize,
            $fileExt,
            $id
        ]);

        audit_log('UBAH_BUKTI', 'bukti', "Bukti dukung #{$id} ({$namaDokumen}) diperbarui oleh " . Auth::user()['username']);

        json_response([
            'success' => true,
            'message' => 'Bukti dukung berhasil diperbarui!',
            'data' => [
                'id'           => $id,
                'nama_dokumen' => $namaDokumen,
                'kategori'     => $kategori,
                'deskripsi'    => $deskripsi,
                'file_name'    => $fileName,
                'file_ext'     => $fileExt,
                'file_size'    => $fileSize
            ]
        ]);
    }

    public function previewPage($id) {
        if (!Auth::check()) {
            redirect('login');
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("
            SELECT bd.*, u.nama_lengkap as pengunggah
            FROM bukti_dukung bd
            JOIN users u ON bd.uploaded_by = u.id
            WHERE bd.id = ?
        ");
        $stmt->execute([(int)$id]);
        $bukti = $stmt->fetch();

        if (!$bukti) {
            http_response_code(404);
            exit('Berkas bukti dukung tidak ditemukan');
        }

        $fullPath = __DIR__ . '/../../public/' . $bukti['file_path'];
        if (!file_exists($fullPath)) {
            http_response_code(404);
            exit('File fisik tidak ditemukan di server: ' . htmlspecialchars($bukti['file_path']));
        }

        $ext = strtolower($bukti['file_ext'] ?? pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'txt'  => 'text/plain',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];
        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';

        // Direct in-memory Base64 encoding: zero secondary HTTP fetch, 100% immune to IDM intercept
        $fileBytes = file_get_contents($fullPath);
        $base64 = base64_encode($fileBytes);

        view('bukti.preview', [
            'bukti'  => $bukti,
            'base64' => $base64,
            'mime'   => $mime
        ]);
    }

    public function streamFile($id) {
        if (!Auth::check()) {
            http_response_code(401);
            exit('Silakan login terlebih dahulu');
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM bukti_dukung WHERE id = ?");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch();

        if (!$row) {
            http_response_code(404);
            exit('Berkas tidak ditemukan');
        }

        $fullPath = __DIR__ . '/../../public/' . $row['file_path'];
        if (!file_exists($fullPath)) {
            http_response_code(404);
            exit('File fisik tidak ditemukan di server: ' . htmlspecialchars($row['file_path']));
        }

        $ext = strtolower($row['file_ext'] ?? pathinfo($fullPath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf'  => 'application/pdf',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
            'txt'  => 'text/plain',
            'doc'  => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls'  => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ];

        $mime = $mimeTypes[$ext] ?? 'application/octet-stream';
        $safeName = preg_replace('/[^a-zA-Z0-9_\-\. ]/', '', $row['nama_dokumen'] . '.' . $ext);
        $isDownload = !empty($_GET['dl']);

        header('Content-Type: ' . $mime);
        header('Content-Disposition: ' . ($isDownload ? 'attachment' : 'inline') . '; filename="' . $safeName . '"');
        header('Content-Length: ' . filesize($fullPath));
        header('Cache-Control: private, max-age=86400, must-revalidate');
        header('Pragma: public');
        header('X-Content-Type-Options: nosniff');

        readfile($fullPath);
        exit;
    }

    public function viewFile($id) {
        $this->previewPage($id);
    }
}

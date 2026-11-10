<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use PDO;

class PengaturanController {
    public function index() {
        $pdo = getDbConnection();
        $settings = $pdo->query("SELECT kunci, nilai FROM pengaturan")->fetchAll(PDO::FETCH_KEY_PAIR);

        view('admin.pengaturan.index', [
            'title'    => 'Pengaturan Sistem & Tampilan',
            'settings' => $settings,
        ]);
    }

    public function update() {
        $pdo = getDbConnection();

        $allowedKeys = ['login_title', 'login_subtitle', 'login_desc'];
        foreach ($allowedKeys as $k) {
            if (isset($_POST[$k])) {
                $stmt = $pdo->prepare("INSERT INTO pengaturan (kunci, nilai) VALUES (?, ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)");
                $stmt->execute([$k, trim($_POST[$k])]);
            }
        }

        // Handle upload background login
        if (isset($_FILES['login_bg_file']) && $_FILES['login_bg_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['login_bg_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowedExt)) {
                json_response(['success' => false, 'message' => 'Format gambar background harus JPG, PNG, atau WEBP!'], 422);
            }

            if ($file['size'] > 5 * 1024 * 1024) {
                json_response(['success' => false, 'message' => 'Ukuran gambar background maksimal 5MB!'], 422);
            }

            $uploadDir = __DIR__ . '/../../../public/uploads/settings/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = 'bg_login_' . time() . '.' . $ext;
            $destPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $relPath = 'uploads/settings/' . $fileName;
                $stmt = $pdo->prepare("INSERT INTO pengaturan (kunci, nilai) VALUES ('login_bg_image', ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)");
                $stmt->execute([$relPath]);
            } else {
                json_response(['success' => false, 'message' => 'Gagal mengunggah berkas background!'], 500);
            }
        }

        // Handle upload logo aplikasi
        if (isset($_FILES['app_logo_file']) && $_FILES['app_logo_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['app_logo_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExt = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

            if (!in_array($ext, $allowedExt)) {
                json_response(['success' => false, 'message' => 'Format logo harus JPG, PNG, WEBP, atau SVG!'], 422);
            }

            if ($file['size'] > 2 * 1024 * 1024) {
                json_response(['success' => false, 'message' => 'Ukuran logo maksimal 2MB!'], 422);
            }

            $uploadDir = __DIR__ . '/../../../public/uploads/settings/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = 'logo_' . time() . '.' . $ext;
            $destPath = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                $relPath = 'uploads/settings/' . $fileName;
                $stmt = $pdo->prepare("INSERT INTO pengaturan (kunci, nilai) VALUES ('app_logo', ?) ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)");
                $stmt->execute([$relPath]);
            } else {
                json_response(['success' => false, 'message' => 'Gagal mengunggah logo aplikasi!'], 500);
            }
        }

        audit_log('UPDATE', 'pengaturan', 'Admin memperbarui pengaturan tampilan (logo / background login / identitas)');

        json_response([
            'success' => true,
            'message' => 'Pengaturan tampilan dan logo berhasil disimpan!'
        ]);
    }

    public function resetBg() {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("UPDATE pengaturan SET nilai = 'img/gedung-kemenag.jpg' WHERE kunci = 'login_bg_image'");
        $stmt->execute();

        audit_log('UPDATE', 'pengaturan', 'Admin mereset background login ke default');

        json_response([
            'success' => true,
            'message' => 'Background login dikembalikan ke gambar default!'
        ]);
    }

    public function resetLogo() {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("UPDATE pengaturan SET nilai = 'img/logo-kemenag.svg' WHERE kunci = 'app_logo'");
        $stmt->execute();

        audit_log('UPDATE', 'pengaturan', 'Admin mereset logo aplikasi ke default');

        json_response([
            'success' => true,
            'message' => 'Logo aplikasi berhasil dikembalikan ke default!'
        ]);
    }
}

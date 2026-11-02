<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use PDO;

class AuditLogController {
    public function index() {
        $pdo = getDbConnection();

        $kategori = trim($_GET['kategori'] ?? '');
        $q = trim($_GET['q'] ?? '');

        $sql = "
            SELECT al.*, u.username, u.nama_lengkap
            FROM audit_logs al
            LEFT JOIN users u ON al.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if ($kategori !== '') {
            $sql .= " AND al.kategori = ?";
            $params[] = $kategori;
        }

        if ($q !== '') {
            $sql .= " AND (al.detail LIKE ? OR al.action LIKE ? OR u.username LIKE ? OR u.nama_lengkap LIKE ?)";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
            $params[] = "%$q%";
        }

        $sql .= " ORDER BY al.id DESC LIMIT 200";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $logs = $stmt->fetchAll();

        // Get distinct categories for filter
        $kategoris = $pdo->query("SELECT DISTINCT kategori FROM audit_logs ORDER BY kategori ASC")->fetchAll(PDO::FETCH_COLUMN);

        view('admin.audit.index', [
            'title'     => 'Audit Log Sistem',
            'logs'      => $logs,
            'kategoris' => $kategoris,
            'selectedKategori' => $kategori,
            'searchQuery'      => $q,
        ]);
    }

    public function clear() {
        $pdo = getDbConnection();
        $pdo->exec("DELETE FROM audit_logs");
        audit_log('CLEAR_LOGS', 'admin', 'Semua riwayat log aktivitas dibersihkan oleh ' . Auth::user()['username']);
        json_response(['success' => true, 'message' => 'Audit log berhasil dibersihkan.']);
    }
}

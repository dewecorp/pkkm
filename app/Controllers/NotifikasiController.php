<?php
namespace App\Controllers;

use App\Core\Auth;

class NotifikasiController {
    public function index(): void {
        if (!Auth::hasAnyRole(['admin', 'kamad'])) {
            json_response(['success' => false, 'message' => 'Akses ditolak!'], 403);
        }

        $pdo = getDbConnection();
        $userId = (int)Auth::id();

        $stmt = $pdo->prepare("
            SELECT id, judul, pesan, link, is_read, created_at
            FROM notifikasi
            WHERE user_id = ?
            ORDER BY id DESC
            LIMIT 15
        ");
        $stmt->execute([$userId]);
        $items = $stmt->fetchAll();

        $unread = $pdo->prepare("SELECT COUNT(*) FROM notifikasi WHERE user_id = ? AND is_read = 0");
        $unread->execute([$userId]);

        foreach ($items as &$item) {
            $item['id'] = (int)$item['id'];
            $item['is_read'] = (int)$item['is_read'];
            $item['waktu'] = $this->waktu($item['created_at']);
            unset($item['created_at']);
        }
        unset($item);

        json_response([
            'success' => true,
            'unread' => (int)$unread->fetchColumn(),
            'items' => $items,
        ]);
    }

    public function read(): void {
        if (!Auth::hasAnyRole(['admin', 'kamad'])) {
            json_response(['success' => false, 'message' => 'Akses ditolak!'], 403);
        }

        $pdo = getDbConnection();
        $pdo->prepare("UPDATE notifikasi SET is_read = 1 WHERE user_id = ? AND is_read = 0")
            ->execute([(int)Auth::id()]);

        json_response(['success' => true]);
    }

    private function waktu(string $timestamp): string {
        $selisih = time() - strtotime($timestamp);
        if ($selisih < 60) return 'Baru saja';
        if ($selisih < 3600) return floor($selisih / 60) . ' menit lalu';
        if ($selisih < 86400) return floor($selisih / 3600) . ' jam lalu';
        if ($selisih < 604800) return floor($selisih / 86400) . ' hari lalu';
        return date('d M Y H:i', strtotime($timestamp));
    }
}

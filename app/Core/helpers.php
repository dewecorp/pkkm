<?php
// Helper functions

function base_url(string $path = ''): string {
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    $base = rtrim($scriptDir, '/\\');
    return $base . '/' . ltrim($path, '/');
}

function asset_url(string $path): string {
    $path = ltrim($path, '/');
    $file = __DIR__ . '/../../public/' . $path;
    $version = is_file($file) ? filemtime($file) : null;
    return base_url($path) . ($version ? '?v=' . $version : '');
}

function nav_items(): array {
    $navAdmin = [
        ['icon'=>'fa-gauge-high',     'label'=>'Dashboard',        'url'=>'admin/dashboard',          'key'=>'dashboard'],
        ['icon'=>'fa-calendar-days',  'label'=>'Periode Penilaian','url'=>'admin/periode',            'key'=>'periode'],
        ['icon'=>'fa-school',         'label'=>'Data Madrasah',    'url'=>'admin/madrasah',           'key'=>'madrasah'],
        ['icon'=>'fa-user-tie',       'label'=>'Kepala Madrasah',  'url'=>'admin/kepala-madrasah',    'key'=>'kepala'],
        ['icon'=>'fa-users',          'label'=>'Pengguna & Penilai','url'=>'admin/pengguna',          'key'=>'pengguna'],
        ['icon'=>'fa-book-open',      'label'=>'Master Instrumen', 'url'=>'admin/instrumen',          'key'=>'instrumen'],
        ['icon'=>'fa-clipboard-list', 'label'=>'Form Penilaian',   'url'=>'form-penilaian',           'key'=>'form-penilaian'],
        ['icon'=>'fa-chart-pie',      'label'=>'Rekapitulasi',     'url'=>'laporan',                  'key'=>'laporan'],
        ['icon'=>'fa-folder-open',    'label'=>'Bukti Dukung',     'url'=>'bukti',                    'key'=>'bukti'],
        ['icon'=>'fa-file-lines',     'label'=>'Audit Log',        'url'=>'admin/audit-log',          'key'=>'audit'],
        ['icon'=>'fa-gear',           'label'=>'Pengaturan',       'url'=>'admin/pengaturan',         'key'=>'pengaturan'],
    ];
    $navKamad = [
        ['icon'=>'fa-gauge-high',     'label'=>'Dashboard',        'url'=>'kamad/dashboard',          'key'=>'dashboard'],
        ['icon'=>'fa-clipboard-list', 'label'=>'Form Penilaian',   'url'=>'form-penilaian',           'key'=>'form-penilaian'],
        ['icon'=>'fa-cloud-arrow-up', 'label'=>'Bukti Kinerja',    'url'=>'bukti',                    'key'=>'bukti'],
        ['icon'=>'fa-chart-bar',      'label'=>'Hasil Penilaian',  'url'=>'laporan',                  'key'=>'laporan'],
    ];
    $navPenilai = [
        ['icon'=>'fa-gauge-high',       'label'=>'Dashboard',         'url'=>'penilai/dashboard',        'key'=>'dashboard'],
        ['icon'=>'fa-pen-to-square',    'label'=>'Form Penilaian',    'url'=>'penilai/penilaian',        'key'=>'penilaian'],
        ['icon'=>'fa-folder-open',      'label'=>'Bukti Dukung',      'url'=>'bukti',                    'key'=>'bukti'],
    ];

    if (\App\Core\Auth::hasRole('admin')) return $navAdmin;
    if (\App\Core\Auth::hasRole('kamad')) return $navKamad;
    return $navPenilai;
}

function predikat_warna(?string $predikat): array {
    $map = [
        'Amat Baik' => ['bg' => '#ecfdf5', 'fg' => '#059669'],
        'Baik'      => ['bg' => '#eff6ff', 'fg' => '#2563eb'],
        'Cukup'     => ['bg' => '#fffbeb', 'fg' => '#d97706'],
        'Sedang'    => ['bg' => '#faf5ff', 'fg' => '#9333ea'],
        'Kurang'    => ['bg' => '#fef2f2', 'fg' => '#dc2626'],
    ];
    return $map[$predikat ?? ''] ?? ['bg' => '#eef7f1', 'fg' => '#1e6347'];
}

function notifikasi_kirim_ke_role(array $kodeRole, string $judul, string $pesan, ?string $link = null, ?int $kecualiUserId = null): void {
    try {
        if (empty($kodeRole)) return;
        $pdo = getDbConnection();
        $in = implode(',', array_fill(0, count($kodeRole), '?'));
        $stmt = $pdo->prepare("
            SELECT DISTINCT u.id
            FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            JOIN roles r ON r.id = ur.role_id
            WHERE u.status = 'aktif' AND r.kode_role IN ($in)
        ");
        $stmt->execute($kodeRole);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (!$ids) return;

        $ins = $pdo->prepare("INSERT INTO notifikasi (user_id, tipe, judul, pesan, link) VALUES (?, 'penilaian', ?, ?, ?)");
        foreach ($ids as $uid) {
            if ($kecualiUserId && (int)$uid === $kecualiUserId) continue;
            $ins->execute([(int)$uid, $judul, $pesan, $link]);
        }
    } catch (Throwable $e) {
        // Notifikasi tidak boleh menggagalkan proses utama.
    }
}

function view(string $viewPath, array $data = []): void {
    extract($data);
    $viewFile = __DIR__ . '/../../views/' . str_replace('.', '/', $viewPath) . '.php';
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        echo "View [{$viewPath}] not found!";
    }
}

function json_response(array $data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function csrf_field(): string {
    $token = \App\Core\Auth::csrfToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

function audit_log(string $action, string $kategori, ?string $detail = null): void {
    try {
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("INSERT INTO audit_logs (user_id, action, kategori, detail, ip_address, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            \App\Core\Auth::id(),
            $action,
            $kategori,
            $detail,
            $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            $_SERVER['HTTP_USER_AGENT'] ?? 'CLI/Unknown'
        ]);
    } catch (\Exception $e) {
        // fail silently for audit logging
    }
}

function get_setting(string $kunci, ?string $default = null): ?string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            $pdo = getDbConnection();
            $rows = $pdo->query("SELECT kunci, nilai FROM pengaturan")->fetchAll(\PDO::FETCH_KEY_PAIR);
            if ($rows) {
                $cache = $rows;
            }
        } catch (\Exception $e) {
            // table might not exist
        }
    }
    return $cache[$kunci] ?? $default;
}

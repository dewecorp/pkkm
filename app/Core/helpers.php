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

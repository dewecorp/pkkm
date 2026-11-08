<?php
namespace App\Core;

class Auth {
    // Batas waktu idle (detik): 2 jam = 7200 detik
    public const IDLE_TIMEOUT = 7200;

    public static function check(): bool {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }

        // Cek idle timeout (2 jam tanpa aktivitas)
        $now = time();
        $lastActivity = $_SESSION['last_activity'] ?? null;
        if ($lastActivity !== null && ($now - $lastActivity) > self::IDLE_TIMEOUT) {
            $username = $_SESSION['user']['username'] ?? 'unknown';
            audit_log('LOGOUT_TIMEOUT', 'auth', "Sesi pengguna {$username} kedaluwarsa karena idle lebih dari 2 jam");
            self::logout();
            $_SESSION['session_expired'] = true;
            return false;
        }

        // Perbarui timestamp aktivitas terakhir
        $_SESSION['last_activity'] = $now;
        return true;
    }

    public static function user(): ?array {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int {
        return $_SESSION['user_id'] ?? null;
    }

    public static function roles(): array {
        return $_SESSION['roles'] ?? [];
    }

    public static function hasRole(string $role): bool {
        return in_array($role, self::roles());
    }

    public static function hasAnyRole(array $roles): bool {
        return !empty(array_intersect($roles, self::roles()));
    }

    public static function login(array $user, array $roles): void {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user'] = $user;
        $_SESSION['roles'] = $roles;
        $_SESSION['created_time'] = time();
        $_SESSION['last_activity'] = time();
        unset($_SESSION['session_expired']);
    }

    public static function logout(): void {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function csrfToken(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): bool {
        return !empty($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}

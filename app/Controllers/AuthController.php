<?php
namespace App\Controllers;

use App\Core\Auth;
use PDO;

class AuthController {
    public function showLogin() {
        if (Auth::check()) {
            $this->redirectBasedOnRole();
        }
        $expired = !empty($_GET['expired']) || !empty($_SESSION['session_expired']);
        unset($_SESSION['session_expired']);
        view('auth.login', ['sessionExpired' => $expired]);
    }

    public function login() {
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $csrf = $_POST['csrf_token'] ?? '';

        if (!Auth::verifyCsrf($csrf)) {
            json_response(['success' => false, 'message' => 'CSRF token tidak valid!'], 400);
        }

        if (empty($username) || empty($password)) {
            json_response(['success' => false, 'message' => 'Username dan password wajib diisi!'], 422);
        }

        $pdo = getDbConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR nip = ?) AND status = 'aktif' LIMIT 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            json_response(['success' => false, 'message' => 'Username atau password salah!'], 401);
        }

        // Get Roles
        $rStmt = $pdo->prepare("
            SELECT r.kode_role 
            FROM roles r 
            JOIN user_roles ur ON ur.role_id = r.id 
            WHERE ur.user_id = ?
        ");
        $rStmt->execute([$user['id']]);
        $roles = $rStmt->fetchAll(PDO::FETCH_COLUMN);

        Auth::login($user, $roles);
        audit_log('LOGIN', 'auth', "Pengguna {$username} berhasil login");

        $redirect = base_url('dashboard');
        if (in_array('admin', $roles)) $redirect = base_url('admin/dashboard');
        elseif (in_array('kamad', $roles)) $redirect = base_url('kamad/dashboard');
        elseif (in_array('penilai', $roles)) $redirect = base_url('penilai/dashboard');

        json_response([
            'success' => true,
            'message' => 'Login berhasil!',
            'redirect' => $redirect
        ]);
    }

    public function logout() {
        $reason = $_GET['reason'] ?? '';
        $user = Auth::check() ? Auth::user()['username'] : null;
        if ($user) {
            if ($reason === 'timeout') {
                audit_log('LOGOUT_TIMEOUT', 'auth', "Pengguna {$user} logout otomatis karena idle lebih dari 2 jam");
            } else {
                audit_log('LOGOUT', 'auth', "Pengguna {$user} logout");
            }
        }
        Auth::logout();
        $target = base_url('login') . ($reason === 'timeout' ? '?expired=1' : '');
        header('Location: ' . $target);
        exit;
    }

    private function redirectBasedOnRole() {
        if (Auth::hasRole('admin')) {
            header('Location: ' . base_url('admin/dashboard'));
        } elseif (Auth::hasRole('kamad')) {
            header('Location: ' . base_url('kamad/dashboard'));
        } else {
            header('Location: ' . base_url('penilai/dashboard'));
        }
        exit;
    }
}

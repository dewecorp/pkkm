<?php
namespace App\Controllers;

use App\Core\Auth;
use PDO;

class AuthController {
    public function showLogin() {
        if (Auth::check()) {
            $this->redirectBasedOnRole();
        }
        view('auth.login');
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
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'aktif' LIMIT 1");
        $stmt->execute([$username]);
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
        if (Auth::check()) {
            audit_log('LOGOUT', 'auth', "Pengguna " . Auth::user()['username'] . " logout");
            Auth::logout();
        }
        header('Location: ' . base_url('login'));
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

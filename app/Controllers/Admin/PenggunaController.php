<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use PDO;

class PenggunaController {
    public function index() {
        $pdo  = getDbConnection();
        $rows = $pdo->query("
            SELECT u.*, 
                   GROUP_CONCAT(DISTINCT r.nama_role SEPARATOR ', ') as roles_text,
                   GROUP_CONCAT(DISTINCT r.id SEPARATOR ',') as role_ids,
                   GROUP_CONCAT(DISTINCT kp.nama_kelompok SEPARATOR ', ') as kelompok_text,
                   MAX(pp.periode_id) as periode_id,
                   MAX(pp.kelompok_id) as kelompok_id,
                   MAX(pp.urutan_penilai) as urutan_penilai
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            LEFT JOIN roles r ON ur.role_id = r.id
            LEFT JOIN periode_penilai pp ON pp.user_id = u.id
            LEFT JOIN kelompok_penilai kp ON pp.kelompok_id = kp.id
            GROUP BY u.id
            ORDER BY u.id DESC
        ")->fetchAll();

        $roles    = $pdo->query("SELECT * FROM roles")->fetchAll();
        $kelompok = $pdo->query("SELECT * FROM kelompok_penilai ORDER BY id")->fetchAll();
        $periode  = $pdo->query("SELECT id,nama_periode FROM periode_penilaian ORDER BY id DESC LIMIT 10")->fetchAll();

        view('admin.pengguna.index', [
            'title'    => 'Pengguna & Penilai',
            'rows'     => $rows,
            'roles'    => $roles,
            'kelompok' => $kelompok,
            'periode'  => $periode,
        ]);
    }

    public function store() {
        $d   = $_POST;
        $pdo = getDbConnection();

        if (empty($d['username']) || empty($d['password']) || empty($d['nama_lengkap'])) {
            json_response(['success'=>false,'message'=>'Username, password, dan nama lengkap wajib diisi!'],422);
        }

        $chk = $pdo->prepare("SELECT id FROM users WHERE username=?");
        $chk->execute([$d['username']]);
        if ($chk->fetchColumn()) json_response(['success'=>false,'message'=>'Username sudah digunakan!'],422);

        $hash = password_hash($d['password'], PASSWORD_BCRYPT);
        $ins  = $pdo->prepare("INSERT INTO users (username,password,nama_lengkap,nip,npk,jabatan,pangkat_golongan,unit_kerja,status) VALUES (?,?,?,?,?,?,?,?,'aktif')");
        $ins->execute([$d['username'],$hash,$d['nama_lengkap'],$d['nip']??'',$d['npk']??'',$d['jabatan']??'',$d['pangkat_golongan']??'',$d['unit_kerja']??'']);
        $uid = $pdo->lastInsertId();

        if (!empty($d['role_id'])) {
            foreach ((array)$d['role_id'] as $rid) {
                $pdo->prepare("INSERT IGNORE INTO user_roles(user_id,role_id)VALUES(?,?)")->execute([$uid,$rid]);
            }
        }

        // Jika penilai: tugaskan ke periode dan kelompok
        if (!empty($d['periode_id']) && !empty($d['kelompok_id'])) {
            $kId = (int)$d['kelompok_id'];
            $pId = (int)$d['periode_id'];

            $pdo->prepare("INSERT INTO periode_penilai (periode_id,user_id,kelompok_id,urutan_penilai,status_penilaian) VALUES (?,?,?,?,?)")
                ->execute([$pId,$uid,$kId,$d['urutan_penilai']??1,'belum_mulai']);
            $ppId = $pdo->lastInsertId();

            $pdo->prepare("INSERT INTO penilaian (periode_penilai_id,periode_id,user_id,kelompok_id,status) VALUES (?,?,?,?,'draft')")
                ->execute([$ppId,$pId,$uid,$kId]);
        }

        audit_log('TAMBAH_PENGGUNA','admin',"Pengguna baru {$d['username']} dibuat oleh ".Auth::user()['username']);
        json_response(['success'=>true,'message'=>'Pengguna berhasil ditambahkan!']);
    }

    public function update() {
        $d   = $_POST;
        $uid = (int)($d['id'] ?? 0);
        $pdo = getDbConnection();

        if (empty($d['username']) || empty($d['nama_lengkap'])) {
            json_response(['success'=>false,'message'=>'Username dan nama lengkap wajib diisi!'], 422);
        }

        $chk = $pdo->prepare("SELECT id FROM users WHERE username=? AND id!=?");
        $chk->execute([$d['username'], $uid]);
        if ($chk->fetchColumn()) {
            json_response(['success'=>false,'message'=>'Username sudah digunakan akun lain!'], 422);
        }

        if (!empty($d['password'])) {
            if (strlen(trim($d['password'])) < 6) {
                json_response(['success'=>false,'message'=>'Password minimal 6 karakter!'], 422);
            }
            $hash = password_hash($d['password'], PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET username=?, password=?, nama_lengkap=?, nip=?, npk=?, jabatan=?, pangkat_golongan=?, unit_kerja=? WHERE id=?");
            $stmt->execute([
                $d['username'], $hash, $d['nama_lengkap'],
                $d['nip']??'', $d['npk']??'', $d['jabatan']??'',
                $d['pangkat_golongan']??'', $d['unit_kerja']??'', $uid
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET username=?, nama_lengkap=?, nip=?, npk=?, jabatan=?, pangkat_golongan=?, unit_kerja=? WHERE id=?");
            $stmt->execute([
                $d['username'], $d['nama_lengkap'],
                $d['nip']??'', $d['npk']??'', $d['jabatan']??'',
                $d['pangkat_golongan']??'', $d['unit_kerja']??'', $uid
            ]);
        }

        if (isset($d['role_id'])) {
            $pdo->prepare("DELETE FROM user_roles WHERE user_id=?")->execute([$uid]);
            foreach ((array)$d['role_id'] as $rid) {
                $pdo->prepare("INSERT IGNORE INTO user_roles(user_id,role_id) VALUES(?,?)")->execute([$uid, $rid]);
            }
        }

        if (!empty($d['periode_id']) && !empty($d['kelompok_id'])) {
            $kId = (int)$d['kelompok_id'];
            $pId = (int)$d['periode_id'];
            $urutan = (int)($d['urutan_penilai'] ?? 1);

            $chkPP = $pdo->prepare("SELECT id FROM periode_penilai WHERE user_id=? AND periode_id=?");
            $chkPP->execute([$uid, $pId]);
            $existingPP = $chkPP->fetchColumn();

            if ($existingPP) {
                $pdo->prepare("UPDATE periode_penilai SET kelompok_id=?, urutan_penilai=? WHERE id=?")->execute([$kId, $urutan, $existingPP]);
                $pdo->prepare("UPDATE penilaian SET kelompok_id=? WHERE periode_penilai_id=?")->execute([$kId, $existingPP]);
            } else {
                $pdo->prepare("INSERT INTO periode_penilai (periode_id,user_id,kelompok_id,urutan_penilai,status_penilaian) VALUES (?,?,?,?,'belum_mulai')")
                    ->execute([$pId, $uid, $kId, $urutan]);
                $ppId = $pdo->lastInsertId();
                $pdo->prepare("INSERT INTO penilaian (periode_penilai_id,periode_id,user_id,kelompok_id,status) VALUES (?,?,?,?,'draft')")
                    ->execute([$ppId, $pId, $uid, $kId]);
            }
        }

        audit_log('UPDATE_PENGGUNA','admin',"Pengguna #{$uid} {$d['username']} diperbarui oleh ".Auth::user()['username']);
        json_response(['success'=>true,'message'=>'Data pengguna berhasil diperbarui!']);
    }

    public function resetPassword() {
        $uid  = (int)($_POST['id']??0);
        $pass = trim($_POST['password']??'');
        if (strlen($pass) < 6) json_response(['success'=>false,'message'=>'Password minimal 6 karakter!'],422);
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        getDbConnection()->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash,$uid]);
        json_response(['success'=>true,'message'=>'Password berhasil direset.']);
    }

    public function toggleStatus() {
        $uid = (int)($_POST['id']??0);
        $pdo = getDbConnection();
        $cur = $pdo->prepare("SELECT status FROM users WHERE id=?");
        $cur->execute([$uid]);
        $status = $cur->fetchColumn();
        $new = $status === 'aktif' ? 'nonaktif' : 'aktif';
        $pdo->prepare("UPDATE users SET status=? WHERE id=?")->execute([$new,$uid]);
        json_response(['success'=>true,'message'=>"Status pengguna diubah menjadi $new.",'new_status'=>$new]);
    }

    public function destroy() {
        $uid = (int)($_POST['id']??0);
        if ($uid === Auth::id() || $uid === 1) {
            json_response(['success'=>false,'message'=>'Akun admin utama tidak boleh dihapus!'], 422);
        }
        $pdo = getDbConnection();
        $hasPenilaian = $pdo->prepare("SELECT COUNT(*) FROM detail_penilaian dp JOIN penilaian p ON dp.penilaian_id=p.id WHERE p.user_id=?");
        $hasPenilaian->execute([$uid]);
        if ($hasPenilaian->fetchColumn() > 0) {
            json_response(['success'=>false,'message'=>'Pengguna sudah memiliki riwayat pengisian nilai, tidak dapat dihapus!'], 422);
        }
        $pdo->prepare("DELETE FROM user_roles WHERE user_id=?")->execute([$uid]);
        $pdo->prepare("DELETE FROM periode_penilai WHERE user_id=?")->execute([$uid]);
        $pdo->prepare("DELETE FROM penilaian WHERE user_id=?")->execute([$uid]);
        $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$uid]);
        json_response(['success'=>true,'message'=>'Pengguna berhasil dihapus.']);
    }
}

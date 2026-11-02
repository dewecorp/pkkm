<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use PDO;

class KepalaController {
    public function index() {
        $pdo  = getDbConnection();
        $rows = $pdo->query("SELECT k.*,m.nama_madrasah FROM kepala_madrasah k JOIN madrasah m ON k.madrasah_id=m.id ORDER BY k.nama_lengkap")->fetchAll();
        $madrasahList = $pdo->query("SELECT id,nama_madrasah FROM madrasah ORDER BY nama_madrasah")->fetchAll();
        view('admin.kepala.index',['title'=>'Data Kepala Madrasah','rows'=>$rows,'madrasahList'=>$madrasahList]);
    }

    public function store() {
        $d = $_POST; $pdo = getDbConnection();
        $userId = null;
        if (!empty($d['username']) && !empty($d['password'])) {
            $hash = password_hash($d['password'], PASSWORD_BCRYPT);
            $ins  = $pdo->prepare("INSERT INTO users (username,password,nama_lengkap,nip,npk,nuptk,jabatan,pangkat_golongan,status) VALUES (?,?,?,?,?,?,?,?,'aktif')");
            $ins->execute([$d['username'],$hash,$d['nama_lengkap'],$d['nip']??'',$d['npk']??'',$d['nuptk']??'','Kepala Madrasah',$d['pangkat_golongan']??'']);
            $userId = $pdo->lastInsertId();
            $roleId = $pdo->query("SELECT id FROM roles WHERE kode_role='kamad'")->fetchColumn();
            $pdo->prepare("INSERT INTO user_roles(user_id,role_id)VALUES(?,?)")->execute([$userId,$roleId]);
        }
        $stmt = $pdo->prepare("INSERT INTO kepala_madrasah (user_id,madrasah_id,nama_lengkap,nip,npk,nuptk,tempat_lahir,tanggal_lahir,jenis_kelamin,pangkat_golongan,pendidikan_terakhir,tmt_kamad,masa_kerja,status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,'aktif')");
        $stmt->execute([$userId,$d['madrasah_id'],$d['nama_lengkap'],$d['nip']??'',$d['npk']??'',$d['nuptk']??'',$d['tempat_lahir']??'',$d['tanggal_lahir']??null,$d['jenis_kelamin']??'L',$d['pangkat_golongan']??'',$d['pendidikan_terakhir']??'',$d['tmt_kamad']??null,$d['masa_kerja']??'']);
        json_response(['success'=>true,'message'=>'Data kepala madrasah berhasil disimpan!']);
    }

    public function update() {
        $d = $_POST; $id = (int)($d['id']??0); $pdo = getDbConnection();
        $stmt = $pdo->prepare("UPDATE kepala_madrasah SET madrasah_id=?,nama_lengkap=?,nip=?,npk=?,nuptk=?,tempat_lahir=?,tanggal_lahir=?,jenis_kelamin=?,pangkat_golongan=?,pendidikan_terakhir=?,tmt_kamad=?,masa_kerja=?,status=? WHERE id=?");
        $stmt->execute([$d['madrasah_id'],$d['nama_lengkap'],$d['nip']??'',$d['npk']??'',$d['nuptk']??'',$d['tempat_lahir']??'',$d['tanggal_lahir']??null,$d['jenis_kelamin']??'L',$d['pangkat_golongan']??'',$d['pendidikan_terakhir']??'',$d['tmt_kamad']??null,$d['masa_kerja']??'',$d['status']??'aktif',$id]);
        json_response(['success'=>true,'message'=>'Data kepala madrasah berhasil diperbarui!']);
    }

    public function destroy() {
        $id = (int)($_POST['id']??0); $pdo = getDbConnection();
        $trx = $pdo->prepare("SELECT COUNT(*) FROM periode_penilaian WHERE kamad_id=?");
        $trx->execute([$id]);
        if ($trx->fetchColumn() > 0) json_response(['success'=>false,'message'=>'Data tidak dapat dihapus karena sudah ada periode penilaian yang menggunakan kepala madrasah ini!'],422);
        $pdo->prepare("DELETE FROM kepala_madrasah WHERE id=?")->execute([$id]);
        json_response(['success'=>true,'message'=>'Data kepala madrasah berhasil dihapus.']);
    }
}

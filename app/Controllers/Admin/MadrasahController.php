<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use PDO;

class MadrasahController {
    public function index() {
        $pdo  = getDbConnection();
        $rows = $pdo->query("SELECT * FROM madrasah ORDER BY nama_madrasah")->fetchAll();
        view('admin.madrasah.index', ['title'=>'Data Madrasah','rows'=>$rows]);
    }

    public function store() {
        $d = $_POST;
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("INSERT INTO madrasah (nsm,npsn,nama_madrasah,status_akreditasi,tahun_akreditasi,alamat,desa_kelurahan,kecamatan,kabupaten_kota,provinsi,telp_fax,email,website) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$d['nsm']??'',$d['npsn']??'',$d['nama_madrasah']??'',$d['status_akreditasi']??'A',$d['tahun_akreditasi']??'2024',$d['alamat']??'',$d['desa_kelurahan']??'',$d['kecamatan']??'',$d['kabupaten_kota']??'Jepara',$d['provinsi']??'Jawa Tengah',$d['telp_fax']??'',$d['email']??'',$d['website']??'']);
        json_response(['success'=>true,'message'=>'Data madrasah berhasil disimpan!']);
    }

    public function update() {
        $d = $_POST; $id = (int)($d['id']??0);
        $pdo = getDbConnection();
        $stmt = $pdo->prepare("UPDATE madrasah SET nsm=?,npsn=?,nama_madrasah=?,status_akreditasi=?,tahun_akreditasi=?,alamat=?,desa_kelurahan=?,kecamatan=?,kabupaten_kota=?,provinsi=?,telp_fax=?,email=?,website=? WHERE id=?");
        $stmt->execute([$d['nsm']??'',$d['npsn']??'',$d['nama_madrasah']??'',$d['status_akreditasi']??'A',$d['tahun_akreditasi']??'2024',$d['alamat']??'',$d['desa_kelurahan']??'',$d['kecamatan']??'',$d['kabupaten_kota']??'',$d['provinsi']??'',$d['telp_fax']??'',$d['email']??'',$d['website']??'',$id]);
        json_response(['success'=>true,'message'=>'Data madrasah berhasil diperbarui!']);
    }

    public function destroy() {
        $id = (int)($_POST['id']??0);
        $pdo = getDbConnection();
        $trx = $pdo->prepare("SELECT COUNT(*) FROM periode_penilaian WHERE madrasah_id=?");
        $trx->execute([$id]);
        if ($trx->fetchColumn() > 0) json_response(['success'=>false,'message'=>'Madrasah tidak dapat dihapus, sudah digunakan dalam periode penilaian!'],422);
        $pdo->prepare("DELETE FROM madrasah WHERE id=?")->execute([$id]);
        json_response(['success'=>true,'message'=>'Data madrasah berhasil dihapus.']);
    }
}

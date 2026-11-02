<?php
namespace App\Controllers\Admin;

use App\Core\Auth;
use PDO;

class PeriodeController {
    public function index() {
        $pdo = getDbConnection();
        $rows = $pdo->query("
            SELECT p.*, m.nama_madrasah, km.nama_lengkap as nama_kamad, v.nomor_versi
            FROM periode_penilaian p
            JOIN madrasah m ON p.madrasah_id = m.id
            JOIN kepala_madrasah km ON p.kamad_id = km.id
            JOIN instrumen_versi v ON p.instrumen_versi_id = v.id
            ORDER BY p.id DESC
        ")->fetchAll();

        $madrasahList = $pdo->query("SELECT id, nama_madrasah FROM madrasah ORDER BY nama_madrasah")->fetchAll();
        $kamadList    = $pdo->query("SELECT k.id, k.nama_lengkap, m.nama_madrasah FROM kepala_madrasah k JOIN madrasah m ON k.madrasah_id = m.id WHERE k.status='aktif' ORDER BY k.nama_lengkap")->fetchAll();
        $versiList    = $pdo->query("SELECT id, nomor_versi, nama_versi FROM instrumen_versi WHERE status='aktif' ORDER BY id DESC")->fetchAll();

        view('admin.periode.index', [
            'title' => 'Periode Penilaian',
            'rows'  => $rows,
            'madrasahList' => $madrasahList,
            'kamadList'    => $kamadList,
            'versiList'    => $versiList,
        ]);
    }

    public function store() {
        $pdo = getDbConnection();
        $d = $_POST;

        $required = ['nama_periode','tahun_penilaian','jenis_penilaian','tanggal_mulai','tanggal_selesai','madrasah_id','kamad_id','instrumen_versi_id'];
        foreach ($required as $field) {
            if (empty($d[$field])) {
                json_response(['success'=>false,'message'=>"Field $field wajib diisi!"], 422);
            }
        }

        $stmt = $pdo->prepare("INSERT INTO periode_penilaian
            (madrasah_id,kamad_id,instrumen_versi_id,nama_periode,tahun_penilaian,jenis_penilaian,tahun_ke,tanggal_mulai,tanggal_selesai,tempat_penilaian,status,created_by)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([
            $d['madrasah_id'],$d['kamad_id'],$d['instrumen_versi_id'],
            $d['nama_periode'],$d['tahun_penilaian'],$d['jenis_penilaian'],
            $d['tahun_ke']??4,$d['tanggal_mulai'],$d['tanggal_selesai'],
            $d['tempat_penilaian']??'','draft',Auth::id()
        ]);
        $periodeId = $pdo->lastInsertId();
        $this->createSnapshot($pdo, $periodeId, $d['instrumen_versi_id']);

        audit_log('TAMBAH_PERIODE','periode',"Periode #{$periodeId} {$d['nama_periode']} dibuat oleh ".Auth::user()['username']);
        json_response(['success'=>true,'message'=>'Periode berhasil ditambahkan!']);
    }

    public function update() {
        $pdo = getDbConnection();
        $d   = $_POST;
        $id  = (int)($d['id']??0);

        $stmt = $pdo->prepare("UPDATE periode_penilaian SET
            nama_periode=?,tahun_penilaian=?,jenis_penilaian=?,tahun_ke=?,
            tanggal_mulai=?,tanggal_selesai=?,tempat_penilaian=?,status=?
            WHERE id=?");
        $stmt->execute([
            $d['nama_periode'],$d['tahun_penilaian'],$d['jenis_penilaian'],$d['tahun_ke']??4,
            $d['tanggal_mulai'],$d['tanggal_selesai'],$d['tempat_penilaian']??'',$d['status']??'draft',$id
        ]);
        json_response(['success'=>true,'message'=>'Periode berhasil diperbarui!']);
    }

    public function destroy() {
        $id  = (int)($_POST['id']??0);
        $pdo = getDbConnection();

        $trx = $pdo->prepare("SELECT COUNT(*) FROM detail_penilaian dp JOIN penilaian p ON dp.penilaian_id=p.id WHERE p.periode_id=?");
        $trx->execute([$id]);
        if ($trx->fetchColumn() > 0) {
            json_response(['success'=>false,'message'=>'Periode tidak dapat dihapus karena sudah memiliki data penilaian!'],422);
        }

        $pdo->prepare("DELETE FROM periode_penilaian WHERE id=?")->execute([$id]);
        json_response(['success'=>true,'message'=>'Periode berhasil dihapus.']);
    }

    private function createSnapshot(PDO $pdo, int $periodeId, int $versiId): void {
        $komps = $pdo->prepare("SELECT * FROM komponen_penilaian WHERE versi_id=? ORDER BY nomor_komponen");
        $komps->execute([$versiId]);
        foreach ($komps->fetchAll() as $k) {
            $pdo->prepare("INSERT INTO snapshot_komponen (periode_id,master_komponen_id,nomor_komponen,nama_komponen,deskripsi,urutan) VALUES (?,?,?,?,?,?)")
                ->execute([$periodeId,$k['id'],$k['nomor_komponen'],$k['nama_komponen'],$k['deskripsi'],$k['urutan']]);
            $skId = $pdo->lastInsertId();

            $tugas = $pdo->prepare("SELECT * FROM tugas_utama WHERE komponen_id=? ORDER BY urutan");
            $tugas->execute([$k['id']]);
            foreach ($tugas->fetchAll() as $t) {
                $pdo->prepare("INSERT INTO snapshot_tugas_utama (periode_id,snapshot_komponen_id,master_tugas_id,kode_tugas,nama_tugas,urutan) VALUES (?,?,?,?,?,?)")
                    ->execute([$periodeId,$skId,$t['id'],$t['kode_tugas'],$t['nama_tugas'],$t['urutan']]);
                $stId = $pdo->lastInsertId();

                $inds = $pdo->prepare("SELECT * FROM indikator_kerja WHERE tugas_id=? ORDER BY urutan");
                $inds->execute([$t['id']]);
                foreach ($inds->fetchAll() as $i) {
                    $pdo->prepare("INSERT INTO snapshot_indikator (periode_id,snapshot_tugas_id,master_indikator_id,kode_indikator,nomor_sub,uraian_indikator,data_kinerja_diharapkan,bukti_autentik,skor_maksimal,urutan) VALUES (?,?,?,?,?,?,?,?,?,?)")
                        ->execute([$periodeId,$stId,$i['id'],$i['kode_indikator'],$i['nomor_sub'],$i['uraian_indikator'],$i['data_kinerja_diharapkan'],$i['bukti_autentik'],$i['skor_maksimal'],$i['urutan']]);
                }
            }
        }
    }
}

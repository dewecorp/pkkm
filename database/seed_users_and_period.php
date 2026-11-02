<?php
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDbConnection();
    $pdo->beginTransaction();

    // 1. Madrasah
    $mStmt = $pdo->query("SELECT id FROM madrasah LIMIT 1");
    $madrasahId = $mStmt->fetchColumn();
    if (!$madrasahId) {
        $insM = $pdo->prepare("INSERT INTO madrasah (nsm, npsn, nama_madrasah, status_akreditasi, tahun_akreditasi, alamat, kabupaten_kota, provinsi, telp_fax) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $insM->execute([
            '121233200001',
            '20363210',
            'MAN 1 Jepara',
            'A (Unggul)',
            '2024',
            'Jl. Raya Bawu No. 1 Batealit',
            'Jepara',
            'Jawa Tengah',
            '(0291) 591234'
        ]);
        $madrasahId = $pdo->lastInsertId();
    }

    // Role helper
    $getRoleId = function($code) use ($pdo) {
        $st = $pdo->prepare("SELECT id FROM roles WHERE kode_role = ?");
        $st->execute([$code]);
        return $st->fetchColumn();
    };
    $roleKamadId = $getRoleId('kamad');
    $rolePenilaiId = $getRoleId('penilai');

    // 2. Kamad User & Kamad Data
    $uKamadStmt = $pdo->prepare("SELECT id FROM users WHERE username = 'kamad'");
    $uKamadStmt->execute();
    $kamadUserId = $uKamadStmt->fetchColumn();
    if (!$kamadUserId) {
        $pwd = password_hash('kamad123', PASSWORD_BCRYPT);
        $insU = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, nip, npk, nuptk, jabatan, pangkat_golongan, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'aktif')");
        $insU->execute([
            'kamad',
            $pwd,
            'Drs. H. Ahmad Fauzi, M.Pd.',
            '196805121994031002',
            '987654321',
            '1234567890123456',
            'Kepala Madrasah',
            'Pembina Tingkat I, IV/b'
        ]);
        $kamadUserId = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$kamadUserId, $roleKamadId]);
    }

    $kmStmt = $pdo->prepare("SELECT id FROM kepala_madrasah WHERE user_id = ?");
    $kmStmt->execute([$kamadUserId]);
    $kamadId = $kmStmt->fetchColumn();
    if (!$kamadId) {
        $insKm = $pdo->prepare("INSERT INTO kepala_madrasah (user_id, madrasah_id, nama_lengkap, nip, npk, nuptk, tempat_lahir, tanggal_lahir, jenis_kelamin, pangkat_golongan, pendidikan_terakhir, tmt_kamad, masa_kerja, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif')");
        $insKm->execute([
            $kamadUserId,
            $madrasahId,
            'Drs. H. Ahmad Fauzi, M.Pd.',
            '196805121994031002',
            '987654321',
            '1234567890123456',
            'Jepara',
            '1968-05-12',
            'L',
            'Pembina Tingkat I, IV/b',
            'S2 Manajemen Pendidikan',
            '2021-01-02',
            '4 Tahun'
        ]);
        $kamadId = $pdo->lastInsertId();
    }

    // 3. Penilai Akun untuk 6 Kelompok
    $penilaiList = [
        ['username' => 'penilai_ks', 'nama' => 'Dra. Hj. Ida Nunung Puspowati, M.S.I.', 'kelompok' => 'KS', 'nip' => '197103151996032001', 'jabatan' => 'Kasi Pendidikan Madrasah'],
        ['username' => 'penilai_ky', 'nama' => 'H. Nur Salim, S.Ag., M.Pd.I.', 'kelompok' => 'KY', 'nip' => '-', 'jabatan' => 'Ketua Yayasan'],
        ['username' => 'penilai_wasmad', 'nama' => 'H. M. Abdul Haris, M.Pd.I.', 'kelompok' => 'WASMAD', 'nip' => '197001211997031001', 'jabatan' => 'Pengawas Madrasah Ahli Madya'],
        ['username' => 'penilai_guru', 'nama' => 'Subandi, S.Pd., M.Si.', 'kelompok' => 'GURU', 'nip' => '197508102002121003', 'jabatan' => 'Guru Madya'],
        ['username' => 'penilai_tendik', 'nama' => 'Siti Rohmah, S.E.', 'kelompok' => 'TENDIK', 'nip' => '198204152009012008', 'jabatan' => 'Kepala Tata Usaha'],
        ['username' => 'penilai_komite', 'nama' => 'H. Abdul Rozaq, S.H.', 'kelompok' => 'KOMITE', 'nip' => '-', 'jabatan' => 'Ketua Komite Madrasah']
    ];

    $penilaiUserIds = [];

    foreach ($penilaiList as $p) {
        $pStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $pStmt->execute([$p['username']]);
        $uid = $pStmt->fetchColumn();
        if (!$uid) {
            $pwd = password_hash('penilai123', PASSWORD_BCRYPT);
            $insP = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, nip, jabatan, status) VALUES (?, ?, ?, ?, ?, 'aktif')");
            $insP->execute([$p['username'], $pwd, $p['nama'], $p['nip'], $p['jabatan']]);
            $uid = $pdo->lastInsertId();
            $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$uid, $rolePenilaiId]);
        }
        $penilaiUserIds[$p['kelompok']] = $uid;
    }

    // 4. Buat Periode Penilaian Percontohan & Buat Snapshot Otomatis
    $perStmt = $pdo->prepare("SELECT id FROM periode_penilaian WHERE nama_periode LIKE ?");
    $perStmt->execute(['%PKKM 4 Tahunan 2024%']);
    $periodeId = $perStmt->fetchColumn();

    $versiId = $pdo->query("SELECT id FROM instrumen_versi WHERE nomor_versi = '1.0'")->fetchColumn();
    $adminId = $pdo->query("SELECT id FROM users WHERE username = 'admin'")->fetchColumn();

    if (!$periodeId) {
        $insPr = $pdo->prepare("INSERT INTO periode_penilaian (madrasah_id, kamad_id, instrumen_versi_id, nama_periode, tahun_penilaian, jenis_penilaian, tahun_ke, tanggal_mulai, tanggal_selesai, tempat_penilaian, status, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'aktif', ?)");
        $insPr->execute([
            $madrasahId,
            $kamadId,
            $versiId,
            'PKKM 4 Tahunan 2024 - Periode 2021-2024',
            2024,
            'empat_tahunan',
            4,
            '2024-01-01',
            '2024-12-31',
            'Jepara',
            $adminId
        ]);
        $periodeId = $pdo->lastInsertId();

        // SNAPSHOT INSTRUMEN: Copy dari master komponen, tugas, indikator ke snapshot_*
        $kompRows = $pdo->prepare("SELECT * FROM komponen_penilaian WHERE versi_id = ? ORDER BY nomor_komponen ASC");
        $kompRows->execute([$versiId]);
        $komps = $kompRows->fetchAll();

        foreach ($komps as $k) {
            $insSk = $pdo->prepare("INSERT INTO snapshot_komponen (periode_id, master_komponen_id, nomor_komponen, nama_komponen, deskripsi, urutan) VALUES (?, ?, ?, ?, ?, ?)");
            $insSk->execute([$periodeId, $k['id'], $k['nomor_komponen'], $k['nama_komponen'], $k['deskripsi'], $k['urutan']]);
            $snapKompId = $pdo->lastInsertId();

            $tugasRows = $pdo->prepare("SELECT * FROM tugas_utama WHERE komponen_id = ? ORDER BY urutan ASC");
            $tugasRows->execute([$k['id']]);
            $tugass = $tugasRows->fetchAll();

            foreach ($tugass as $t) {
                $insSt = $pdo->prepare("INSERT INTO snapshot_tugas_utama (periode_id, snapshot_komponen_id, master_tugas_id, kode_tugas, nama_tugas, urutan) VALUES (?, ?, ?, ?, ?, ?)");
                $insSt->execute([$periodeId, $snapKompId, $t['id'], $t['kode_tugas'], $t['nama_tugas'], $t['urutan']]);
                $snapTugasId = $pdo->lastInsertId();

                $indRows = $pdo->prepare("SELECT * FROM indikator_kerja WHERE tugas_id = ? ORDER BY urutan ASC");
                $indRows->execute([$t['id']]);
                $inds = $indRows->fetchAll();

                foreach ($inds as $i) {
                    $insSi = $pdo->prepare("INSERT INTO snapshot_indikator (periode_id, snapshot_tugas_id, master_indikator_id, kode_indikator, nomor_sub, uraian_indikator, data_kinerja_diharapkan, bukti_autentik, skor_maksimal, urutan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $insSi->execute([$periodeId, $snapTugasId, $i['id'], $i['kode_indikator'], $i['nomor_sub'], $i['uraian_indikator'], $i['data_kinerja_diharapkan'], $i['bukti_autentik'], $i['skor_maksimal'], $i['urutan']]);
                }
            }
        }

        // 5. Tugaskan 6 Penilai ke Periode Ini
        foreach ($penilaiList as $p) {
            $kId = $pdo->prepare("SELECT id FROM kelompok_penilai WHERE kode_kelompok = ?");
            $kId->execute([$p['kelompok']]);
            $kelompokId = $kId->fetchColumn();

            $insPp = $pdo->prepare("INSERT INTO periode_penilai (periode_id, user_id, kelompok_id, urutan_penilai, status_penilaian) VALUES (?, ?, ?, 1, 'belum_mulai')");
            $insPp->execute([$periodeId, $penilaiUserIds[$p['kelompok']], $kelompokId]);
            $ppId = $pdo->lastInsertId();

            // Init header penilaian
            $insPen = $pdo->prepare("INSERT INTO penilaian (periode_penilai_id, periode_id, user_id, kelompok_id, status) VALUES (?, ?, ?, ?, 'draft')");
            $insPen->execute([$ppId, $periodeId, $penilaiUserIds[$p['kelompok']], $kelompokId]);
        }
    }

    $pdo->commit();
    echo "Seed Pengguna, Madrasah, Kamad, Periode, dan Snapshot selesai dengan sukses!\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Seed error: " . $e->getMessage() . "\n";
}

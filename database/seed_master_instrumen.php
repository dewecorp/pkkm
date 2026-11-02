<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../app/Services/PkkmExcelReader.php';

try {
    $pdo = getDbConnection();
    $excelFile = __DIR__ . '/../PKKM_TAHUN_KE-4_Haris1.xlsx';
    if (!file_exists($excelFile)) {
        die("File $excelFile tidak ditemukan!\n");
    }

    $reader = new PkkmExcelReader($excelFile);
    $rows = $reader->getSheetData('KS'); // KS contains all 108 indicators

    $pdo->beginTransaction();

    // 1. Create default admin user if not exists
    $adminStmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $adminStmt->execute();
    $adminId = $adminStmt->fetchColumn();

    if (!$adminId) {
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $insertAdmin = $pdo->prepare("INSERT INTO users (username, password, nama_lengkap, jabatan, status) VALUES ('admin', ?, 'Administrator PKKM', 'Administrator Sistem', 'aktif')");
        $insertAdmin->execute([$hash]);
        $adminId = $pdo->lastInsertId();

        $roleAdminId = $pdo->query("SELECT id FROM roles WHERE kode_role = 'admin'")->fetchColumn();
        $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$adminId, $roleAdminId]);
    }

    // 2. Insert or get Versi Instrumen 1.0
    $vStmt = $pdo->prepare("SELECT id FROM instrumen_versi WHERE nomor_versi = '1.0'");
    $vStmt->execute();
    $versiId = $vStmt->fetchColumn();

    if (!$versiId) {
        $insV = $pdo->prepare("INSERT INTO instrumen_versi (nomor_versi, nama_versi, deskripsi, status, created_by) VALUES ('1.0', 'Instrumen Standar Kemenag (5 Komponen)', 'Instrumen resmi Penilaian Kinerja Kepala Madrasah hasil audit Excel template Haris1', 'aktif', ?)");
        $insV->execute([$adminId]);
        $versiId = $pdo->lastInsertId();
    }

    // 3. Parse Komponen, Tugas Utama, and Indikator
    $compMap = [
        1 => 'Usaha Pengembangan Madrasah',
        2 => 'Pelaksanaan Tugas Manajerial',
        3 => 'Pengembangan Kewirausahaan',
        4 => 'Supervisi Kepada Guru dan Tenaga Kependidikan',
        5 => 'Hasil Kinerja Kepala Madrasah'
    ];

    $compDbIds = [];
    foreach ($compMap as $num => $nama) {
        $cStmt = $pdo->prepare("SELECT id FROM komponen_penilaian WHERE versi_id = ? AND nomor_komponen = ?");
        $cStmt->execute([$versiId, $num]);
        $cId = $cStmt->fetchColumn();
        if (!$cId) {
            $insC = $pdo->prepare("INSERT INTO komponen_penilaian (versi_id, nomor_komponen, nama_komponen, urutan) VALUES (?, ?, ?, ?)");
            $insC->execute([$versiId, $num, $nama, $num]);
            $cId = $pdo->lastInsertId();
        }
        $compDbIds[$num] = $cId;
    }

    $curCompNum = 0;
    $curTugasId = null;
    $curTugasKode = null;
    $indicatorCount = 0;

    foreach ($rows as $rIdx => $cols) {
        $a = $cols['A'] ?? '';
        $b = $cols['B'] ?? '';
        $c = $cols['C'] ?? '';
        $d = $cols['D'] ?? '';
        $e = $cols['E'] ?? '';
        $j = $cols['J'] ?? '';

        if (strpos($b, '1. USAHA PENGEMBANGAN') !== false) { $curCompNum = 1; continue; }
        if (strpos($b, '2. PELAKSANAAN TUGAS') !== false) { $curCompNum = 2; continue; }
        if (strpos($b, '3. PENGEMBANGAN KEWIRAUSAHAAN') !== false) { $curCompNum = 3; continue; }
        if (strpos($b, '4. SUPERVISI') !== false) { $curCompNum = 4; continue; }
        if (strpos($b, 'HASIL KINERJA') !== false && strpos($b, 'PEROLEHAN') === false) { $curCompNum = 5; continue; }

        // Check if row is Tugas Utama header (e.g. 1.1, 2.1, 5.1)
        if (preg_match('/^[1-5]\.[0-9]+$/', $a)) {
            $curTugasKode = $a;
            $tugasNama = $b;
            $kompDbId = $compDbIds[$curCompNum];

            $tStmt = $pdo->prepare("SELECT id FROM tugas_utama WHERE komponen_id = ? AND kode_tugas = ?");
            $tStmt->execute([$kompDbId, $curTugasKode]);
            $curTugasId = $tStmt->fetchColumn();
            if (!$curTugasId) {
                $insT = $pdo->prepare("INSERT INTO tugas_utama (komponen_id, kode_tugas, nama_tugas, urutan) VALUES (?, ?, ?, ?)");
                $urutanTugas = (int)explode('.', $curTugasKode)[1];
                $insT->execute([$kompDbId, $curTugasKode, $tugasNama, $urutanTugas]);
                $curTugasId = $pdo->lastInsertId();
            }
        }

        // Check if row has sub indicator number in C
        if ($c !== '' && is_numeric(rtrim($c, '.')) && $curTugasId) {
            $subNo = rtrim($c, '.');
            $kodeInd = $curTugasKode . '.' . $subNo;

            $iStmt = $pdo->prepare("SELECT id FROM indikator_kerja WHERE tugas_id = ? AND nomor_sub = ?");
            $iStmt->execute([$curTugasId, $subNo]);
            $indId = $iStmt->fetchColumn();

            if (!$indId) {
                $insI = $pdo->prepare("INSERT INTO indikator_kerja (tugas_id, kode_indikator, nomor_sub, uraian_indikator, data_kinerja_diharapkan, bukti_autentik, skor_maksimal, urutan) VALUES (?, ?, ?, ?, ?, ?, 4, ?)");
                $insI->execute([$curTugasId, $kodeInd, $subNo, $d, $e, $j, (int)$subNo]);
                $indicatorCount++;
            }
        }
    }

    $pdo->commit();
    echo "Import Master Instrumen selesai!\n";
    echo "Total Indikator yang diimpor: $indicatorCount butir.\n";

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo "Error importing: " . $e->getMessage() . "\n";
}

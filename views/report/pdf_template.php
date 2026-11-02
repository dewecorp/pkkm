<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekapitulasi Penilaian Kinerja Kepala Madrasah</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 20px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .title { font-size: 14px; font-weight: bold; margin-bottom: 2px; }
        .subtitle { font-size: 11px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        th, td { border: 1px solid #94a3b8; padding: 6px 8px; }
        th { background-color: #f1f5f9; text-transform: uppercase; font-size: 10px; }
        .no-border td { border: none; padding: 2px 4px; }
        .footer-table td { border: none; padding-top: 30px; }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="title">REKAPITULASI PENILAIAN KINERJA EMPAT TAHUNAN KEPALA MADRASAH</div>
        <div class="subtitle">KANTOR KEMENTERIAN AGAMA KABUPATEN JEPARA</div>
    </div>

    <table class="no-border" style="margin-bottom: 15px;">
        <tr><td style="width: 140px;">Nama Madrasah</td><td>: <strong><?= htmlspecialchars($periode['nama_madrasah']) ?></strong></td></tr>
        <tr><td>NPSN / NSM</td><td>: <?= htmlspecialchars($periode['npsn'] ?? '-') ?> / <?= htmlspecialchars($periode['nsm'] ?? '-') ?></td></tr>
        <tr><td>Nama Kepala Madrasah</td><td>: <strong><?= htmlspecialchars($periode['nama_kamad']) ?></strong></td></tr>
        <tr><td>NIP / NPK</td><td>: <?= htmlspecialchars($periode['kamad_nip'] ?? '-') ?> / <?= htmlspecialchars($periode['kamad_npk'] ?? '-') ?></td></tr>
        <tr><td>Tahun Penilaian</td><td>: <?= htmlspecialchars($periode['tahun_penilaian']) ?></td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;" class="text-center">No</th>
                <th>Jenis Penilaian</th>
                <th style="width: 60px;" class="text-center">Bobot</th>
                <th style="width: 80px;" class="text-right">Skor Rata-Rata</th>
                <th style="width: 100px;" class="text-right">Skor Tertimbang</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $defGroups = [
                ['no' => 1, 'kode' => 'KS', 'nama' => 'Hasil Penilaian Kepala Seksi', 'bobot' => 4],
                ['no' => 2, 'kode' => 'KY', 'nama' => 'Hasil Penilaian Ketua Yayasan', 'bobot' => 2],
                ['no' => 3, 'kode' => 'WASMAD', 'nama' => 'Hasil Rata-Rata Penilaian Pengawas Madrasah', 'bobot' => 1],
                ['no' => 4, 'kode' => 'GURU', 'nama' => 'Hasil Rata-Rata Penilaian Guru', 'bobot' => 1],
                ['no' => 5, 'kode' => 'TENDIK', 'nama' => 'Hasil Rata-Rata Penilaian Tenaga Kependidikan', 'bobot' => 1],
                ['no' => 6, 'kode' => 'KOMITE', 'nama' => 'Hasil Rata-Rata Penilaian Komite Madrasah', 'bobot' => 1],
            ];
            foreach ($defGroups as $g):
                $grpData = $rekap['groups'][$g['kode']] ?? null;
                $avg = $grpData['rata_rata'] ?? 0.0;
                $weighted = round(($avg * $g['bobot']) / 10.0, 2);
            ?>
                <tr>
                    <td class="text-center"><?= $g['no'] ?>.</td>
                    <td><?= $g['nama'] ?></td>
                    <td class="text-center"><?= $g['bobot'] ?></td>
                    <td class="text-right"><?= number_format($avg, 2) ?></td>
                    <td class="text-right font-bold"><?= number_format($weighted, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="4" class="text-right">HASIL PENILAIAN EMPAT TAHUNAN (NKTE):</td>
                <td class="text-right" style="font-size: 13px;"><?= number_format($rekap['nkte'], 2) ?></td>
            </tr>
            <tr style="background-color: #f8fafc; font-weight: bold;">
                <td colspan="4" class="text-right">PREDIKAT PENILAIAN KINERJA:</td>
                <td class="text-right" style="font-size: 12px; color: #047857;"><?= htmlspecialchars($rekap['predikat']) ?></td>
            </tr>
        </tbody>
    </table>

    <div style="font-size: 10px; color: #475569; margin-top: 5px;">
        <em>* Rumus Pembobotan Resmi: NKTE = (4×KS + 2×KY + PM + GR + TK + KM) / 10</em>
    </div>

    <!-- Tanda Tangan Sesuai Excel -->
    <table class="footer-table" style="margin-top: 40px;">
        <tr>
            <td style="width: 50%; text-align: center;">
                Mengetahui,<br>
                Ketua Tim Penilai
                <div style="height: 60px;"></div>
                <strong>( ________________________ )</strong><br>
                NIP. ........................................
            </td>
            <td style="width: 50%; text-align: center;">
                Jepara, <?= date('d F Y') ?><br>
                Kepala Madrasah Yang Dinilai
                <div style="height: 60px;"></div>
                <strong><?= htmlspecialchars($periode['nama_kamad']) ?></strong><br>
                NIP. <?= htmlspecialchars($periode['kamad_nip'] ?? '-') ?>
            </td>
        </tr>
    </table>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Form Penilaian — <?= htmlspecialchars($penilaian['kode_kelompok']) ?> — <?= htmlspecialchars($penilaian['nama_penilai']) ?></title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #f1f5f9;
            line-height: 1.45;
        }

        /* ── Action Toolbar (Hidden on print) ───────────────── */
        .no-print-bar {
            position: sticky;
            top: 0;
            z-index: 999;
            background: #1e293b;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .no-print-bar .title { font-weight: 700; font-size: 13px; }
        .no-print-bar .btns { display: flex; gap: 8px; }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 14px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            text-decoration: none;
        }
        .btn-print { background: #059669; color: #fff; }
        .btn-print:hover { background: #047857; }
        .btn-close { background: #475569; color: #fff; }
        .btn-close:hover { background: #334155; }

        /* ── Sheet Container ────────────────────────────────── */
        .sheet {
            background: #fff;
            max-width: 900px;
            margin: 24px auto;
            padding: 32px 36px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-radius: 4px;
        }

        /* ── Header Dokumen ─────────────────────────────────── */
        .doc-header {
            text-align: center;
            padding-bottom: 12px;
            border-bottom: 2px solid #1e6347;
            margin-bottom: 16px;
        }
        .doc-header h1 { font-size: 14px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .doc-header h2 { font-size: 12px; font-weight: 700; color: #1e6347; margin-top: 2px; }
        .doc-header p  { font-size: 10px; color: #64748b; margin-top: 2px; }

        /* ── Info Penilaian ─────────────────────────────────── */
        .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .meta-table td { padding: 3px 6px; font-size: 11px; vertical-align: top; }
        .meta-table td.label { width: 150px; font-weight: 600; color: #475569; }
        .meta-table td.colon { width: 10px; }
        .meta-table td.val   { font-weight: 600; color: #0f172a; }

        /* ── Ringkasan Komponen ─────────────────────────────── */
        .section-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1e6347;
            margin-bottom: 6px;
        }
        table.tbl {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        table.tbl th, table.tbl td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
            font-size: 10px;
        }
        table.tbl th {
            background: #1e6347;
            color: #fff;
            text-align: left;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.5px;
        }
        table.tbl th.c, table.tbl td.c { text-align: center; }
        table.tbl th.r, table.tbl td.r { text-align: right; }
        tr.komponen-hdr td {
            background: #eef7f1;
            font-weight: 700;
            color: #17503a;
            font-size: 10px;
        }
        tr.tugas-hdr td {
            background: #f8fafc;
            font-weight: 600;
            color: #334155;
            font-size: 9.5px;
        }
        tr.total-row td {
            background: #1e6347;
            color: #fff;
            font-weight: 700;
        }

        /* Skor highlight */
        .skor-box {
            display: inline-block;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
        }
        .skor-1 { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
        .skor-2 { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
        .skor-3 { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .skor-4 { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
        .skor-none { color: #94a3b8; font-style: italic; }

        /* Catatan */
        .note-box {
            background: #f1f5f9;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            color: #475569;
            margin-top: 3px;
        }

        /* Tanda tangan */
        .signature-table { width: 100%; border-collapse: collapse; margin-top: 30px; page-break-inside: avoid; }
        .signature-table td { width: 50%; text-align: center; vertical-align: top; font-size: 11px; padding: 0 20px; }
        .signature-space { height: 60px; }
        .sig-name { font-weight: 700; border-bottom: 1px solid #0f172a; display: inline-block; padding-bottom: 2px; }

        /* ── Media Print ────────────────────────────────────── */
        @media print {
            body { background: #fff; font-size: 9.5px; }
            .no-print-bar { display: none !important; }
            .sheet {
                max-width: 100%;
                margin: 0;
                padding: 10mm 12mm;
                box-shadow: none;
                border-radius: 0;
            }
            @page {
                size: A4 portrait;
                margin: 10mm;
            }
            tr.komponen-hdr, tr.tugas-hdr {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            table.tbl th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .skor-box {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>
<body>

<!-- ── Action Bar (desktop preview) ────────────────────────── -->
<div class="no-print-bar">
    <div class="title">
        Pratinjau Formulir Penilaian — <?= htmlspecialchars($penilaian['nama_kelompok']) ?>
    </div>
    <div class="btns">
        <button class="btn btn-print" onclick="window.print()">
            &#128438; Cetak Sekarang
        </button>
        <button class="btn btn-close" onclick="window.close()">
            &#x2715; Tutup
        </button>
    </div>
</div>

<!-- ── Halaman Dokumen ─────────────────────────────────────── -->
<div class="sheet">

    <!-- Header Dokumen -->
    <div class="doc-header">
        <h1>Formulir Penilaian Kinerja Kepala Madrasah</h1>
        <h2>Kelompok <?= htmlspecialchars($penilaian['kode_kelompok']) ?> — <?= htmlspecialchars($penilaian['nama_kelompok']) ?></h2>
        <p>Sistem Informasi Penilaian Kinerja Kepala Madrasah (PKKM)</p>
    </div>

    <!-- Info Penilaian -->
    <table class="meta-table">
        <tr>
            <td class="label">Nama Madrasah</td><td class="colon">:</td>
            <td class="val"><?= htmlspecialchars($penilaian['nama_madrasah']) ?></td>
            <td class="label">Periode Penilaian</td><td class="colon">:</td>
            <td class="val"><?= htmlspecialchars($penilaian['nama_periode']) ?> (<?= htmlspecialchars($penilaian['tahun_penilaian']) ?>)</td>
        </tr>
        <tr>
            <td class="label">Kepala Madrasah</td><td class="colon">:</td>
            <td class="val"><?= htmlspecialchars($penilaian['nama_kamad']) ?></td>
            <td class="label">Jenis Penilaian</td><td class="colon">:</td>
            <td class="val"><?= ucfirst(str_replace('_',' ', htmlspecialchars($penilaian['jenis_penilaian']))) ?></td>
        </tr>
        <tr>
            <td class="label">Penilai</td><td class="colon">:</td>
            <td class="val"><?= htmlspecialchars($penilaian['nama_penilai']) ?></td>
            <td class="label">Status Penilaian</td><td class="colon">:</td>
            <td class="val"><?= ucfirst(htmlspecialchars($penilaian['status'])) ?> (Bobot: <?= (int)$penilaian['bobot_default'] ?>)</td>
        </tr>
    </table>

    <!-- Ringkasan Komponen -->
    <div class="section-title">Ringkasan Capaian Per Komponen</div>
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:30px" class="c">No</th>
                <th>Komponen</th>
                <th style="width:85px" class="c">Butir Terisi</th>
                <th style="width:95px" class="c">Skor Perolehan</th>
                <th style="width:80px" class="c">Skor Maks</th>
                <th style="width:55px" class="c">% Capaian</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tree as $kData):
                $k   = $kData['komponen'];
                $pct = $kData['skor_maks'] > 0 ? round($kData['skor_perolehan'] / $kData['skor_maks'] * 100) : 0;
            ?>
            <tr>
                <td class="c"><?= $k['nomor_komponen'] ?></td>
                <td><strong><?= htmlspecialchars($k['nama_komponen']) ?></strong></td>
                <td class="c"><?= $kData['terisi'] ?> / <?= $kData['total_ind'] ?></td>
                <td class="c font-bold"><?= $kData['skor_perolehan'] ?></td>
                <td class="c"><?= $kData['skor_maks'] ?></td>
                <td class="c font-bold"><?= $pct ?>%</td>
            </tr>
            <?php endforeach; ?>
            <?php $pctTotal = $total_skor_maks > 0 ? round($total_skor_perolehan / $total_skor_maks * 100) : 0; ?>
            <tr class="total-row">
                <td colspan="2" class="r">TOTAL SKOR:</td>
                <td class="c"><?= $terisi_count ?> / <?= $total_indikator ?></td>
                <td class="c"><?= $total_skor_perolehan ?></td>
                <td class="c"><?= $total_skor_maks ?></td>
                <td class="c"><?= $pctTotal ?>%</td>
            </tr>
            <tr style="background:#f1f5f9;font-weight:700;">
                <td colspan="3" class="r">NILAI AKHIR (Konversi 100):</td>
                <td class="c" style="font-size:12px;color:#059669;"><?= $penilaian['nilai_akhir'] !== null ? number_format($penilaian['nilai_akhir'], 2) : '—' ?></td>
                <td class="r">PREDIKAT:</td>
                <td class="c" style="font-size:11px;color:#059669;"><?= htmlspecialchars($penilaian['predikat'] ?? '—') ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Rincian Skor Butir Indikator -->
    <div class="section-title" style="margin-top:18px;">Rincian Butir Penilaian Kinerja</div>
    <table class="tbl">
        <thead>
            <tr>
                <th style="width:26px" class="c">No</th>
                <th style="width:75px">Kode</th>
                <th>Uraian Indikator</th>
                <th style="width:160px">Data Kinerja Diharapkan</th>
                <th style="width:120px">Bukti Autentik</th>
                <th style="width:45px" class="c">Skor</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; ?>
            <?php foreach ($tree as $kData):
                $k = $kData['komponen'];
            ?>
            <tr class="komponen-hdr">
                <td colspan="6">
                    KOMPONEN <?= $k['nomor_komponen'] ?>: <?= htmlspecialchars(strtoupper($k['nama_komponen'])) ?>
                    &nbsp;&middot;&nbsp; Skor: <?= $kData['skor_perolehan'] ?>/<?= $kData['skor_maks'] ?>
                    &nbsp;&middot;&nbsp; Terisi: <?= $kData['terisi'] ?>/<?= $kData['total_ind'] ?> butir
                </td>
            </tr>
            <?php foreach ($kData['tugas'] as $tData):
                $t = $tData['tugas'];
            ?>
            <tr class="tugas-hdr">
                <td colspan="6">
                    Tugas <?= htmlspecialchars($t['kode_tugas']) ?>: <?= htmlspecialchars($t['nama_tugas']) ?>
                </td>
            </tr>
            <?php foreach ($tData['indikator'] as $ind): ?>
            <tr>
                <td class="c"><?= $no++ ?></td>
                <td><strong><?= htmlspecialchars($ind['kode_indikator']) ?></strong></td>
                <td>
                    <?= htmlspecialchars($ind['uraian_indikator']) ?>
                    <?php if (!empty($ind['catatan_penilai'])): ?>
                    <div class="note-box">
                        <strong>Catatan:</strong> <?= htmlspecialchars($ind['catatan_penilai']) ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td style="font-size:9.5px;color:#475569;"><?= nl2br(htmlspecialchars($ind['data_kinerja_diharapkan'])) ?></td>
                <td style="font-size:9.5px;color:#475569;font-style:italic;"><?= htmlspecialchars($ind['bukti_autentik']) ?></td>
                <td class="c">
                    <?php if ($ind['skor'] !== null): ?>
                    <span class="skor-box skor-<?= (int)$ind['skor'] ?>"><?= (int)$ind['skor'] ?></span>
                    <?php else: ?>
                    <span class="skor-none">—</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                Kepala Seksi Pendidikan Madrasah
                <div class="signature-space"></div>
                <div class="sig-name">( ________________________ )</div><br>
                <span style="font-size:10px;color:#64748b;">NIP. ........................................</span>
            </td>
            <td>
                Jepara, <?= date('d F Y') ?><br>
                Penilai Kinerja
                <div class="signature-space"></div>
                <div class="sig-name"><?= htmlspecialchars($penilaian['nama_penilai']) ?></div><br>
                <span style="font-size:10px;color:#64748b;">Kelompok: <?= htmlspecialchars($penilaian['nama_kelompok']) ?></span>
            </td>
        </tr>
    </table>

</div>

<script>
    // Otomatis buka dialog print begitu halaman selesai dimuat
    window.addEventListener('load', function() {
        setTimeout(function() {
            window.print();
        }, 400);
    });
</script>
</body>
</html>

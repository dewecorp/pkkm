<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-5">

<!-- ── Page Header ──────────────────────────────────────────── -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Form Penilaian Penilai</h1>
        <p class="text-sm text-slate-500 mt-0.5">Monitor progres pengisian, komponen terisi & nilai per penilai</p>
    </div>
    <?php if ($periodeAktif): ?>
    <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
        <span><?= e($periodeAktif['nama_periode']) ?> · <?= e($periodeAktif['nama_madrasah']) ?></span>
    </div>
    <?php endif; ?>
</div>

<!-- ── Filter Periode ──────────────────────────────────────── -->
<?php if (!empty($periodeList)): ?>
<div class="card p-4">
    <form method="GET" class="flex flex-wrap items-end gap-3">
        <div class="flex-1 min-w-[200px]">
            <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1 block">Pilih Periode</label>
            <select name="periode_id" onchange="this.form.submit()"
                    class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500 bg-white">
                <?php foreach ($periodeList as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $p['id'] == $periodeId ? 'selected' : '' ?>>
                    <?= e($p['nama_periode']) ?> (<?= e($p['tahun_penilaian']) ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- ── Tabel Daftar Form ───────────────────────────────────── -->
<?php if (!empty($list)): ?>

<?php
// Hitung ringkasan
$totalPenilai  = count($list);
$selesai       = count(array_filter($list, fn($r) => in_array($r['status'], ['submitted','locked'])));
$belumMulai    = count(array_filter($list, fn($r) => (int)$r['terisi_indikator'] === 0));
?>
<!-- Mini stat strip -->
<div class="grid grid-cols-3 gap-3">
    <?php foreach ([
        ['Total Penilai',  $totalPenilai,           'fa-users',        'brand'],
        ['Selesai Submit', $selesai,                 'fa-circle-check', 'emerald'],
        ['Belum Mulai',    $belumMulai,              'fa-clock',        'amber'],
    ] as [$label, $val, $icon, $color]): ?>
    <div class="card p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0
            <?= $color==='emerald'?'bg-emerald-50':'($color==="amber"?"bg-amber-50":"bg-brand-50")' ?>">
            <i class="fa-solid <?= $icon ?> text-sm
                <?= $color==='emerald'?'text-emerald-600':($color==='amber'?'text-amber-600':'text-brand-600') ?>"></i>
        </div>
        <div>
            <div class="text-[10px] text-slate-400 font-medium"><?= $label ?></div>
            <div class="text-xl font-black text-slate-800 leading-none"><?= $val ?></div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50 text-left">
                    <th class="px-4 py-3 font-semibold text-slate-600">Penilai</th>
                    <th class="px-4 py-3 font-semibold text-slate-600">Kelompok</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 text-center">Status</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 text-center">Komponen Terisi</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 text-center">Butir Terisi</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 text-center">Nilai Akhir</th>
                    <th class="px-4 py-3 font-semibold text-slate-600 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                <?php foreach ($list as $row):
                    $isDone      = in_array($row['status'], ['submitted','locked']);
                    $totalInd    = (int)$row['total_indikator'];
                    $terisiInd   = (int)$row['terisi_indikator'];
                    $pctInd      = $totalInd > 0 ? round($terisiInd/$totalInd*100) : 0;
                    $kompTerisi  = (int)$row['komponen_terisi'];
                    $totalKomp   = (int)$row['total_komponen'];
                    $pctKomp     = $totalKomp > 0 ? round($kompTerisi/$totalKomp*100) : 0;
                    $stMap = ['draft'=>['Berjalan','amber'],'submitted'=>['Tersimpan','emerald'],'locked'=>['Terkunci','slate']];
                    [$stLabel,$stColor] = $stMap[$row['status']] ?? [$row['status'],'slate'];
                    $pw = predikat_warna($row['predikat'] ?? '');
                ?>
                <tr class="hover:bg-slate-50/60 transition">
                    <td class="px-4 py-3 font-semibold text-slate-800"><?= e($row['nama_penilai']) ?></td>
                    <td class="px-4 py-3">
                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-brand-50 text-brand-700 border border-brand-200">
                            <?= e($row['kode_kelompok']) ?>
                        </span>
                        <div class="text-[11px] text-slate-500 mt-0.5"><?= e($row['nama_kelompok']) ?></div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold
                            <?= $stColor==='emerald'?'bg-emerald-50 text-emerald-700':($stColor==='amber'?'bg-amber-50 text-amber-700':'bg-slate-100 text-slate-600') ?>">
                            <?= $stLabel ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="font-bold text-slate-800"><?= $kompTerisi ?>/<?= $totalKomp ?></div>
                        <div class="w-24 mx-auto mt-1.5 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-1.5 rounded-full bg-brand-500 transition-all" style="width:<?= $pctKomp ?>%"></div>
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5"><?= $pctKomp ?>%</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="font-bold text-slate-800"><?= $terisiInd ?>/<?= $totalInd ?></div>
                        <div class="w-24 mx-auto mt-1.5 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-1.5 rounded-full <?= $isDone?'bg-emerald-500':'bg-brand-400' ?> transition-all" style="width:<?= $pctInd ?>%"></div>
                        </div>
                        <div class="text-[10px] text-slate-400 mt-0.5"><?= $pctInd ?>%</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <?php if ($row['nilai_akhir'] !== null): ?>
                        <div class="text-lg font-black text-slate-800 leading-none"><?= number_format($row['nilai_akhir'],2) ?></div>
                        <?php if (!empty($row['predikat'])): ?>
                        <span class="inline-block mt-1 px-2 py-0.5 rounded text-[9px] font-bold border"
                              style="background:<?= $pw['bg'] ?>;color:<?= $pw['fg'] ?>;border-color:<?= $pw['fg'] ?>33;">
                            <?= e($row['predikat']) ?>
                        </span>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="text-slate-300">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1.5 flex-wrap">
                            <a href="<?= base_url('form-penilaian/detail/' . $row['id']) ?>"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-brand-50 hover:bg-brand-600 text-brand-600 hover:text-white text-[10px] font-bold transition border border-brand-200">
                                <i class="fa-solid fa-eye text-[9px]"></i><span>Detail</span>
                            </a>
                            <a href="<?= base_url('form-penilaian/cetak/' . $row['id']) ?>" target="_blank"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-700 text-slate-600 hover:text-white text-[10px] font-bold transition border border-slate-200">
                                <i class="fa-solid fa-print text-[9px]"></i><span>Cetak</span>
                            </a>
                            <a href="<?= base_url('form-penilaian/excel/' . $row['id']) ?>"
                               class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-600 text-emerald-600 hover:text-white text-[10px] font-bold transition border border-emerald-200">
                                <i class="fa-solid fa-file-excel text-[9px]"></i><span>Excel</span>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php else: ?>
<div class="card p-16 text-center">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 bg-slate-50 text-slate-400">
        <i class="fa-solid fa-clipboard-list text-3xl"></i>
    </div>
    <h3 class="font-bold text-slate-700 text-sm">Belum Ada Data Form Penilaian</h3>
    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Pilih periode penilaian di atas atau tunggu penilai mulai mengisi formulir.</p>
</div>
<?php endif; ?>

</div><!-- /space-y-5 -->

<?php view('layout.footer'); ?>

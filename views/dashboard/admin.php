<?php view('layout.header', ['title' => $title]); ?>

<?php
// Summary stat per kelompok
$groupDefs = [
    'KS'     => ['label'=>'Kepala Seksi',    'bobot'=>4, 'icon'=>'fa-user-shield',  'color'=>'#1e6347','bg'=>'rgba(30,99,71,0.10)'],
    'KY'     => ['label'=>'Ketua Yayasan',   'bobot'=>2, 'icon'=>'fa-building',     'color'=>'#0d9488','bg'=>'rgba(13,148,136,0.10)'],
    'WASMAD' => ['label'=>'Pengawas',        'bobot'=>1, 'icon'=>'fa-binoculars',   'color'=>'#06b6d4','bg'=>'rgba(6,182,212,0.10)'],
    'GURU'   => ['label'=>'Guru',            'bobot'=>1, 'icon'=>'fa-chalkboard-user','color'=>'#10b981','bg'=>'rgba(16,185,129,0.10)'],
    'TENDIK' => ['label'=>'Tenaga Kependidikan','bobot'=>1,'icon'=>'fa-id-card',    'color'=>'#f59e0b','bg'=>'rgba(245,158,11,0.10)'],
    'KOMITE' => ['label'=>'Komite',          'bobot'=>1, 'icon'=>'fa-handshake',    'color'=>'#ef4444','bg'=>'rgba(239,68,68,0.10)'],
];
?>

<div class="space-y-6">

<!-- ── Page Header ─────────────────────────────────────── -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Dashboard Administrator</h1>
        <p class="text-sm text-slate-500 mt-0.5">
            Pantau progres penilaian kinerja kepala madrasah secara real-time
        </p>
    </div>
    <?php if ($periode): ?>
    <div class="flex items-center space-x-2 px-3.5 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
        <span><?= e($periode['nama_periode']) ?></span>
    </div>
    <?php endif; ?>
</div>

<!-- ── KPI Cards ───────────────────────────────────────── -->
<?php
$predikat = ($rekap && $rekap['nkte'] > 0) ? ($rekap['predikat'] ?? '') : '';
$kpis = [
    ['label'=>'Total Pengguna',    'value'=>$stats['total_users'],
     'icon'=>'fa-users',           'color'=>'#1e6347','grad'=>'linear-gradient(135deg,#4c9d75,#1a573f)','badge'=>''],
    ['label'=>'Periode Penilaian', 'value'=>$stats['total_periode'],
     'icon'=>'fa-calendar-check',  'color'=>'#10b981','grad'=>'linear-gradient(135deg,#34d399,#059669)','badge'=>''],
    ['label'=>'Penilai Aktif',     'value'=>$stats['total_penilai'],
     'icon'=>'fa-user-check',      'color'=>'#f59e0b','grad'=>'linear-gradient(135deg,#fbbf24,#d97706)','badge'=>''],
    ['label'=>'Skor NKTE',
     'value'=>($rekap && $rekap['nkte'] > 0) ? number_format($rekap['nkte'],2) : '—',
     'icon'=>'fa-trophy',          'color'=>'#ef4444','grad'=>'linear-gradient(135deg,#fb7185,#e11d48)',
     'badge'=>$predikat],
];
?>
<?php view('partials.stat-cards', ['items' => $kpis]); ?>

<?php view('partials.mobile-menu-grid', ['nav' => nav_items()]); ?>

<!-- ── Main Grid: Kelompok + Chart ────────────────────── -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    <!-- Kelompok Penilai Table (2/3) -->
    <div class="card xl:col-span-2 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-slate-800 text-sm">Progres 6 Kelompok Penilai</h3>
                <p class="text-xs text-slate-400 mt-0.5">Status kelengkapan formulir tiap kelompok</p>
            </div>
            <a href="<?= base_url('laporan') ?>" class="text-xs text-brand-600 font-semibold hover:text-brand-700 flex items-center space-x-1">
                <span>Rekap Lengkap</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="divide-y divide-slate-50">
            <?php if ($rekap && !empty($rekap['groups'])): ?>
            <?php foreach ($rekap['groups'] as $kode => $grp): ?>
            <?php $def = $groupDefs[$kode] ?? []; ?>
            <div class="px-5 py-4 flex items-center gap-4 hover:bg-slate-50/70 transition">
                <!-- Icon -->
                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0"
                     style="background:<?= $def['bg'] ?? '#f1f5f9' ?>;">
                    <i class="fa-solid <?= $def['icon'] ?? 'fa-user' ?> text-xs" style="color:<?= $def['color'] ?? '#64748b' ?>;"></i>
                </div>

                <!-- Info -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-xs font-semibold text-slate-700"><?= e($grp['kelompok']['nama_kelompok']) ?></span>
                        <span class="text-xs font-bold" style="color:<?= $def['color'] ?? '#64748b' ?>;">
                            <?= number_format($grp['rata_rata'],2) ?>
                        </span>
                    </div>
                    <!-- Progress bar -->
                    <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                        <?php $pct = $grp['total_penilai'] > 0 ? ($grp['penilai_selesai']/$grp['total_penilai'])*100 : 0; ?>
                        <div class="h-1.5 rounded-full transition-all duration-700"
                             style="width:<?= $pct ?>%; background:<?= $def['color'] ?? '#1e6347' ?>;"></div>
                    </div>
                    <div class="flex justify-between mt-1">
                        <span class="text-[10px] text-slate-400"><?= $grp['penilai_selesai'] ?> / <?= $grp['total_penilai'] ?> penilai selesai</span>
                        <?php if ($grp['is_lengkap']): ?>
                        <span class="text-[10px] font-bold text-emerald-600"><i class="fa-solid fa-circle-check mr-0.5"></i>Lengkap</span>
                        <?php else: ?>
                        <span class="text-[10px] font-bold text-amber-500"><i class="fa-solid fa-clock mr-0.5"></i>Belum Lengkap</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Bobot badge -->
                <div class="text-center shrink-0 w-10">
                    <div class="text-[10px] text-slate-400">Bobot</div>
                    <div class="font-black text-slate-700"><?= (int)$grp['kelompok']['bobot_default'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- NKTE Gauge (1/3) -->
    <div class="card flex flex-col">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800 text-sm">Hasil NKTE</h3>
            <p class="text-xs text-slate-400 mt-0.5">Nilai Kinerja Empat Tahunan</p>
        </div>
        <div class="flex-1 flex flex-col items-center justify-center p-6 space-y-5">
            <!-- Big Score -->
            <div class="text-center">
                <div class="text-6xl font-black tracking-tight" style="background:linear-gradient(135deg,#1e6347,#17503a);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                    <?= ($rekap && $rekap['nkte'] > 0) ? number_format($rekap['nkte'],2) : '—' ?>
                </div>
                <div class="text-xs text-slate-400 font-medium mt-1">
                    <?= ($rekap && $rekap['nkte'] > 0) ? 'dari skala 100' : 'Menunggu penilai mengisi instrumen' ?>
                </div>
            </div>

            <!-- Predikat Pill -->
            <?php if ($rekap && $rekap['nkte'] > 0): ?>
            <?php
            $predColor = ['Amat Baik'=>'#059669','Baik'=>'#2563eb','Cukup'=>'#d97706','Sedang'=>'#9333ea','Kurang'=>'#dc2626'];
            $predBg    = ['Amat Baik'=>'#ecfdf5','Baik'=>'#eff6ff','Cukup'=>'#fffbeb','Sedang'=>'#faf5ff','Kurang'=>'#fef2f2'];
            $pColor = $predColor[$rekap['predikat']] ?? '#1e6347';
            $pBg    = $predBg[$rekap['predikat']] ?? '#eef7f1';
            ?>
            <div class="px-5 py-2 rounded-full font-bold text-sm" style="background:<?= $pBg ?>;color:<?= $pColor ?>;border:1px solid <?= $pColor ?>22;">
                <?= e($rekap['predikat']) ?>
            </div>
            <?php else: ?>
            <div class="px-4 py-1.5 rounded-full font-semibold text-xs text-amber-700 bg-amber-50 border border-amber-200">
                <i class="fa-solid fa-hourglass-half mr-1 text-[10px]"></i>Belum Ada Nilai
            </div>
            <?php endif; ?>

            <!-- Formula Note -->
            <div class="text-center text-[10px] text-slate-400 bg-slate-50 rounded-xl px-4 py-3 leading-relaxed">
                NKTE = (4·KS + 2·KY + PM + GR + TK + KM) / 10
            </div>

            <!-- Component Breakdown -->
            <?php if ($rekap): ?>
            <div class="w-full space-y-2">
                <?php foreach ($rekap['groups'] as $kode => $grp): ?>
                <?php $def = $groupDefs[$kode] ?? []; $avg = $grp['rata_rata']; ?>
                <div class="flex items-center space-x-2 text-[11px]">
                    <span class="w-14 font-semibold text-slate-500"><?= $kode ?></span>
                    <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-1.5 rounded-full" style="width:<?= $avg ?>%;background:<?= $def['color'] ?? '#1e6347' ?>;"></div>
                    </div>
                    <span class="w-9 text-right font-bold text-slate-600"><?= number_format($avg,1) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</div><!-- /space-y-6 -->

<?php view('layout.footer'); ?>

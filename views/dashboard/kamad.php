<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Dashboard Kepala Madrasah</h1>
        <p class="text-sm text-slate-500 mt-0.5">Pantau proses penilaian dan kelola bukti autentik kinerja Anda</p>
    </div>
    <a href="<?= base_url('bukti') ?>"
       class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white shadow transition"
       style="background:linear-gradient(135deg,#1e6347,#17503a);">
        <i class="fa-solid fa-cloud-arrow-up"></i>
        <span>Unggah Bukti Kinerja</span>
    </a>
</div>

<!-- ── KPI Cards Ringkasan ─────────────────────────────────── -->
<?php
$totalPenilai = 0;
$selesaiPenilai = 0;
if (!empty($rekap['groups'])) {
    foreach ($rekap['groups'] as $grp) {
        $totalPenilai += (int)($grp['total_penilai'] ?? 0);
        $selesaiPenilai += (int)($grp['penilai_selesai'] ?? 0);
    }
}
$pctKelengkapan = $totalPenilai > 0 ? round(($selesaiPenilai / $totalPenilai) * 100) : 0;
$nkteValue = (!empty($rekap['nkte']) && $rekap['nkte'] > 0) ? number_format($rekap['nkte'], 2) : '—';

$kamadKpis = [
    ['label'=>'Status Periode',
     'value'=>$periode ? ucfirst($periode['status']) : 'Belum Ada',
     'icon'=>'fa-calendar-check', 'color'=>'#1e6347', 'grad'=>'linear-gradient(135deg,#4c9d75,#1a573f)',
     'badge'=>$periode ? 'TA '.$periode['tahun_penilaian'] : '', 'small'=>true],
    ['label'=>'Skor NKTE', 'value'=>$nkteValue,
     'icon'=>'fa-chart-line', 'color'=>'#0d9488', 'grad'=>'linear-gradient(135deg,#2dd4bf,#0f766e)',
     'badge'=>$nkteValue !== '—' ? ($rekap['predikat'] ?? '') : '', 'small'=>false],
    ['label'=>'Progres Penilaian', 'value'=>$selesaiPenilai.' / '.$totalPenilai,
     'icon'=>'fa-list-check', 'color'=>'#f59e0b', 'grad'=>'linear-gradient(135deg,#fbbf24,#d97706)',
     'badge'=>$pctKelengkapan.'%', 'small'=>false],
    ['label'=>'Publikasi Hasil',
     'value'=>$is_published ? 'Dipublikasikan' : 'Menunggu',
     'icon'=>'fa-bullhorn',
     'color'=>$is_published ? '#10b981' : '#f59e0b',
     'grad'=>$is_published ? 'linear-gradient(135deg,#34d399,#059669)' : 'linear-gradient(135deg,#fbbf24,#d97706)',
     'badge'=>'', 'small'=>true],
];
view('partials.stat-cards', ['items' => $kamadKpis]);
?>

<?php view('partials.mobile-menu-grid', ['nav' => nav_items()]); ?>

<!-- Profile + Info Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- Profil Card -->
    <div class="card p-6 flex flex-col items-center text-center space-y-3">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-2xl font-black text-white shadow"
             style="background:linear-gradient(135deg,#1e6347,#17503a);">
            <?= strtoupper(substr($kamad['nama_lengkap']??'K',0,1)) ?>
        </div>
        <div>
            <h3 class="font-bold text-slate-800 text-sm leading-tight"><?= e($kamad['nama_lengkap']??'-') ?></h3>
            <p class="text-xs text-slate-400 mt-0.5"><?= e($kamad['pangkat_golongan']??'-') ?></p>
        </div>
        <div class="w-full pt-2 border-t border-slate-100 space-y-2 text-left">
            <?php
            $rows = [
                ['NIP',       $kamad['nip']??'-'],
                ['NPK',       $kamad['npk']??'-'],
                ['NUPTK',     $kamad['nuptk']??'-'],
                ['TMT Kamad', $kamad['tmt_kamad']??'-'],
                ['Masa Kerja',$kamad['masa_kerja']??'-'],
            ];
            foreach ($rows as [$k,$v]):
            ?>
            <div class="flex justify-between text-[11px]">
                <span class="text-slate-400 font-medium"><?= $k ?></span>
                <span class="font-semibold text-slate-700 text-right max-w-[160px] truncate"><?= e($v) ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Periode + Status Card -->
    <div class="card p-6 space-y-4 lg:col-span-2">
        <h3 class="font-semibold text-slate-800 text-sm border-b border-slate-100 pb-3">Informasi Periode Penilaian</h3>

        <?php if ($periode): ?>
        <div class="grid grid-cols-2 gap-4">
            <?php
            $pInfo = [
                ['Nama Periode',    $periode['nama_periode']],
                ['Jenis Penilaian', ucfirst(str_replace('_',' ',$periode['jenis_penilaian']))],
                ['Tahun Penilaian', $periode['tahun_penilaian']],
                ['Mulai — Selesai', $periode['tanggal_mulai'].' s.d '.$periode['tanggal_selesai']],
                ['Versi Instrumen', 'v'.$periode['nomor_versi']],
                ['Status Periode',  ucfirst($periode['status'])],
            ];
            foreach ($pInfo as [$k,$v]):
            ?>
            <div>
                <div class="text-[10px] text-slate-400 font-medium uppercase tracking-wider"><?= $k ?></div>
                <div class="text-xs font-semibold text-slate-700 mt-0.5"><?= e($v) ?></div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Publikasi Status -->
        <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs font-medium text-slate-600">Status Publikasi Hasil:</span>
            <?php if ($is_published): ?>
            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200">
                <i class="fa-solid fa-circle-check"></i><span>Dipublikasikan</span>
            </span>
            <?php else: ?>
            <span class="inline-flex items-center space-x-1.5 px-3 py-1 rounded-full text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200">
                <i class="fa-solid fa-hourglass-half"></i><span>Menunggu Publikasi</span>
            </span>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <p class="text-xs text-slate-400">Belum ada periode penilaian aktif untuk akun Anda.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Hasil Resmi (jika dipublikasikan) -->
<?php if ($is_published && $rekap): ?>
<?php
$predColor = ['Amat Baik'=>'#059669','Baik'=>'#2563eb','Cukup'=>'#d97706','Sedang'=>'#9333ea','Kurang'=>'#dc2626'];
$pColor = $predColor[$rekap['predikat']] ?? '#1e6347';
?>
<div class="card overflow-hidden">
    <div class="h-1" style="background:linear-gradient(90deg,#1e6347,#17503a,#0f766e);"></div>
    <div class="p-6 flex flex-col sm:flex-row items-center justify-between gap-6">
        <div>
            <div class="text-[11px] font-bold uppercase tracking-wider text-brand-600 mb-1">Hasil Resmi Penilaian Kinerja (NKTE)</div>
            <div class="text-5xl font-black" style="background:linear-gradient(135deg,#1e6347,#17503a);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                <?= number_format($rekap['nkte'],2) ?>
            </div>
            <div class="text-xs text-slate-400 mt-1">dari skala 100</div>
        </div>
        <div class="flex items-center space-x-4">
            <div class="text-center">
                <div class="text-[10px] text-slate-400">Predikat</div>
                <div class="text-xl font-black mt-0.5" style="color:<?= $pColor ?>"><?= e($rekap['predikat']) ?></div>
            </div>
            <a href="<?= base_url('laporan/pdf/'.$periode['id']) ?>" target="_blank"
               class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white shadow transition"
               style="background:#dc2626;">
                <i class="fa-solid fa-file-pdf"></i><span>Unduh PDF</span>
            </a>
        </div>
    </div>
</div>
<?php elseif ($periode): ?>
<div class="card p-5 flex items-start space-x-4">
    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-blue-50">
        <i class="fa-solid fa-info-circle text-blue-500 text-sm"></i>
    </div>
    <div>
        <h4 class="text-sm font-semibold text-slate-700">Penilaian Sedang Berlangsung</h4>
        <p class="text-xs text-slate-500 mt-0.5">Hasil resmi akan ditampilkan di sini setelah semua penilai menyelesaikan penilaian dan administrator mempublikasikan hasilnya.</p>
    </div>
</div>
<?php endif; ?>

</div>
<?php view('layout.footer'); ?>


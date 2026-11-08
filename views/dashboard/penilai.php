<?php view('layout.header', ['title' => $title]); ?>

<?php
$a = $activeAssignment;
$isDone = $a && in_array($a['status_penilaian'], ['submitted', 'locked']);
$totalInd = (int)($a['total_indikator'] ?? 0);
$terisiInd = (int)($a['terisi_indikator'] ?? 0);
$pct = $totalInd > 0 ? round(($terisiInd / $totalInd) * 100) : 0;
?>

<div class="space-y-6">

<!-- ── Page Header ─────────────────────────────────────────── -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Dashboard Penilai</h1>
        <p class="text-sm text-slate-500 mt-0.5">Ringkasan status penugasan dan capaian penilaian kinerja kepala madrasah</p>
    </div>
    <?php if ($a): ?>
    <div class="flex items-center space-x-2">
        <a href="<?= base_url('penilai/penilaian') ?>"
           class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-xs font-bold text-white shadow transition"
           style="background:linear-gradient(135deg,#1e6347,#17503a);">
            <i class="fa-solid fa-pen-to-square"></i>
            <span>Buka Form Penilaian</span>
        </a>
    </div>
    <?php endif; ?>
</div>

<?php if ($a): ?>

<!-- ── KPI Cards Ringkasan ─────────────────────────────────── -->
<?php
$statusColor = $isDone ? '#10b981' : '#f59e0b';
$statusGrad  = $isDone ? 'linear-gradient(135deg,#34d399,#059669)' : 'linear-gradient(135deg,#fbbf24,#d97706)';
$penilaiKpis = [
    ['label'=>'Status Pengisian', 'value'=>$isDone ? 'Telah Disubmit' : 'Sedang Berjalan',
     'icon'=>$isDone ? 'fa-lock' : 'fa-hourglass-half', 'color'=>$statusColor, 'grad'=>$statusGrad,
     'badge'=>'', 'small'=>true],
    ['label'=>'Progres Indikator', 'value'=>$terisiInd.' / '.$totalInd,
     'icon'=>'fa-list-check', 'color'=>'#1e6347', 'grad'=>'linear-gradient(135deg,#4c9d75,#1a573f)',
     'badge'=>$pct.'%', 'small'=>false],
    ['label'=>'Nilai Akhir', 'value'=>$a['nilai_akhir'] !== null ? number_format($a['nilai_akhir'], 2) : '—',
     'icon'=>'fa-calculator', 'color'=>'#0d9488', 'grad'=>'linear-gradient(135deg,#2dd4bf,#0f766e)',
     'badge'=>$a['nilai_akhir'] !== null ? ($a['predikat'] ?? 'Belum final') : '', 'small'=>false],
    ['label'=>'Kelompok & Bobot', 'value'=>e($a['kode_kelompok']),
     'icon'=>'fa-user-shield', 'color'=>'#2b7d55', 'grad'=>'linear-gradient(135deg,#4c9d75,#1e6347)',
     'badge'=>'Bobot '.(int)$a['bobot_default'], 'small'=>false],
];
view('partials.stat-cards', ['items' => $penilaiKpis]);
?>

<?php view('partials.mobile-menu-grid', ['nav' => nav_items()]); ?>

<!-- ── Main Grid: Detail Penugasan & Komponen Progress ─────── -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    <!-- Profil Penugasan (1/3) -->
    <div class="card p-6 space-y-4">
        <div class="border-b border-slate-100 pb-3">
            <span class="text-[10px] uppercase tracking-wider font-extrabold text-brand-600">Informasi Penugasan</span>
            <h3 class="text-base font-bold text-slate-800 mt-0.5"><?= e($a['nama_kelompok']) ?></h3>
        </div>

        <div class="space-y-3 text-xs">
            <div>
                <div class="text-slate-400 text-[11px]">Madrasah Dinilai:</div>
                <div class="font-bold text-slate-800 mt-0.5 flex items-center space-x-1.5">
                    <i class="fa-solid fa-school text-slate-400"></i>
                    <span><?= e($a['nama_madrasah']) ?></span>
                </div>
            </div>

            <div>
                <div class="text-slate-400 text-[11px]">Kepala Madrasah:</div>
                <div class="font-bold text-slate-800 mt-0.5 flex items-center space-x-1.5">
                    <i class="fa-solid fa-user-tie text-slate-400"></i>
                    <span><?= e($a['nama_kamad']) ?></span>
                </div>
            </div>

            <div>
                <div class="text-slate-400 text-[11px]">Periode:</div>
                <div class="font-semibold text-slate-700 mt-0.5"><?= e($a['nama_periode']) ?> (<?= $a['tahun_penilaian'] ?>)</div>
            </div>

            <div>
                <div class="text-slate-400 text-[11px]">Jenis Penilaian:</div>
                <div class="font-semibold text-slate-700 mt-0.5"><?= ucfirst(str_replace('_', ' ', $a['jenis_penilaian'])) ?></div>
            </div>

            <?php if (!empty($a['tanggal_mulai']) && !empty($a['tanggal_selesai'])): ?>
            <div>
                <div class="text-slate-400 text-[11px]">Jadwal Pelaksanaan:</div>
                <div class="font-semibold text-slate-700 mt-0.5">
                    <?= date('d M Y', strtotime($a['tanggal_mulai'])) ?> s.d <?= date('d M Y', strtotime($a['tanggal_selesai'])) ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($a['tempat_penilaian'])): ?>
            <div>
                <div class="text-slate-400 text-[11px]">Tempat:</div>
                <div class="font-semibold text-slate-700 mt-0.5"><?= e($a['tempat_penilaian']) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Overall progress bar -->
        <div class="pt-2 border-t border-slate-100 space-y-1.5">
            <div class="flex justify-between text-xs">
                <span class="text-slate-500 font-medium">Kelengkapan</span>
                <span class="font-bold text-brand-600"><?= $pct ?>%</span>
            </div>
            <div class="h-2 rounded-full bg-slate-100 overflow-hidden">
                <div class="h-2 rounded-full transition-all duration-500"
                     style="width:<?= $pct ?>%; background:linear-gradient(90deg,#1e6347,#1a573f);"></div>
            </div>
        </div>

        <!-- CTA Button -->
        <div class="pt-2">
            <a href="<?= base_url('penilaian/form/' . $a['penilaian_id']) ?>"
               class="w-full py-3 rounded-xl text-xs font-bold text-white flex items-center justify-center space-x-2 transition shadow"
               style="background:<?= $isDone ? 'linear-gradient(135deg,#475569,#334155)' : 'linear-gradient(135deg,#1e6347,#17503a)' ?>;">
                <i class="fa-solid <?= $isDone ? 'fa-eye' : 'fa-pen-to-square' ?>"></i>
                <span><?= $isDone ? 'Lihat Lembar Nilai' : 'Lanjutkan Penilaian' ?></span>
            </a>
        </div>
    </div>

    <!-- Ringkasan Per Komponen (2/3) -->
    <div class="card p-6 space-y-4 lg:col-span-2">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Ringkasan Perolehan Per Komponen</h3>
                <p class="text-xs text-slate-400 mt-0.5">Rincian kelengkapan pengisian dan akumulasi skor tiap komponen instrumen</p>
            </div>
            <a href="<?= base_url('penilai/penilaian') ?>" class="text-xs text-brand-600 font-semibold hover:text-brand-700 flex items-center space-x-1">
                <span>Ke Form</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="space-y-4">
            <?php if (!empty($componentProgress)): ?>
            <?php foreach ($componentProgress as $k): ?>
            <?php
                $kTotal = (int)$k['total_indikator'];
                $kDone = (int)$k['terisi_indikator'];
                $kPct = $kTotal > 0 ? round(($kDone / $kTotal) * 100) : 0;
                $kLengkap = ($kDone >= $kTotal && $kTotal > 0);
            ?>
            <div class="p-4 rounded-xl bg-slate-50/70 border border-slate-100 space-y-2.5">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start space-x-3">
                        <span class="w-7 h-7 rounded-lg flex items-center justify-center font-black text-xs shrink-0 <?= $kLengkap ? 'bg-emerald-100 text-emerald-700' : 'bg-brand-100 text-brand-700' ?>">
                            <?= $k['nomor_komponen'] ?>
                        </span>
                        <div>
                            <div class="font-bold text-slate-800 text-xs"><?= e($k['nama_komponen']) ?></div>
                            <div class="text-[11px] text-slate-400 mt-0.5">
                                <?= $kDone ?> dari <?= $kTotal ?> butir indikator dinilai
                            </div>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-xs font-black text-slate-800">
                            Skor: <?= number_format($k['total_skor'], 0) ?>
                        </div>
                        <?php if ($kLengkap): ?>
                        <span class="text-[10px] font-bold text-emerald-600"><i class="fa-solid fa-circle-check mr-0.5"></i>Lengkap</span>
                        <?php else: ?>
                        <span class="text-[10px] font-bold text-amber-500"><i class="fa-solid fa-clock mr-0.5"></i>Belum Lengkap</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Progress bar -->
                <div class="h-1.5 rounded-full bg-slate-200/70 overflow-hidden">
                    <div class="h-1.5 rounded-full transition-all duration-500"
                         style="width:<?= $kPct ?>%; background:<?= $kLengkap ? '#10b981' : '#1e6347' ?>;"></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── Petunjuk Alur Penilaian ─────────────────────────────── -->
<div class="card p-5">
    <h3 class="font-semibold text-slate-800 text-xs mb-3 flex items-center space-x-2">
        <i class="fa-solid fa-circle-info text-brand-500"></i>
        <span>Petunjuk Pengisian Penilaian</span>
    </h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-600">
        <div class="p-3 bg-slate-50 rounded-xl space-y-1">
            <div class="font-bold text-slate-800">1. Pelajari Bukti Autentik</div>
            <p class="text-slate-500 leading-relaxed text-[11px]">Buka menu Bukti Dukung untuk memverifikasi dokumen fisik yang diunggah kepala madrasah sebelum memberi skor.</p>
        </div>
        <div class="p-3 bg-slate-50 rounded-xl space-y-1">
            <div class="font-bold text-slate-800">2. Beri Skor 1 s.d 4</div>
            <p class="text-slate-500 leading-relaxed text-[11px]">Buka Form Penilaian, klik nilai 1-4 pada setiap butir indikator. Skor tersimpan otomatis secara real-time.</p>
        </div>
        <div class="p-3 bg-slate-50 rounded-xl space-y-1">
            <div class="font-bold text-slate-800">3. Submit & Kunci Nilai</div>
            <p class="text-slate-500 leading-relaxed text-[11px]">Setelah semua indikator selesai diisi 100%, klik tombol Submit untuk mengunci hasil penilaian Anda.</p>
        </div>
    </div>
</div>

<?php else: ?>
<!-- State Kosong: Belum ada penugasan -->
<div class="card p-16 text-center">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 bg-slate-50 text-slate-400">
        <i class="fa-solid fa-clipboard-question text-3xl"></i>
    </div>
    <h3 class="font-bold text-slate-700 text-sm">Belum Ada Penugasan Penilaian</h3>
    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Akun Anda belum ditugaskan sebagai penilai pada periode penilaian aktif. Silakan hubungi administrator.</p>
</div>
<?php endif; ?>

</div><!-- /space-y-6 -->

<?php view('layout.footer'); ?>

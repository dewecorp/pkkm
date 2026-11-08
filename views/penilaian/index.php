<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">

<!-- ── Page Header ─────────────────────────────────────────── -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Formulir Penilaian Kinerja</h1>
        <p class="text-sm text-slate-500 mt-0.5">Daftar lembar penilaian kinerja kepala madrasah yang ditugaskan kepada Anda</p>
    </div>
</div>

<?php view('partials.panduan-skor'); ?>

<!-- ── List Lembar Penilaian ───────────────────────────────── -->
<?php if (!empty($list)): ?>
<div class="space-y-4">
    <?php foreach ($list as $item): ?>
    <?php
        $isDone = in_array($item['status'], ['submitted', 'locked']);
        $total = (int)$item['total_indikator'];
        $terisi = (int)$item['terisi_indikator'];
        $pct = $total > 0 ? round(($terisi / $total) * 100) : 0;
    ?>
    <div class="card card-hover overflow-hidden p-0">
        <!-- Status Bar -->
        <div class="h-1 w-full" style="background:<?= $isDone ? 'linear-gradient(90deg,#059669,#10b981)' : 'linear-gradient(90deg,#1e6347,#4c9d75)' ?>;"></div>

        <div class="p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <!-- Left: Info -->
            <div class="space-y-3 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-1 rounded-lg text-xs font-bold text-white shadow-sm"
                          style="background:linear-gradient(135deg,#1e6347,#17503a);">
                        <?= e($item['kode_kelompok']) ?> — <?= e($item['nama_kelompok']) ?>
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-600 bg-slate-100">
                        Bobot: <?= (int)$item['bobot_default'] ?>
                    </span>
                    <span class="px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-600 bg-slate-100">
                        <?= ucfirst(str_replace('_', ' ', $item['jenis_penilaian'])) ?> <?= $item['tahun_penilaian'] ?>
                    </span>

                    <?php if ($item['status'] === 'locked'): ?>
                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg text-xs font-bold text-slate-700 bg-slate-100 border border-slate-200">
                        <i class="fa-solid fa-lock text-[10px]"></i>
                        <span>Terkunci Admin</span>
                    </span>
                    <?php elseif ($item['status'] === 'submitted'): ?>
                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg text-xs font-bold text-emerald-700 bg-emerald-50 border border-emerald-200">
                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                        <span>Tersimpan (Dapat Diubah)</span>
                    </span>
                    <?php else: ?>
                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg text-xs font-bold text-amber-700 bg-amber-50 border border-amber-200">
                        <i class="fa-solid fa-pen text-[10px]"></i>
                        <span>Sedang Berjalan</span>
                    </span>
                    <?php endif; ?>
                </div>

                <div>
                    <h3 class="text-base font-bold text-slate-800 leading-tight"><?= e($item['nama_periode']) ?></h3>
                    <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-xs text-slate-500 mt-1">
                        <span><i class="fa-solid fa-school mr-1 text-slate-400"></i><?= e($item['nama_madrasah']) ?></span>
                        <span><i class="fa-solid fa-user-tie mr-1 text-slate-400"></i><?= e($item['nama_kamad']) ?></span>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="space-y-1 max-w-md">
                    <div class="flex justify-between text-xs">
                        <span class="text-slate-400 font-medium">Pengisian Butir Indikator</span>
                        <span class="font-bold text-slate-700"><?= $terisi ?> / <?= $total ?> (<?= $pct ?>%)</span>
                    </div>
                    <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-1.5 rounded-full transition-all duration-500"
                             style="width:<?= $pct ?>%; background:<?= $isDone ? '#10b981' : '#1e6347' ?>;"></div>
                    </div>
                </div>
            </div>

            <!-- Right: Score + CTA Button -->
            <div class="flex flex-row lg:flex-col items-center lg:items-end justify-between lg:justify-center gap-4 shrink-0 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                <?php if ($item['nilai_akhir'] !== null): ?>
                <div class="text-left lg:text-right">
                    <div class="text-[10px] text-slate-400 font-medium uppercase tracking-wider">Nilai Konversi</div>
                    <div class="text-2xl font-black text-slate-800 leading-none mt-0.5">
                        <?= number_format($item['nilai_akhir'], 2) ?>
                    </div>
                    <?php if (!empty($item['predikat'])): ?>
                    <?php $pw = predikat_warna($item['predikat']); ?>
                    <span class="inline-block mt-1 px-2 py-0.5 rounded text-[10px] font-bold border"
                          style="background:<?= $pw['bg'] ?>;color:<?= $pw['fg'] ?>;border-color:<?= $pw['fg'] ?>33;">
                        <?= e($item['predikat']) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <a href="<?= base_url('penilaian/form/' . $item['id']) ?>"
                   class="inline-flex items-center space-x-2 px-5 py-3 rounded-xl text-xs font-bold text-white shadow transition"
                   style="background:<?= ($item['status'] === 'locked') ? 'linear-gradient(135deg,#475569,#334155)' : 'linear-gradient(135deg,#1e6347,#17503a)' ?>;">
                    <i class="fa-solid <?= ($item['status'] === 'locked') ? 'fa-eye' : 'fa-pen-to-square' ?>"></i>
                    <span><?= ($item['status'] === 'locked') ? 'Lihat Lembar Nilai' : ($item['status'] === 'submitted' ? 'Lanjutkan / Ubah Nilai' : 'Buka & Isi Formulir') ?></span>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="card p-16 text-center">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 bg-slate-50 text-slate-400">
        <i class="fa-solid fa-clipboard-question text-3xl"></i>
    </div>
    <h3 class="font-bold text-slate-700 text-sm">Belum Ada Formulir Penilaian</h3>
    <p class="text-xs text-slate-400 mt-1 max-w-sm mx-auto">Anda belum memiliki formulir penilaian yang ditugaskan. Hubungi administrator untuk penugasan penilai.</p>
</div>
<?php endif; ?>

</div><!-- /space-y-6 -->

<?php view('layout.footer'); ?>

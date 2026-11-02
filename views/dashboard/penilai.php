<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Tugas Penilaian Anda</h1>
        <p class="text-sm text-slate-500 mt-0.5">Formulir penilaian kinerja kepala madrasah yang ditugaskan</p>
    </div>
</div>

<!-- Assignment Cards -->
<?php if (!empty($assignments)): ?>
    <div class="space-y-4">
    <?php foreach ($assignments as $a): ?>
    <?php
        $isDone = in_array($a['status_penilaian'], ['submitted','locked']);
        $pct = 0; // will be calculated client side if needed
    ?>
    <div class="card card-hover p-0 overflow-hidden">
        <!-- Color accent bar -->
        <div class="h-1 w-full" style="background:<?= $isDone ? 'linear-gradient(90deg,#059669,#10b981)' : 'linear-gradient(90deg,#6366f1,#818cf8)' ?>;"></div>

        <div class="p-5 sm:p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-5">
            <!-- Info -->
            <div class="space-y-2.5 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-bold text-white"
                          style="background:linear-gradient(135deg,#6366f1,#4338ca);">
                        <?= e($a['kode_kelompok']) ?> — <?= e($a['nama_kelompok']) ?>
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[11px] font-semibold text-slate-600 bg-slate-100">
                        <?= e(ucfirst(str_replace('_',' ',$a['jenis_penilaian']))) ?> <?= e($a['tahun_penilaian']) ?>
                    </span>
                    <?php if ($isDone): ?>
                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200">
                        <i class="fa-solid fa-lock text-[10px]"></i><span>Terkunci</span>
                    </span>
                    <?php else: ?>
                    <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-amber-700 bg-amber-50 border border-amber-200">
                        <i class="fa-solid fa-pen text-[10px]"></i><span>Sedang Berlangsung</span>
                    </span>
                    <?php endif; ?>
                </div>

                <h3 class="text-base font-bold text-slate-800"><?= e($a['nama_periode']) ?></h3>

                <div class="flex flex-wrap gap-x-5 gap-y-1 text-xs text-slate-500">
                    <span><i class="fa-solid fa-school mr-1 text-slate-300"></i><?= e($a['nama_madrasah']) ?></span>
                    <span><i class="fa-solid fa-user-tie mr-1 text-slate-300"></i><?= e($a['nama_kamad']) ?></span>
                </div>

                <?php if ($isDone): ?>
                <div class="flex items-center space-x-3 pt-1">
                    <div class="text-sm font-black text-slate-800"><?= number_format($a['nilai_akhir']??0,2) ?></div>
                    <div class="text-xs text-slate-400">/100</div>
                    <div class="px-2 py-0.5 rounded text-[11px] font-bold text-brand-700 bg-brand-50 border border-brand-200"><?= e($a['predikat']??'-') ?></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- CTA Button -->
            <div class="shrink-0">
                <a href="<?= base_url('penilaian/form/'.$a['penilaian_id']) ?>"
                   class="inline-flex items-center space-x-2 px-5 py-3 rounded-xl text-sm font-bold text-white shadow transition"
                   style="background:<?= $isDone ? 'linear-gradient(135deg,#475569,#334155)' : 'linear-gradient(135deg,#6366f1,#4338ca)' ?>;">
                    <i class="fa-solid <?= $isDone ? 'fa-eye' : 'fa-pen-to-square' ?>"></i>
                    <span><?= $isDone ? 'Lihat Lembar Nilai' : 'Isi Formulir Penilaian' ?></span>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    </div>

<?php else: ?>
<div class="card p-16 text-center">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4"
         style="background:rgba(99,102,241,0.08);">
        <i class="fa-solid fa-clipboard-question text-2xl text-brand-400"></i>
    </div>
    <h3 class="font-semibold text-slate-700 text-sm">Belum Ada Penugasan</h3>
    <p class="text-xs text-slate-400 mt-1 max-w-xs mx-auto">Anda belum ditugaskan sebagai penilai pada periode penilaian yang aktif. Hubungi administrator untuk informasi lebih lanjut.</p>
</div>
<?php endif; ?>

</div>
<?php view('layout.footer'); ?>

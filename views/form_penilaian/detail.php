<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">

<!-- ── Floating Topbar Header ──────────────────────────────── -->
<div class="card p-4 sm:p-6 sticky top-[4.25rem] lg:top-20 z-20 bg-white/95 border-white/80 shadow-[0_12px_36px_-20px_rgba(11,42,30,0.45)]">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div class="min-w-0 space-y-2">
            <div class="flex flex-wrap items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-brand-600 text-white shadow-sm">
                    <i class="fa-solid fa-layer-group text-[10px]"></i>
                    <span><?= e($penilaian['kode_kelompok']) ?> &middot; <?= e($penilaian['nama_kelompok']) ?></span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    <i class="fa-solid fa-school text-[10px] text-slate-400"></i>
                    <span class="truncate max-w-[200px]"><?= e($penilaian['nama_madrasah']) ?></span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                    <i class="fa-solid fa-user-check text-[10px] text-slate-400"></i>
                    <span>Penilai: <?= e($penilaian['nama_penilai']) ?></span>
                </span>
                <?php
                $stMap = ['draft'=>['Sedang Berjalan','amber'],'submitted'=>['Tersimpan','emerald'],'locked'=>['Terkunci','slate']];
                [$stLabel,$stColor] = $stMap[$penilaian['status']] ?? [$penilaian['status'],'slate'];
                ?>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold
                    <?= $stColor === 'emerald' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                      : ($stColor === 'amber'  ? 'bg-amber-50 text-amber-700 border border-amber-200'
                                               : 'bg-slate-100 text-slate-700 border border-slate-200') ?>">
                    <?= $stLabel ?>
                </span>
            </div>
            <h2 class="text-base sm:text-lg font-bold text-slate-800 leading-snug"><?= e($penilaian['nama_periode']) ?></h2>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 shrink-0">
            <!-- Progres -->
            <div class="px-3.5 py-2 rounded-xl bg-slate-50 border border-slate-200 text-right">
                <div class="text-[11px] text-slate-500 font-medium">Progres Pengisian</div>
                <div class="text-sm font-bold text-brand-700">
                    <?= $terisi_count ?> / <?= $total_indikator ?>
                    <span class="text-[11px] font-medium text-slate-500">Butir</span>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="flex items-center gap-2">
                <!-- Cetak via Window Print tab baru -->
                <a href="<?= base_url('form-penilaian/cetak/' . $penilaian['id']) ?>" target="_blank"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold shadow transition">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak</span>
                </a>
                <a href="<?= base_url('form-penilaian/excel/' . $penilaian['id']) ?>"
                   class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow transition">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Excel</span>
                </a>
                <a href="<?= base_url('form-penilaian') ?>"
                   class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ── Ringkasan Skor ────────────────────────────────────── -->
<div class="bg-gradient-to-r from-brand-700 to-brand-600 text-white rounded-xl p-4 flex flex-wrap items-center justify-between gap-4 text-xs shadow-sm">
    <div class="flex items-center space-x-2">
        <i class="fa-solid fa-calculator text-emerald-400 text-base"></i>
        <span>Skor Total Perolehan: <strong class="text-sm text-emerald-300 font-bold"><?= number_format($total_skor_perolehan, 0) ?></strong>
        / <span class="text-white/70"><?= number_format($total_skor_maks, 0) ?> maks</span></span>
    </div>
    <div class="flex items-center space-x-6">
        <div>Nilai Konversi (100): <strong class="text-sm text-amber-300 font-bold"><?= number_format($penilaian['nilai_akhir'] ?? 0, 2) ?></strong></div>
        <div>Predikat: <strong class="text-sm text-white bg-white/20 px-2 py-0.5 rounded font-bold"><?= e($penilaian['predikat'] ?? '—') ?></strong></div>
    </div>
</div>

<!-- ── Ringkasan Per Komponen ────────────────────────────── -->
<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="font-semibold text-slate-800 text-sm">Ringkasan Per Komponen</h3>
        <p class="text-xs text-slate-400 mt-0.5">Jumlah butir terisi & skor per komponen</p>
    </div>
    <div class="divide-y divide-slate-50">
        <?php foreach ($tree as $kData):
            $k      = $kData['komponen'];
            $pct    = $kData['skor_maks'] > 0 ? round(($kData['skor_perolehan'] / $kData['skor_maks']) * 100) : 0;
            $indPct = $kData['total_ind'] > 0 ? round(($kData['terisi'] / $kData['total_ind']) * 100) : 0;
        ?>
        <div class="px-5 py-4 flex items-center gap-4">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-brand-50 text-brand-700 font-black text-sm border border-brand-100">
                <?= $k['nomor_komponen'] ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold text-slate-700 truncate"><?= e($k['nama_komponen']) ?></span>
                    <span class="text-xs font-bold text-brand-700 ml-2 shrink-0"><?= $kData['skor_perolehan'] ?>/<?= $kData['skor_maks'] ?></span>
                </div>
                <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                    <div class="h-1.5 rounded-full bg-brand-500 transition-all duration-500" style="width:<?= $pct ?>%"></div>
                </div>
                <div class="flex justify-between mt-1 text-[10px] text-slate-400">
                    <span><?= $kData['terisi'] ?>/<?= $kData['total_ind'] ?> butir terisi (<?= $indPct ?>%)</span>
                    <span><?= $pct ?>% skor</span>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ── Detail Indikator ──────────────────────────────────── -->
<div class="space-y-8">
    <?php foreach ($tree as $kData):
        $k = $kData['komponen'];
    ?>
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="bg-slate-100/80 px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-200 flex items-center justify-between gap-4">
            <div>
                <span class="text-[11px] font-bold text-brand-700 uppercase tracking-wider">Komponen <?= $k['nomor_komponen'] ?></span>
                <h3 class="text-base font-bold text-slate-800"><?= e($k['nama_komponen']) ?></h3>
            </div>
            <div class="text-right shrink-0">
                <div class="text-[10px] text-slate-400">Skor</div>
                <div class="text-sm font-black text-brand-700"><?= $kData['skor_perolehan'] ?><span class="text-slate-400 font-medium text-xs">/<?= $kData['skor_maks'] ?></span></div>
                <div class="text-[10px] text-slate-400"><?= $kData['terisi'] ?>/<?= $kData['total_ind'] ?> butir</div>
            </div>
        </div>

        <div class="divide-y divide-slate-100">
            <?php foreach ($kData['tugas'] as $tData):
                $t = $tData['tugas'];
            ?>
            <div class="p-4 sm:p-6">
                <div class="mb-4">
                    <span class="px-2 py-0.5 rounded text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                        Tugas <?= e($t['kode_tugas']) ?>
                    </span>
                    <h4 class="text-sm font-bold text-slate-800 mt-1"><?= e($t['nama_tugas']) ?></h4>
                </div>

                <div class="space-y-4">
                    <?php foreach ($tData['indikator'] as $ind):
                        $hasSkor = $ind['skor'] !== null;
                        $skorColors = [
                            1 => ['bg'=>'#fef2f2','border'=>'#fca5a5','text'=>'#dc2626'],
                            2 => ['bg'=>'#fffbeb','border'=>'#fcd34d','text'=>'#d97706'],
                            3 => ['bg'=>'#eff6ff','border'=>'#93c5fd','text'=>'#2563eb'],
                            4 => ['bg'=>'#ecfdf5','border'=>'#6ee7b7','text'=>'#059669'],
                        ];
                        $sc = $hasSkor ? ($skorColors[(int)$ind['skor']] ?? []) : [];
                    ?>
                    <div class="bg-slate-50/60 rounded-xl p-3.5 sm:p-5 border <?= $hasSkor ? 'border-slate-200' : 'border-amber-200/60 bg-amber-50/20' ?> hover:border-slate-300 transition">
                        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                            <!-- Kiri: Detail Indikator -->
                            <div class="space-y-2 flex-1">
                                <div class="flex items-center space-x-2">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-200 text-slate-700">
                                        <?= e($ind['kode_indikator']) ?>
                                    </span>
                                    <span class="font-bold text-xs text-slate-800"><?= e($ind['uraian_indikator']) ?></span>
                                </div>

                                <div class="text-xs text-slate-600 bg-white p-3 rounded-lg border border-slate-200/80">
                                    <div class="font-semibold text-slate-700 mb-0.5">Data Kinerja yang Diharapkan:</div>
                                    <div><?= nl2br(e($ind['data_kinerja_diharapkan'])) ?></div>
                                </div>

                                <div class="text-xs text-slate-500 italic">
                                    <strong>Bukti Autentik:</strong> <?= e($ind['bukti_autentik']) ?>
                                </div>

                                <?php if (!empty($ind['catatan_penilai'])): ?>
                                <div class="text-xs text-slate-600 bg-blue-50 p-2 rounded-lg border border-blue-100">
                                    <i class="fa-solid fa-comment-dots text-blue-400 mr-1"></i>
                                    <strong>Catatan Penilai:</strong> <?= e($ind['catatan_penilai']) ?>
                                </div>
                                <?php endif; ?>
                            </div>

                            <!-- Kanan: Skor (read-only) -->
                            <div class="w-full lg:w-48 shrink-0">
                                <?php if ($hasSkor): ?>
                                <div class="rounded-xl border-2 p-4 text-center"
                                     style="background:<?= $sc['bg'] ?>;border-color:<?= $sc['border'] ?>">
                                    <div class="text-[10px] font-bold uppercase tracking-wider mb-1" style="color:<?= $sc['text'] ?>">Skor Diberikan</div>
                                    <div class="text-4xl font-black" style="color:<?= $sc['text'] ?>"><?= $ind['skor'] ?></div>
                                    <div class="text-[10px] mt-1" style="color:<?= $sc['text'] ?>">dari 4</div>
                                </div>
                                <?php else: ?>
                                <div class="rounded-xl border-2 border-dashed border-amber-300 bg-amber-50 p-4 text-center">
                                    <i class="fa-solid fa-clock text-amber-400 text-2xl mb-1"></i>
                                    <div class="text-xs font-semibold text-amber-600">Belum Diisi</div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

</div><!-- /space-y-6 -->

<?php view('layout.footer'); ?>

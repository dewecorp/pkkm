<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">

    <!-- ── Top Header / Info Card (Statis & Rapi, Tidak Melayang Menutup Konten) ── -->
    <div class="card p-5 sm:p-6 bg-white border border-slate-200/80 shadow-sm">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
            <div class="min-w-0 space-y-3">
                <!-- Badges -->
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-brand-700 text-white shadow-xs">
                        <i class="fa-solid fa-layer-group text-[10px]"></i>
                        <span>Kelompok <?= e($penilaian['kode_kelompok']) ?> &middot; <?= e($penilaian['nama_kelompok']) ?></span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="fa-solid fa-school text-[11px] text-slate-400"></i>
                        <span><?= e($penilaian['nama_madrasah']) ?></span>
                    </span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                        <i class="fa-solid fa-user-check text-[11px] text-brand-600"></i>
                        <span>Penilai: <strong><?= e($penilaian['nama_penilai']) ?></strong></span>
                    </span>
                    <?php
                    $stMap = [
                        'draft'     => ['Sedang Berjalan', 'bg-amber-50 text-amber-700 border-amber-200', 'fa-clock'],
                        'submitted' => ['Tersimpan',       'bg-emerald-50 text-emerald-700 border-emerald-200', 'fa-circle-check'],
                        'locked'    => ['Terkunci',        'bg-slate-100 text-slate-700 border-slate-200', 'fa-lock']
                    ];
                    [$stLabel, $stCls, $stIcon] = $stMap[$penilaian['status']] ?? [$penilaian['status'], 'bg-slate-100 text-slate-700 border-slate-200', 'fa-info-circle'];
                    ?>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold border <?= $stCls ?>">
                        <i class="fa-solid <?= $stIcon ?> text-[10px]"></i>
                        <span><?= $stLabel ?></span>
                    </span>
                </div>

                <!-- Judul Periode & Kamad -->
                <div>
                    <h1 class="text-lg sm:text-xl font-bold text-slate-800 leading-tight">
                        Detail Hasil Penilaian: <?= e($penilaian['nama_periode']) ?>
                    </h1>
                    <p class="text-xs text-slate-500 mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                        <span>Kepala Madrasah: <strong class="text-slate-700"><?= e($penilaian['nama_kamad']) ?></strong></span>
                        <span>&bull;</span>
                        <span>Tahun: <strong class="text-slate-700"><?= e($penilaian['tahun_penilaian']) ?></strong></span>
                        <span>&bull;</span>
                        <span>Jenis: <strong class="text-slate-700"><?= $penilaian['jenis_penilaian'] === 'tahunan' ? 'Tahunan (4 Komponen)' : '4 Tahunan (5 Komponen)' ?></strong></span>
                    </p>
                </div>
            </div>

            <!-- Progres Pengisian & Tombol Aksi -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 shrink-0">
                <!-- Progres -->
                <div class="px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-left sm:text-right">
                    <div class="text-[11px] text-slate-500 font-medium">Progres Pengisian</div>
                    <div class="text-base font-black text-brand-700">
                        <?= $terisi_count ?> <span class="text-xs text-slate-400 font-normal">/</span> <?= $total_indikator ?>
                        <span class="text-xs font-semibold text-slate-500">Butir</span>
                    </div>
                </div>

                <!-- Tombol Cetak, Excel, Kembali -->
                <div class="flex items-center gap-2">
                    <a href="<?= base_url('form-penilaian/cetak/' . $penilaian['id']) ?>" target="_blank"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold shadow-xs transition"
                       title="Cetak Form Penilaian">
                        <i class="fa-solid fa-print"></i>
                        <span>Cetak</span>
                    </a>
                    <a href="<?= base_url('form-penilaian/excel/' . $penilaian['id']) ?>"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-xs transition"
                       title="Unduh format Excel">
                        <i class="fa-solid fa-file-excel"></i>
                        <span>Excel</span>
                    </a>
                    <a href="<?= base_url('form-penilaian') ?>"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        <i class="fa-solid fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- ── KPI Summary Banner ─────────────────────────────────── -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
        <!-- Total Skor Perolehan -->
        <div class="card p-4 bg-white border border-slate-200/80">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Total Skor Perolehan</span>
                <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-calculator"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-slate-800"><?= number_format($total_skor_perolehan, 0) ?></span>
                <span class="text-xs font-medium text-slate-400">/ <?= number_format($total_skor_maks, 0) ?> maks</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Akumulasi seluruh butir</div>
        </div>

        <!-- Nilai Konversi (100) -->
        <div class="card p-4 bg-white border border-slate-200/80">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Nilai Konversi (100)</span>
                <span class="w-8 h-8 rounded-lg bg-brand-50 text-brand-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
            </div>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-brand-700"><?= number_format($penilaian['nilai_akhir'] ?? 0, 2) ?></span>
                <span class="text-xs font-medium text-slate-400">/ 100</span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Skor perolehan / skor maks &times; 100</div>
        </div>

        <!-- Predikat Penilaian -->
        <div class="card p-4 bg-white border border-slate-200/80">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Predikat Kinerja</span>
                <span class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-award"></i>
                </span>
            </div>
            <div class="mt-2">
                <span class="inline-block px-2.5 py-1 rounded-md text-sm font-black bg-brand-100 text-brand-800 uppercase tracking-wide">
                    <?= e($penilaian['predikat'] ?? '—') ?>
                </span>
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Kategori mutu kinerja</div>
        </div>

        <!-- Kelengkapan Pengisian -->
        <div class="card p-4 bg-white border border-slate-200/80">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-500">Kelengkapan Butir</span>
                <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-list-check"></i>
                </span>
            </div>
            <?php
            $persenTerisi = $total_indikator > 0 ? round(($terisi_count / $total_indikator) * 100) : 0;
            ?>
            <div class="mt-2 flex items-baseline gap-1.5">
                <span class="text-2xl font-black text-slate-800"><?= $persenTerisi ?>%</span>
                <span class="text-xs font-medium text-slate-400">(<?= $terisi_count ?>/<?= $total_indikator ?> butir)</span>
            </div>
            <div class="w-full h-1.5 rounded-full bg-slate-100 overflow-hidden mt-2">
                <div class="h-1.5 rounded-full bg-brand-600 transition-all" style="width: <?= $persenTerisi ?>%"></div>
            </div>
        </div>
    </div>

    <!-- ── 5 Komponen Tab Selector (Desktop & Mobile Navigasi Cepat) ── -->
    <div class="card p-2 sm:p-2.5 bg-slate-100/80 border border-slate-200">
        <div class="flex items-center justify-between px-2 pt-1 pb-2">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-table-columns text-brand-600 text-xs"></i>
                <span class="text-xs font-bold text-slate-700">Pilih Komponen Penilaian:</span>
            </div>
            <button type="button" onclick="pilihKomponen('all')" id="btn-tab-all"
                    class="px-2.5 py-1 rounded-lg text-xs font-bold transition text-slate-600 hover:text-brand-700 hover:bg-white/80">
                Tampilkan Semua Komponen
            </button>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-<?= min(count($tree), 5) ?> gap-2">
            <?php foreach ($tree as $idx => $kData):
                $k       = $kData['komponen'];
                $pctKomp = $kData['skor_maks'] > 0 ? round(($kData['skor_perolehan'] / $kData['skor_maks']) * 100) : 0;
            ?>
            <button type="button" onclick="pilihKomponen(<?= $k['id'] ?>)" id="btn-tab-<?= $k['id'] ?>"
                    class="tab-komp-btn flex flex-col p-2.5 sm:p-3 rounded-xl transition text-left border <?= $idx === 0 ? 'bg-white shadow-sm border-brand-300 text-brand-700 font-bold' : 'bg-white/60 hover:bg-white text-slate-600 border-transparent font-medium' ?>">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-[10px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded bg-brand-50 text-brand-700">
                        Komp. <?= $k['nomor_komponen'] ?>
                    </span>
                    <span class="text-[11px] font-bold text-brand-700">
                        <?= $kData['skor_perolehan'] ?>/<?= $kData['skor_maks'] ?>
                    </span>
                </div>
                <span class="text-xs leading-snug line-clamp-2 text-slate-800">
                    <?= e($k['nama_komponen']) ?>
                </span>
                <div class="mt-2 flex items-center justify-between text-[10px] text-slate-400">
                    <span><?= $kData['terisi'] ?>/<?= $kData['total_ind'] ?> butir</span>
                    <span class="font-bold text-slate-600"><?= $pctKomp ?>%</span>
                </div>
            </button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── Daftar Komponen & Butir Indikator ──────────────────── -->
    <div class="space-y-6" id="komponenContainer">
        <?php foreach ($tree as $kIdx => $kData):
            $k       = $kData['komponen'];
            $pctKomp = $kData['skor_maks'] > 0 ? round(($kData['skor_perolehan'] / $kData['skor_maks']) * 100) : 0;
        ?>
        <div class="komponen-card card overflow-hidden border border-slate-200/90 shadow-sm transition-all"
             id="komp-section-<?= $k['id'] ?>" data-komponen-id="<?= $k['id'] ?>">

            <!-- Komponen Banner Header -->
            <div class="bg-gradient-to-r from-slate-50 via-brand-50/20 to-slate-50 px-5 sm:px-6 py-4 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-brand-700 text-white">
                            KOMPONEN <?= $k['nomor_komponen'] ?>
                        </span>
                        <span class="text-xs text-slate-500 font-medium">Struktur Baku Penilaian</span>
                    </div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800 mt-1">
                        <?= e($k['nama_komponen']) ?>
                    </h2>
                </div>

                <div class="flex items-center gap-4 shrink-0 sm:text-right">
                    <div class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs">
                        <div class="text-[10px] text-slate-400 uppercase font-semibold">Skor Perolehan</div>
                        <div class="text-base font-black text-brand-700">
                            <?= $kData['skor_perolehan'] ?> <span class="text-xs text-slate-400 font-medium">/ <?= $kData['skor_maks'] ?></span>
                        </div>
                    </div>
                    <div class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 shadow-2xs">
                        <div class="text-[10px] text-slate-400 uppercase font-semibold">Butir Terisi</div>
                        <div class="text-base font-black text-slate-800">
                            <?= $kData['terisi'] ?> <span class="text-xs text-slate-400 font-medium">/ <?= $kData['total_ind'] ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- List Tugas Utama & Butir Indikator -->
            <div class="divide-y divide-slate-100">
                <?php foreach ($kData['tugas'] as $tData):
                    $t = $tData['tugas'];
                ?>
                <div class="p-4 sm:p-6 bg-white">
                    <!-- Tugas Utama Badge -->
                    <div class="mb-4 pb-2 border-b border-slate-100 flex items-baseline gap-2.5">
                        <span class="px-2.5 py-1 rounded-lg text-xs font-extrabold bg-brand-50 text-brand-800 border border-brand-200/80 shrink-0">
                            Tugas <?= e($t['kode_tugas']) ?>
                        </span>
                        <h3 class="text-sm font-bold text-slate-800 leading-snug">
                            <?= e($t['nama_tugas']) ?>
                        </h3>
                    </div>

                    <!-- Butir Indikator Rows -->
                    <div class="space-y-4">
                        <?php foreach ($tData['indikator'] as $ind):
                            $hasSkor = $ind['skor'] !== null;
                            $skorVal = (int)($ind['skor'] ?? 0);

                            // Palet warna skor terarah
                            $skorStyles = [
                                1 => ['bg' => '#fef2f2', 'border' => '#fca5a5', 'text' => '#dc2626', 'label' => 'Kurang'],
                                2 => ['bg' => '#fffbeb', 'border' => '#fcd34d', 'text' => '#d97706', 'label' => 'Cukup'],
                                3 => ['bg' => '#eff6ff', 'border' => '#93c5fd', 'text' => '#2563eb', 'label' => 'Baik'],
                                4 => ['bg' => '#ecfdf5', 'border' => '#6ee7b7', 'text' => '#059669', 'label' => 'Sangat Baik'],
                            ];
                            $style = $hasSkor ? ($skorStyles[$skorVal] ?? ['bg'=>'#f8fafc','border'=>'#e2e8f0','text'=>'#334155','label'=>'Tercatat']) : null;
                        ?>
                        <div class="rounded-2xl p-4 sm:p-5 border transition-all duration-150 <?= $hasSkor ? 'bg-slate-50/40 border-slate-200 hover:border-slate-300 hover:bg-slate-50/70' : 'bg-amber-50/20 border-amber-200/80' ?>">

                            <!-- Desktop: Grid Proporsional (Kiri Luas, Kanan Kompak Fixed) -->
                            <div class="indikator-layout-grid">

                                <!-- Sisi Kiri: Uraian, Data Kinerja, Bukti Autentik, Berkas Bukti -->
                                <div class="space-y-3 min-w-0">
                                    <!-- Header Indikator: Kode + Teks Uraian Lega -->
                                    <div class="flex items-start gap-2.5">
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-md text-xs font-black bg-slate-200 text-slate-800 shrink-0 mt-0.5">
                                            <?= e($ind['kode_indikator']) ?>
                                        </span>
                                        <div class="text-sm font-bold text-slate-800 leading-snug">
                                            <?= e($ind['uraian_indikator']) ?>
                                        </div>
                                    </div>

                                    <!-- Data Kinerja yang Diharapkan -->
                                    <div class="text-xs bg-white p-3 rounded-xl border border-slate-200/80 text-slate-700 shadow-2xs">
                                        <div class="font-bold text-slate-600 mb-1 flex items-center gap-1.5">
                                            <i class="fa-solid fa-bullseye text-brand-600 text-[11px]"></i>
                                            <span>Data Kinerja yang Diharapkan:</span>
                                        </div>
                                        <div class="text-slate-600 leading-relaxed whitespace-pre-line pl-4 border-l-2 border-brand-200">
                                            <?= nl2br(e($ind['data_kinerja_diharapkan'])) ?>
                                        </div>
                                    </div>

                                    <!-- Bukti Autentik Deskripsi -->
                                    <div class="text-xs text-slate-600 bg-slate-100/60 p-2.5 rounded-xl border border-slate-200/60 leading-relaxed">
                                        <span class="font-bold text-slate-700">Bukti Autentik Kualitas Kinerja:</span>
                                        <span class="italic text-slate-600"><?= e($ind['bukti_autentik']) ?></span>
                                    </div>

                                    <!-- Berkas Bukti Terunggah (jika ada file diunggah kamad) -->
                                    <?php if (!empty($ind['bukti_list'])): ?>
                                    <div class="p-3 bg-white rounded-xl border border-brand-200/70 space-y-2">
                                        <div class="text-[11px] font-bold text-brand-800 flex items-center gap-1.5">
                                            <i class="fa-solid fa-paperclip text-brand-600"></i>
                                            <span>Dokumen Bukti Fisik Terunggah (<?= count($ind['bukti_list']) ?> berkas):</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <?php foreach ($ind['bukti_list'] as $bIdx => $b): ?>
                                            <div class="flex items-center justify-between p-2 rounded-lg bg-slate-50 border border-slate-200 hover:border-brand-300 transition">
                                                <div class="flex items-center space-x-2 min-w-0 pr-1.5">
                                                    <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-brand-100 text-brand-800 shrink-0">
                                                        <?= e($b['file_ext'] ?? 'FILE') ?>
                                                    </span>
                                                    <span class="text-xs font-semibold text-slate-800 truncate block" title="<?= e($b['nama_dokumen']) ?>">
                                                        <?= e($b['nama_dokumen']) ?>
                                                    </span>
                                                </div>
                                                <a href="<?= base_url('bukti/preview/' . $b['id']) ?>" target="_blank"
                                                   class="px-2 py-1 rounded bg-brand-600 text-white text-[10px] font-bold hover:bg-brand-700 transition shrink-0"
                                                   title="Buka Pratinjau Dokumen">
                                                    Lihat
                                                </a>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>

                                    <!-- Catatan Penilai (Jika Ada) -->
                                    <?php if (!empty($ind['catatan_penilai'])): ?>
                                    <div class="text-xs bg-blue-50/80 p-2.5 rounded-xl border border-blue-200 text-blue-900 leading-relaxed flex items-start gap-2">
                                        <i class="fa-solid fa-comment-dots text-blue-500 mt-0.5 shrink-0"></i>
                                        <div>
                                            <span class="font-bold text-blue-800">Catatan Penilai:</span>
                                            <span><?= e($ind['catatan_penilai']) ?></span>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Sisi Kanan: Skor Diberikan (Kompak, Elegan, Fixed Width di Desktop) -->
                                <div class="skor-box-wrapper">
                                    <?php if ($hasSkor): ?>
                                    <div class="skor-card-box rounded-2xl border-2 p-4 text-center shadow-xs"
                                         style="background: <?= $style['bg'] ?>; border-color: <?= $style['border'] ?>;">
                                        <div class="text-[10px] font-extrabold uppercase tracking-wider" style="color: <?= $style['text'] ?>;">
                                            SKOR DIBERIKAN
                                        </div>
                                        <div class="text-4xl font-black my-1" style="color: <?= $style['text'] ?>;">
                                            <?= $ind['skor'] ?>
                                        </div>
                                        <div class="text-[11px] font-semibold" style="color: <?= $style['text'] ?>;">
                                            dari 4 poin
                                        </div>
                                        <div class="mt-2 pt-2 border-t border-black/5">
                                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase"
                                                  style="background: white; color: <?= $style['text'] ?>; border: 1px solid <?= $style['border'] ?>;">
                                                <?= $style['label'] ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php else: ?>
                                    <div class="skor-card-box rounded-2xl border-2 border-dashed border-amber-300 bg-amber-50/80 p-4 text-center">
                                        <i class="fa-solid fa-clock text-amber-500 text-2xl mb-1.5 block"></i>
                                        <div class="text-xs font-bold text-amber-700">Belum Diisi</div>
                                        <div class="text-[10px] text-amber-600 mt-0.5">Penilai belum memasukkan nilai</div>
                                    </div>
                                    <?php endif; ?>
                                </div>

                            </div><!-- /indikator-layout-grid -->

                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div><!-- /komponen-card -->
        <?php endforeach; ?>
    </div>

</div><!-- /space-y-6 -->

<!-- ── Style Scoped Khusus Tampilan Detail Form Penilaian ── -->
<style>
/* Layout grid proporsional antara uraian kiri dan box skor kanan */
.indikator-layout-grid {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.skor-box-wrapper {
    width: 100%;
    flex-shrink: 0;
}

.skor-card-box {
    width: 100%;
}

@media (min-width: 1024px) {
    .indikator-layout-grid {
        display: flex;
        flex-direction: row;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1.5rem;
    }
    .skor-box-wrapper {
        width: 180px;
        min-width: 180px;
        max-width: 180px;
    }
}
</style>

<!-- ── Script Interaktif Tab Komponen ── -->
<script>
function pilihKomponen(kompId) {
    const cards = document.querySelectorAll('.komponen-card');
    const tabBtns = document.querySelectorAll('.tab-komp-btn');
    const btnAll = document.getElementById('btn-tab-all');

    if (kompId === 'all') {
        // Tampilkan semua komponen
        cards.forEach(card => card.classList.remove('hidden'));
        tabBtns.forEach(btn => {
            btn.classList.remove('bg-white', 'shadow-sm', 'border-brand-300', 'text-brand-700', 'font-bold');
            btn.classList.add('bg-white/60', 'text-slate-600', 'border-transparent', 'font-medium');
        });
        if (btnAll) {
            btnAll.classList.add('bg-brand-600', 'text-white');
            btnAll.classList.remove('text-slate-600');
        }
    } else {
        // Tampilkan satu komponen terpilih
        cards.forEach(card => {
            const id = parseInt(card.dataset.komponenId, 10);
            if (id === kompId) {
                card.classList.remove('hidden');
            } else {
                card.classList.add('hidden');
            }
        });

        tabBtns.forEach(btn => {
            btn.classList.remove('bg-white', 'shadow-sm', 'border-brand-300', 'text-brand-700', 'font-bold');
            btn.classList.add('bg-white/60', 'text-slate-600', 'border-transparent', 'font-medium');
        });

        const activeBtn = document.getElementById('btn-tab-' + kompId);
        if (activeBtn) {
            activeBtn.classList.remove('bg-white/60', 'text-slate-600', 'border-transparent', 'font-medium');
            activeBtn.classList.add('bg-white', 'shadow-sm', 'border-brand-300', 'text-brand-700', 'font-bold');
        }

        if (btnAll) {
            btnAll.classList.remove('bg-brand-600', 'text-white');
            btnAll.classList.add('text-slate-600');
        }

        // Scroll halus ke kartu komponen
        const targetCard = document.getElementById('komp-section-' + kompId);
        if (targetCard) {
            targetCard.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }
}
</script>

<?php view('layout.footer'); ?>

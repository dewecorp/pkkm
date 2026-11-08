<?php
/**
 * Panduan pemberian skor indikator kinerja PKKM.
 * Dipakai di area penilai (daftar & formulir penilaian).
 * Set $open = true untuk menampilkan panduan langsung terbuka.
 */
$open = $open ?? false;
?>
<div class="card overflow-hidden p-0">
    <details id="panduanSkor"<?= $open ? ' open' : '' ?>>
        <summary class="flex items-center justify-between gap-3 px-5 py-4 cursor-pointer select-none">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-9 h-9 rounded-xl bg-brand-50 text-brand-700 border border-brand-200 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-info text-sm"></i>
                </span>
                <div class="min-w-0">
                    <div class="text-sm font-bold text-slate-800">Panduan Pemberian Skor</div>
                    <div class="text-[11px] text-slate-500">Ketentuan skor 1&ndash;4 berdasarkan kelengkapan dan kualitas bukti kinerja</div>
                </div>
            </div>
            <span class="inline-flex items-center gap-1.5 text-[11px] font-bold text-brand-700 shrink-0">
                <span class="panduan-open-label">Lihat</span>
                <span class="panduan-close-label">Tutup</span>
                <i class="fa-solid fa-chevron-down text-[10px] panduan-chevron"></i>
            </span>
        </summary>

        <div class="px-5 pb-5 pt-4 border-t border-slate-100 space-y-4">
            <p class="text-xs text-slate-600 leading-relaxed">
                Pemberian skor indikator kinerja untuk setiap unsur tugas utama dilakukan dengan ketentuan berikut:
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/60 p-3.5">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-emerald-600 text-white text-xs font-black flex items-center justify-center shrink-0">4</span>
                        <span class="text-[10px] font-black uppercase tracking-wider text-emerald-700">Skor 4 &middot; Sangat Meyakinkan</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Kepala madrasah mampu menunjukkan bukti-bukti yang <strong class="text-emerald-700">LENGKAP</strong> dan <strong class="text-emerald-700">SANGAT MEYAKINKAN</strong> bahwa kepala madrasah yang bersangkutan berkinerja sesuai dengan masing-masing kriteria komponen yang dinilai.
                    </p>
                </div>

                <div class="rounded-xl border border-brand-200 bg-brand-50/60 p-3.5">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-brand-600 text-white text-xs font-black flex items-center justify-center shrink-0">3</span>
                        <span class="text-[10px] font-black uppercase tracking-wider text-brand-700">Skor 3 &middot; Cukup Meyakinkan</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Kepala madrasah mampu menunjukkan bukti-bukti yang <strong class="text-brand-700">LENGKAP</strong> dan <strong class="text-brand-700">CUKUP MEYAKINKAN</strong> bahwa kepala madrasah yang bersangkutan berkinerja sesuai dengan masing-masing kriteria komponen yang dinilai.
                    </p>
                </div>

                <div class="rounded-xl border border-amber-200 bg-amber-50/60 p-3.5">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-amber-500 text-white text-xs font-black flex items-center justify-center shrink-0">2</span>
                        <span class="text-[10px] font-black uppercase tracking-wider text-amber-700">Skor 2 &middot; Kurang Lengkap</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Kepala madrasah mampu menunjukkan bukti-bukti yang <strong class="text-amber-700">KURANG LENGKAP</strong> dan <strong class="text-amber-700">CUKUP MEYAKINKAN</strong> bahwa kepala madrasah yang bersangkutan berkinerja sesuai dengan masing-masing kriteria komponen yang dinilai.
                    </p>
                </div>

                <div class="rounded-xl border border-rose-200 bg-rose-50/60 p-3.5">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="w-7 h-7 rounded-lg bg-rose-600 text-white text-xs font-black flex items-center justify-center shrink-0">1</span>
                        <span class="text-[10px] font-black uppercase tracking-wider text-rose-700">Skor 1 &middot; Sangat Terbatas</span>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Diberikan apabila ditemukan bukti-bukti yang <strong class="text-rose-700">SANGAT TERBATAS</strong> dan <strong class="text-rose-700">KURANG MEYAKINKAN</strong> atau <strong class="text-rose-700">TIDAK DITEMUKAN</strong> bahwa kepala madrasah yang bersangkutan berkinerja sesuai dengan masing-masing kriteria komponen yang dinilai.
                    </p>
                </div>
            </div>

            <div class="rounded-xl border border-brand-200 bg-brand-50/70 p-3.5 flex gap-2.5">
                <i class="fa-solid fa-layer-group text-brand-600 mt-0.5 shrink-0"></i>
                <p class="text-[11px] text-slate-600 leading-relaxed">
                    <strong class="text-brand-800">Kompetensi A&ndash;D</strong> digunakan untuk Penilaian Kinerja Kepala Madrasah Tahun Pertama sampai Tahun Ketiga. Sedangkan untuk Tahun Keempat, semua kompetensi (A sampai D) diisi/digunakan.
                </p>
            </div>
        </div>
    </details>
</div>

<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">
    <!-- Header Formulir (floating di desktop agar tombol submit selalu terjangkau) -->
    <div class="card p-3 sm:p-6 sticky top-[4.25rem] lg:top-20 z-20 bg-white/95 border-white/80 shadow-[0_12px_36px_-20px_rgba(11,42,30,0.45)]">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="min-w-0 space-y-2.5">
                <div class="flex flex-wrap items-center gap-1.5 sm:gap-2">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-brand-600 text-white shadow-sm">
                        <i class="fa-solid fa-layer-group text-[10px]"></i>
                        <span>Kelompok <?= e($penilaian['kode_kelompok']) ?> &middot; <?= e($penilaian['nama_kelompok']) ?></span>
                    </span>
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        <i class="fa-solid fa-school text-[10px] text-slate-400"></i>
                        <span class="truncate max-w-[220px]"><?= e($penilaian['nama_madrasah']) ?></span>
                    </span>
                    <span class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                        <i class="fa-solid fa-user-tie text-[10px] text-slate-400"></i>
                        <span class="truncate max-w-[200px]">Kamad: <?= e($penilaian['nama_kamad']) ?></span>
                    </span>
                </div>
                <h2 class="hidden sm:block text-base sm:text-lg font-bold text-slate-800 leading-snug"><?= e($penilaian['nama_periode']) ?></h2>
            </div>

            <div class="flex flex-row items-center justify-between sm:justify-end gap-2 sm:gap-3 shrink-0">
                <!-- Progress Counter -->
                <div class="flex items-center justify-between gap-3 px-3 py-2 sm:px-3.5 sm:py-2.5 rounded-xl bg-slate-50 border border-slate-200 sm:block sm:text-right">
                    <span class="text-[11px] text-slate-500 font-medium">Progres Pengisian</span>
                    <div class="text-sm font-bold text-brand-700">
                        <span id="answeredCount"><?= $terisi_count ?></span> / <?= $total_indikator ?>
                        <span class="text-[11px] font-medium text-slate-500">Butir</span>
                    </div>
                </div>

                <!-- Submit Button / Status Lock -->
                <div class="flex items-center gap-2 sm:justify-end">
                    <?php if ($penilaian['status'] === 'locked'): ?>
                        <div class="px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-bold flex items-center gap-1.5 border border-slate-200">
                            <i class="fa-solid fa-lock"></i>
                            <span>Terkunci oleh Admin</span>
                        </div>
                        <?php if (\App\Core\Auth::hasRole('admin')): ?>
                            <button onclick="unlockPenilaian(<?= $penilaian['id'] ?>)" class="px-3 py-2.5 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                                <i class="fa-solid fa-lock-open"></i>
                                <span>Buka Kunci</span>
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if ($penilaian['status'] === 'submitted'): ?>
                            <span class="inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200">
                                <i class="fa-solid fa-circle-check text-[11px]"></i>
                                <span>Tersimpan</span>
                            </span>
                        <?php endif; ?>
                        <button onclick="submitPenilaian(<?= $penilaian['id'] ?>)" class="min-h-[44px] px-4 sm:px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition shadow flex items-center justify-center gap-2 whitespace-nowrap">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span><?= ($penilaian['status'] === 'submitted') ? 'Simpan / Submit Nilai' : 'Submit Nilai' ?></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php view('partials.panduan-skor', ['open' => true]); ?>

    <!-- Live Score Summary Bar -->
    <div class="bg-gradient-to-r from-brand-700 to-brand-600 text-white rounded-xl p-4 flex flex-wrap items-center justify-between gap-4 text-xs shadow-sm">
        <div class="flex items-center space-x-2">
            <i class="fa-solid fa-calculator text-emerald-400 text-base"></i>
            <span>Skor Total Perolehan: <strong id="liveTotalSkor" class="text-sm text-emerald-300 font-bold"><?= number_format($penilaian['total_skor'] ?? 0, 0) ?></strong></span>
        </div>
        <div class="flex items-center space-x-6">
            <div>Nilai Konversi (100): <strong id="liveNilaiAkhir" class="text-sm text-amber-300 font-bold"><?= number_format($penilaian['nilai_akhir'] ?? 0, 2) ?></strong></div>
            <div>Predikat: <strong id="livePredikat" class="text-sm text-white bg-white/20 px-2 py-0.5 rounded font-bold"><?= e($penilaian['predikat'] ?? '-') ?></strong></div>
        </div>
    </div>

    <!-- Daftar Indikator Dinamis 5 Komponen -->
    <div class="space-y-8">
        <?php foreach ($tree as $kData): ?>
            <?php $k = $kData['komponen']; ?>
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <!-- Komponen Banner -->
                <div class="bg-slate-100/80 px-4 sm:px-6 py-3 sm:py-4 border-b border-slate-200">
                    <span class="text-[11px] font-bold text-brand-700 uppercase tracking-wider">Komponen <?= $k['nomor_komponen'] ?></span>
                    <h3 class="text-base font-bold text-slate-800"><?= e($k['nama_komponen']) ?></h3>
                </div>

                <!-- Tugas Utama & Indikator -->
                <div class="divide-y divide-slate-200">
                    <?php foreach ($kData['tugas'] as $tData): ?>
                        <?php $t = $tData['tugas']; ?>
                        <div class="p-4 sm:p-6">
                            <div class="mb-4">
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                                    Tugas <?= e($t['kode_tugas']) ?>
                                </span>
                                <h4 class="text-sm font-bold text-slate-800 mt-1"><?= e($t['nama_tugas']) ?></h4>
                            </div>

                            <!-- List Indikator -->
                            <div class="space-y-6">
                                <?php foreach ($tData['indikator'] as $ind): ?>
                                    <div class="bg-slate-50/60 rounded-xl p-3.5 sm:p-5 border border-slate-200 hover:border-slate-300 transition" id="ind-card-<?= $ind['id'] ?>">
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
                                                    <strong>Bukti Autentik Kualitas Kinerja:</strong> <?= e($ind['bukti_autentik']) ?>
                                                </div>

                                                <!-- Bukti Dukung Terunggah -->
                                                <div class="pt-2">
                                                    <?php if (!empty($ind['bukti_list'])): ?>
                                                        <div class="p-3 bg-slate-50/80 rounded-xl border border-slate-200/80 space-y-2">
                                                            <div class="flex items-center justify-between">
                                                                <span class="text-[11px] font-bold text-slate-700 flex items-center space-x-1.5">
                                                                    <i class="fa-solid fa-paperclip text-brand-600"></i>
                                                                    <span>Dokumen Bukti Dukung (<?= count($ind['bukti_list']) ?> berkas):</span>
                                                                </span>
                                                            </div>
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                                <?php foreach ($ind['bukti_list'] as $bIdx => $b): ?>
                                                                <div class="flex items-center justify-between p-2 rounded-xl bg-white border border-slate-200 hover:border-brand-400 hover:shadow-xs transition group">
                                                                    <div class="flex items-center space-x-2 min-w-0 pr-1.5">
                                                                        <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 font-bold text-[10px] flex items-center justify-center shrink-0 border border-slate-200 shadow-xs">
                                                                            <?= $bIdx + 1 ?>
                                                                        </span>
                                                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-black uppercase bg-brand-50 text-brand-700 border border-brand-200/60 shrink-0">
                                                                            <?= e($b['file_ext']) ?>
                                                                        </span>
                                                                        <div class="min-w-0">
                                                                            <a href="<?= base_url('bukti/preview/' . $b['id']) ?>" target="_blank"
                                                                               class="text-xs font-semibold text-slate-800 hover:text-brand-600 truncate block"
                                                                               title="Lihat Pratinjau: <?= e($b['nama_dokumen']) ?>">
                                                                                <?= e($b['nama_dokumen']) ?>
                                                                            </a>
                                                                            <div class="text-[10px] text-slate-400 truncate">
                                                                                <?= round($b['file_size'] / 1024, 1) ?> KB · <?= e($b['kategori'] ?? 'Umum') ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <a href="<?= base_url('bukti/preview/' . $b['id']) ?>" target="_blank"
                                                                       class="px-2.5 py-1 rounded-lg bg-brand-50 hover:bg-brand-600 text-brand-600 hover:text-white text-[11px] font-bold transition flex items-center space-x-1 shrink-0 shadow-xs"
                                                                       title="Buka Preview di Tab Baru">
                                                                        <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                                                                        <span class="text-[10px]">Preview</span>
                                                                    </a>
                                                                </div>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="inline-flex items-center space-x-1.5 text-[11px] text-slate-400 bg-slate-50 px-2.5 py-1 rounded-lg border border-slate-200/60 italic">
                                                             <i class="fa-regular fa-folder-open text-slate-400"></i>
                                                             <span>Belum ada dokumen bukti dukung diunggah</span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <!-- Kanan: Segmented Control Skor 1-4 & Catatan -->
                                            <div class="w-full lg:w-72 shrink-0 bg-white p-4 rounded-xl border border-slate-200 space-y-3">
                                                <div class="flex items-center justify-between gap-2">
                                                    <div class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Beri Skor Kinerja:</div>
                                                    <button type="button" onclick="bukaPanduanSkor()" title="Lihat panduan pemberian skor"
                                                            class="inline-flex items-center gap-1 text-[10px] font-bold text-brand-600 hover:text-brand-800 transition">
                                                        <i class="fa-solid fa-circle-question"></i>
                                                        <span>Panduan</span>
                                                    </button>
                                                </div>
                                                
                                                <div class="grid grid-cols-4 gap-1.5">
                                                    <?php for ($s = 1; $s <= 4; $s++): ?>
                                                        <label class="cursor-pointer select-none">
                                                            <input type="radio" name="skor_<?= $ind['id'] ?>" value="<?= $s ?>" 
                                                                <?= ((int)($ind['skor'] ?? 0) === $s) ? 'checked' : '' ?>
                                                                <?= ($penilaian['status'] === 'locked') ? 'disabled' : '' ?>
                                                                onchange="saveScore(<?= $penilaian['id'] ?>, <?= $ind['id'] ?>, <?= $s ?>)"
                                                                class="peer sr-only">
                                                            <div class="min-h-[44px] py-3 text-center text-xs font-bold rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 peer-checked:bg-brand-600 peer-checked:text-white peer-checked:border-brand-600 peer-checked:shadow-md peer-checked:ring-2 peer-checked:ring-brand-300 peer-disabled:opacity-60 transition-all flex items-center justify-center space-x-1">
                                                                <span><?= $s ?></span>
                                                                <i class="fa-solid fa-check text-[10px] hidden peer-checked:inline-block"></i>
                                                            </div>
                                                        </label>
                                                    <?php endfor; ?>
                                                </div>

                                                <!-- Catatan Penilai -->
                                                <div>
                                                    <input type="text" id="catatan_<?= $ind['id'] ?>" 
                                                        value="<?= e($ind['catatan_penilai'] ?? '') ?>"
                                                        placeholder="Catatan penilai (opsional)..."
                                                        <?= ($penilaian['status'] === 'locked') ? 'disabled' : '' ?>
                                                        onblur="saveScore(<?= $penilaian['id'] ?>, <?= $ind['id'] ?>, null)"
                                                        class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                                                </div>
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
</div>

<script>
function bukaPanduanSkor() {
    const d = document.getElementById('panduanSkor');
    if (!d) return;
    d.open = true;
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    d.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
}

async function saveScore(penilaianId, indikatorId, forcedSkor = null) {
    let skor = forcedSkor;
    if (!skor) {
        const checked = document.querySelector(`input[name="skor_${indikatorId}"]:checked`);
        if (checked) skor = checked.value;
    }
    if (!skor) return;

    const catatan = document.getElementById(`catatan_${indikatorId}`).value;

    const formData = new FormData();
    formData.append('penilaian_id', penilaianId);
    formData.append('indikator_id', indikatorId);
    formData.append('skor', skor);
    formData.append('catatan', catatan);

    try {
        const res = await fetch('<?= base_url('penilaian/save-score') ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            document.getElementById('answeredCount').innerText = data.answered_count;
            document.getElementById('liveTotalSkor').innerText = data.total_skor;
            document.getElementById('liveNilaiAkhir').innerText = data.nilai_akhir.toFixed(2);
            document.getElementById('livePredikat').innerText = data.predikat;
        } else {
            Swal.fire('Peringatan', data.message, 'warning');
        }
    } catch (e) {
        console.error(e);
    }
}

async function submitPenilaian(penilaianId) {
    const terisi = document.getElementById('answeredCount')?.innerText || '0';
    const total = '<?= $total_indikator ?>';
    
    let infoText = 'Setelah disimpan & dikunci, skor Anda tidak dapat diubah lagi tanpa persetujuan administrator.';
    if (parseInt(terisi) < parseInt(total)) {
        infoText = `Anda telah mengisi ${terisi} dari total ${total} butir. Nilai tetap dapat disimpan tanpa harus menunggu semua butir atau berkas lengkap. Lanjutkan simpan & kunci?`;
    }

    const confirm = await Swal.fire({
        title: 'Simpan & Kunci Nilai?',
        text: infoText,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Simpan & Kunci',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#059669'
    });

    if (!confirm.isConfirmed) return;

    const formData = new FormData();
    formData.append('penilaian_id', penilaianId);

    try {
        const res = await fetch('<?= base_url('penilaian/submit') ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Berhasil!', data.message, 'success').then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Kesalahan Jaringan', 'Gagal memproses submit.', 'error');
    }
}

async function unlockPenilaian(penilaianId) {
    const { value: alasan } = await Swal.fire({
        title: 'Buka Kunci Penilaian',
        input: 'textarea',
        inputLabel: 'Alasan Pembukaan Kunci (Wajib)',
        inputPlaceholder: 'Tuliskan alasan pembukaan kunci penilaian ini...',
        inputValidator: (value) => {
            if (!value) {
                return 'Alasan wajib diisi!';
            }
        },
        showCancelButton: true,
        confirmButtonText: 'Buka Kunci',
        confirmButtonColor: '#d97706'
    });

    if (!alasan) return;

    const formData = new FormData();
    formData.append('penilaian_id', penilaianId);
    formData.append('alasan', alasan);

    try {
        const res = await fetch('<?= base_url('penilaian/unlock') ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Berhasil', data.message, 'success').then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Kesalahan', 'Gagal memproses permintaan', 'error');
    }
}
</script>

<?php view('layout.footer'); ?>

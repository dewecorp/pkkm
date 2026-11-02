<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">
    <!-- Header Formulir -->
    <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4 sticky top-20 z-40">
        <div>
            <div class="flex items-center space-x-2">
                <span class="px-2.5 py-0.5 rounded text-xs font-bold bg-navy-700 text-white">
                    Kelompok: <?= e($penilaian['kode_kelompok']) ?> - <?= e($penilaian['nama_kelompok']) ?>
                </span>
                <span class="text-xs text-slate-500 font-medium">
                    Madrasah: <strong><?= e($penilaian['nama_madrasah']) ?></strong> (Kamad: <?= e($penilaian['nama_kamad']) ?>)
                </span>
            </div>
            <h2 class="text-lg font-bold text-navy-800 mt-1"><?= e($penilaian['nama_periode']) ?></h2>
        </div>

        <div class="flex items-center space-x-4 w-full md:w-auto justify-between md:justify-end">
            <!-- Progress Counter -->
            <div class="text-right">
                <div class="text-[11px] text-slate-500">Progres Pengisian:</div>
                <div class="text-sm font-bold text-navy-800">
                    <span id="answeredCount"><?= $terisi_count ?></span> / <?= $total_indikator ?> Butir
                </div>
            </div>

            <!-- Submit Button / Status Lock -->
            <?php if ($penilaian['status'] === 'submitted' || $penilaian['status'] === 'locked'): ?>
                <div class="px-4 py-2 bg-emerald-100 text-emerald-800 rounded-lg text-xs font-bold flex items-center space-x-1.5 border border-emerald-200">
                    <i class="fa-solid fa-lock"></i>
                    <span>Telah Disubmit</span>
                </div>
                <?php if (\App\Core\Auth::hasRole('admin')): ?>
                    <button onclick="unlockPenilaian(<?= $penilaian['id'] ?>)" class="px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-xs font-bold transition flex items-center space-x-1.5">
                        <i class="fa-solid fa-lock-open"></i>
                        <span>Buka Kunci</span>
                    </button>
                <?php endif; ?>
            <?php else: ?>
                <button onclick="submitPenilaian(<?= $penilaian['id'] ?>)" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition shadow flex items-center space-x-2">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Submit & Kunci Nilai</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Live Score Summary Bar -->
    <div class="bg-navy-800 text-white rounded-xl p-4 flex flex-wrap items-center justify-between gap-4 text-xs">
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
                <div class="bg-slate-100/80 px-6 py-4 border-b border-slate-200">
                    <span class="text-[11px] font-bold text-navy-700 uppercase tracking-wider">Komponen <?= $k['nomor_komponen'] ?></span>
                    <h3 class="text-base font-bold text-navy-900"><?= e($k['nama_komponen']) ?></h3>
                </div>

                <!-- Tugas Utama & Indikator -->
                <div class="divide-y divide-slate-200">
                    <?php foreach ($kData['tugas'] as $tData): ?>
                        <?php $t = $tData['tugas']; ?>
                        <div class="p-6">
                            <div class="mb-4">
                                <span class="px-2 py-0.5 rounded text-xs font-bold bg-navy-100 text-navy-800">
                                    Tugas <?= e($t['kode_tugas']) ?>
                                </span>
                                <h4 class="text-sm font-bold text-slate-800 mt-1"><?= e($t['nama_tugas']) ?></h4>
                            </div>

                            <!-- List Indikator -->
                            <div class="space-y-6">
                                <?php foreach ($tData['indikator'] as $ind): ?>
                                    <div class="bg-slate-50/60 rounded-xl p-5 border border-slate-200 hover:border-slate-300 transition" id="ind-card-<?= $ind['id'] ?>">
                                        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
                                            <!-- Kiri: Detail Indikator -->
                                            <div class="space-y-2 flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-200 text-slate-700">
                                                        <?= e($ind['kode_indikator']) ?>
                                                    </span>
                                                    <span class="font-bold text-xs text-navy-900"><?= e($ind['uraian_indikator']) ?></span>
                                                </div>

                                                <div class="text-xs text-slate-600 bg-white p-3 rounded-lg border border-slate-200/80">
                                                    <div class="font-semibold text-slate-700 mb-0.5">Data Kinerja yang Diharapkan:</div>
                                                    <div><?= nl2br(e($ind['data_kinerja_diharapkan'])) ?></div>
                                                </div>

                                                <div class="text-xs text-slate-500 italic">
                                                    <strong>Bukti Autentik Kualitas Kinerja:</strong> <?= e($ind['bukti_autentik']) ?>
                                                </div>

                                                <?php if ($ind['total_bukti'] > 0): ?>
                                                    <div class="pt-1">
                                                        <a href="<?= base_url('bukti?periode_id=' . $penilaian['periode_id']) ?>" target="_blank" class="inline-flex items-center space-x-1.5 text-xs text-emerald-700 hover:text-emerald-800 font-semibold bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200">
                                                            <i class="fa-solid fa-paperclip"></i>
                                                            <span>Ada <?= $ind['total_bukti'] ?> Dokumen Bukti Dukung (Lihat)</span>
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Kanan: Segmented Control Skor 1-4 & Catatan -->
                                            <div class="w-full lg:w-72 shrink-0 bg-white p-4 rounded-xl border border-slate-200 space-y-3">
                                                <div class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Beri Skor Kinerja:</div>
                                                
                                                <div class="grid grid-cols-4 gap-1.5">
                                                    <?php for ($s = 1; $s <= 4; $s++): ?>
                                                        <label class="cursor-pointer select-none">
                                                            <input type="radio" name="skor_<?= $ind['id'] ?>" value="<?= $s ?>" 
                                                                <?= ((int)($ind['skor'] ?? 0) === $s) ? 'checked' : '' ?>
                                                                <?= ($penilaian['status'] === 'submitted' || $penilaian['status'] === 'locked') ? 'disabled' : '' ?>
                                                                onchange="saveScore(<?= $penilaian['id'] ?>, <?= $ind['id'] ?>, <?= $s ?>)"
                                                                class="peer sr-only">
                                                            <div class="py-2.5 text-center text-xs font-bold rounded-xl border border-slate-200 text-slate-700 bg-white hover:bg-slate-50 peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600 peer-checked:shadow-md peer-checked:ring-2 peer-checked:ring-indigo-300 peer-disabled:opacity-60 transition-all flex items-center justify-center space-x-1">
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
                                                        <?= ($penilaian['status'] === 'submitted' || $penilaian['status'] === 'locked') ? 'disabled' : '' ?>
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
    const confirm = await Swal.fire({
        title: 'Submit Penilaian?',
        text: 'Setelah disubmit, seluruh skor Anda akan terkunci dan tidak dapat diubah lagi.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Submit Sekarang',
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

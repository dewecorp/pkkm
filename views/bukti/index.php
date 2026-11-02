<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">

<!-- ── Page Header ─────────────────────────────────────────── -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="flex items-center space-x-2">
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Bukti Dukung Kinerja</h1>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                <?= $totalUploaded ?> Berkas Terunggah
            </span>
        </div>
        <p class="text-sm text-slate-500 mt-0.5">Unggah berkas bukti autentik fisik langsung pada butir indikator masing-masing komponen</p>
    </div>

    <!-- Periode Selector -->
    <form method="GET" class="flex items-center space-x-2">
        <label class="text-xs font-semibold text-slate-500 shrink-0">Periode:</label>
        <select name="periode_id" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-brand-500 outline-none">
            <?php foreach ($periodeList as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $p['id'] == $currentPeriodeId ? 'selected' : '' ?>>
                <?= e($p['nama_periode']) ?> (<?= $p['tahun_penilaian'] ?>)
            </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<!-- ── 5 Komponen Tab Bar ──────────────────────────────────── -->
<div class="grid grid-cols-1 sm:grid-cols-5 gap-2 bg-slate-100/70 p-1.5 rounded-2xl">
    <?php foreach ($komponenList as $k): ?>
    <?php $isActive = ($k['id'] == $activeKompId); ?>
    <a href="?periode_id=<?= $currentPeriodeId ?>&komponen_id=<?= $k['id'] ?>"
       class="flex flex-col p-3 rounded-xl transition text-left <?= $isActive ? 'bg-white shadow text-brand-600 font-bold border border-slate-100' : 'text-slate-600 hover:bg-white/60 font-medium' ?>">
        <div class="flex items-center justify-between mb-1">
            <span class="text-[10px] uppercase tracking-wider font-extrabold px-1.5 py-0.5 rounded <?= $isActive ? 'bg-brand-50 text-brand-700' : 'bg-slate-200/60 text-slate-500' ?>">
                Komp. <?= $k['nomor_komponen'] ?>
            </span>
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full <?= $k['total_bukti'] > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'text-slate-400' ?>">
                <?= $k['total_bukti'] ?> Berkas
            </span>
        </div>
        <span class="text-xs leading-tight line-clamp-2 <?= $isActive ? 'text-slate-800' : 'text-slate-600' ?>">
            <?= e($k['nama_komponen']) ?>
        </span>
    </a>
    <?php endforeach; ?>
</div>

<!-- ── Search Filter ───────────────────────────────────────── -->
<div class="flex items-center justify-between gap-4">
    <div class="relative flex-1 max-w-md">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" id="buktiSearch" placeholder="Cari butir indikator, kode, atau nama berkas..."
            class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
    </div>
    <div class="text-xs text-slate-400">
        Menampilkan <?= count($tugasList) ?> Tugas Utama
    </div>
</div>

<!-- ── Tugas Utama & Indikator Table ───────────────────────── -->
<div class="space-y-5" id="buktiContainer">
    <?php if (!empty($tugasList)): ?>
    <?php foreach ($tugasList as $t): ?>
    <?php $indikators = $indikatorByTugas[$t['id']] ?? []; ?>
    <div class="card overflow-hidden tugas-card" data-tugas="<?= strtolower($t['kode_tugas'] . ' ' . $t['nama_tugas']) ?>">

        <!-- Header Tugas Utama -->
        <div class="px-6 py-3.5 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="px-2 py-0.5 rounded-lg font-bold text-xs text-brand-700 bg-brand-50 border border-brand-200">
                    Tugas <?= e($t['kode_tugas']) ?>
                </span>
                <h3 class="text-xs font-bold text-slate-800"><?= e($t['nama_tugas']) ?></h3>
            </div>
            <span class="text-[11px] text-slate-400"><?= count($indikators) ?> Butir Indikator</span>
        </div>

        <!-- Tabel Butir Indikator -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-white border-b border-slate-100 text-slate-400 uppercase text-[10px] font-semibold tracking-wider">
                    <tr>
                        <th class="px-6 py-3 w-16">Kode</th>
                        <th class="px-6 py-3 w-1/3">Uraian Indikator & Bukti Diharapkan</th>
                        <th class="px-6 py-3">Berkas Bukti Terunggah</th>
                        <th class="px-6 py-3 text-right w-36">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($indikators)): ?>
                    <?php foreach ($indikators as $ind): ?>
                    <?php
                        $fileSearch = '';
                        foreach ($ind['files'] as $f) {
                            $fileSearch .= ' ' . $f['nama_dokumen'] . ' ' . $f['kategori'];
                        }
                    ?>
                    <tr class="hover:bg-slate-50/70 transition indikator-row"
                        data-search="<?= strtolower($ind['kode_indikator'] . ' ' . $ind['uraian_indikator'] . ' ' . $ind['bukti_autentik'] . $fileSearch) ?>">
                        <!-- Kode -->
                        <td class="px-6 py-4 align-top">
                            <span class="font-bold text-brand-600 text-xs"><?= e($ind['kode_indikator']) ?></span>
                        </td>

                        <!-- Uraian -->
                        <td class="px-6 py-4 align-top space-y-1.5">
                            <div class="font-semibold text-slate-800 leading-relaxed"><?= e($ind['uraian_indikator']) ?></div>
                            <div class="text-[11px] text-slate-500 leading-relaxed bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                <span class="font-semibold text-slate-600">Bukti Autentik:</span> <?= e($ind['bukti_autentik']) ?>
                            </div>
                        </td>

                        <!-- Berkas Terunggah -->
                        <td class="px-6 py-4 align-top">
                            <?php if (!empty($ind['files'])): ?>
                            <div class="space-y-2">
                                <?php foreach ($ind['files'] as $f): ?>
                                <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-200/70 hover:border-brand-300 transition">
                                    <div class="flex items-center space-x-2.5 min-w-0 pr-2">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-[10px] uppercase bg-brand-50 text-brand-600 shrink-0">
                                            <?= e($f['file_ext']) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <a href="<?= base_url($f['file_path']) ?>" target="_blank"
                                                class="font-semibold text-slate-800 hover:text-brand-600 text-xs truncate block"
                                                title="<?= e($f['nama_dokumen']) ?>">
                                                <?= e($f['nama_dokumen']) ?>
                                            </a>
                                            <div class="text-[10px] text-slate-400">
                                                <?= round($f['file_size'] / 1024, 1) ?> KB · <?= e($f['pengunggah']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex items-center space-x-1 shrink-0">
                                        <a href="<?= base_url($f['file_path']) ?>" target="_blank"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition"
                                            title="Lihat Berkas">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                                        </a>
                                        <?php if (\App\Core\Auth::hasAnyRole(['admin', 'kamad'])): ?>
                                        <button onclick="delBukti(<?= $f['id'] ?>, '<?= e($f['nama_dokumen']) ?>')"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 transition"
                                            title="Hapus Berkas">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <span class="text-xs text-slate-400 italic">Belum ada berkas diunggah</span>
                            <?php endif; ?>
                        </td>

                        <!-- Aksi Upload -->
                        <td class="px-6 py-4 align-top text-right">
                            <?php if (\App\Core\Auth::hasAnyRole(['admin', 'kamad'])): ?>
                            <button onclick="openUploadModal(<?= $ind['id'] ?>, '<?= e($ind['kode_indikator']) ?>', '<?= htmlspecialchars(addslashes($ind['uraian_indikator'])) ?>')"
                                class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 transition shadow-sm"
                                title="Unggah berkas untuk butir ini">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Unggah</span>
                            </button>
                            <?php else: ?>
                            <span class="text-[11px] text-slate-400">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="card p-12 text-center text-slate-400">
        <i class="fa-solid fa-folder-open text-3xl mb-2 block"></i>
        Tidak ada butir indikator pada komponen ini.
    </div>
    <?php endif; ?>
</div>

</div><!-- /space-y-6 -->

<!-- ── MODAL UNGGAH BUKTI DUKUNG BUTIR ─────────────────────── -->
<div id="uploadModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 sm:p-6 overflow-y-auto">
<div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col max-h-[90vh] my-auto">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
        <div>
            <h3 class="font-bold text-slate-800 text-base">Unggah Bukti Dukung</h3>
            <p id="modalIndikatorBadge" class="text-xs text-brand-600 font-semibold mt-0.5"></p>
        </div>
        <button onclick="closeUploadModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <form id="uploadForm" class="flex flex-col flex-1 min-h-0">
        <input type="hidden" name="periode_id" value="<?= $currentPeriodeId ?>">
        <input type="hidden" name="indikator_id" id="uploadIndikatorId">

        <div class="p-6 space-y-4 overflow-y-auto flex-1">
            <!-- Indikator Target Preview -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-600 leading-relaxed" id="modalIndikatorText">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Dokumen <span class="text-red-500">*</span></label>
                <input type="text" name="nama_dokumen" id="uploadNamaDokumen" required placeholder="Contoh: SK Tim Pengembang Madrasah"
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kategori Dokumen</label>
                    <select name="kategori" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="SK / Kebijakan">SK / Kebijakan</option>
                        <option value="Laporan Kegiatan">Laporan Kegiatan</option>
                        <option value="Foto Dokumentasi">Foto Dokumentasi</option>
                        <option value="Kurikulum & Pembelajaran">Kurikulum & Pembelajaran</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">File Berkas <span class="text-red-500">*</span></label>
                    <input type="file" name="file" required
                        class="w-full text-xs text-slate-500 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi / Catatan Tambahan</label>
                <textarea name="deskripsi" rows="2" placeholder="Keterangan singkat berkas..."
                    class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-xs leading-relaxed focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
            </div>
            <p class="text-[10px] text-slate-400">Format: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Maks 10MB)</p>
        </div>

        <div class="flex justify-end space-x-2 px-6 py-4 border-t border-slate-100 bg-slate-50/50 shrink-0">
            <button type="button" onclick="closeUploadModal()" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
            <button type="submit" id="btnUploadSubmit" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white transition"
                style="background:linear-gradient(135deg,#6366f1,#4338ca);">Unggah Berkas</button>
        </div>
    </form>
</div>
</div>

<script>
function openUploadModal(indId, kode, uraian) {
    document.getElementById('uploadModal').classList.remove('hidden');
    document.getElementById('uploadModal').classList.add('flex');
    document.getElementById('uploadIndikatorId').value = indId;
    document.getElementById('modalIndikatorBadge').innerText = 'Butir Indikator: ' + kode;
    document.getElementById('modalIndikatorText').innerText = uraian;
    document.getElementById('uploadNamaDokumen').focus();
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadModal').classList.remove('flex');
    document.getElementById('uploadForm').reset();
}

// Search Filter
document.getElementById('buktiSearch').addEventListener('input', function(e) {
    const q = e.target.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.indikator-row');
    const cards = document.querySelectorAll('.tugas-card');

    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        row.style.display = text.includes(q) ? '' : 'none';
    });

    cards.forEach(card => {
        const visibleRows = card.querySelectorAll('.indikator-row:not([style*="display: none"])');
        card.style.display = (visibleRows.length > 0 || q === '') ? '' : 'none';
    });
});

document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnUploadSubmit');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Mengunggah...';

    const formData = new FormData(this);
    try {
        const response = await fetch('<?= base_url('bukti/upload') ?>', { method: 'POST', body: formData });
        const res = await response.json();

        if (res.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false })
                .then(() => { closeUploadModal(); location.reload(); });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            btn.disabled = false;
            btn.innerText = 'Unggah Berkas';
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Kesalahan Sistem', text: 'Gagal mengunggah berkas.' });
        btn.disabled = false;
        btn.innerText = 'Unggah Berkas';
    }
});

async function delBukti(id, nama) {
    const c = await Swal.fire({
        title: 'Hapus Berkas?',
        text: `Berkas "${nama}" akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    });
    if (!c.isConfirmed) return;

    const res = await fetch('<?= base_url('bukti/delete/') ?>' + id, { method: 'POST' }).then(r => r.json());
    if (res.success) {
        Swal.fire({ icon: 'success', title: 'Terhapus!', text: res.message, timer: 1200, showConfirmButton: false })
            .then(() => location.reload());
    } else {
        Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
    }
}
</script>

<?php view('layout.footer'); ?>

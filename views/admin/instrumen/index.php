<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">

<!-- ── Page Header ─────────────────────────────────────────── -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="flex items-center space-x-2">
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Master Instrumen Penilaian</h1>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                <?= $grandTotal ?> Indikator Baku
            </span>
        </div>
        <p class="text-sm text-slate-500 mt-0.5">Struktur 5 komponen baku, 29 tugas utama, dan butir indikator penilaian PKKM</p>
    </div>

    <!-- Version selector + Action -->
    <div class="flex items-center space-x-3">
        <form method="GET" class="flex items-center space-x-2">
            <label class="text-xs font-semibold text-slate-500">Versi:</label>
            <select name="versi_id" onchange="this.form.submit()" class="px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs font-semibold text-slate-700 focus:ring-2 focus:ring-brand-500 outline-none">
                <?php foreach ($versiList as $v): ?>
                <option value="<?= $v['id'] ?>" <?= $v['id'] == $currentVersiId ? 'selected' : '' ?>>
                    v<?= e($v['nomor_versi']) ?> — <?= e($v['nama_versi']) ?> (<?= ucfirst($v['status']) ?>)
                </option>
                <?php endforeach; ?>
            </select>
        </form>

        <button onclick="openAddIndikator()" class="inline-flex items-center space-x-2 px-4 py-2 rounded-xl text-xs font-bold text-white shadow" style="background:linear-gradient(135deg,#6366f1,#4338ca);">
            <i class="fa-solid fa-plus"></i><span>Tambah Indikator</span>
        </button>
    </div>
</div>

<!-- ── 5 Komponen Tab Bar ──────────────────────────────────── -->
<div class="grid grid-cols-1 sm:grid-cols-5 gap-2 bg-slate-100/70 p-1.5 rounded-2xl">
    <?php foreach ($komponenList as $k): ?>
    <?php $isActive = ($k['id'] == $activeKompId); ?>
    <a href="?versi_id=<?= $currentVersiId ?>&komponen_id=<?= $k['id'] ?>"
       class="flex flex-col p-3 rounded-xl transition text-left <?= $isActive ? 'bg-white shadow text-brand-600 font-bold border border-slate-100' : 'text-slate-600 hover:bg-white/60 font-medium' ?>">
        <div class="flex items-center justify-between mb-1">
            <span class="text-[10px] uppercase tracking-wider font-extrabold px-1.5 py-0.5 rounded <?= $isActive ? 'bg-brand-50 text-brand-700' : 'bg-slate-200/60 text-slate-500' ?>">
                Komp. <?= $k['nomor_komponen'] ?>
            </span>
            <span class="text-[11px] font-bold <?= $isActive ? 'text-brand-600' : 'text-slate-400' ?>">
                <?= $k['total_indikator'] ?> Butir
            </span>
        </div>
        <span class="text-xs leading-tight line-clamp-2 <?= $isActive ? 'text-slate-800' : 'text-slate-600' ?>">
            <?= e($k['nama_komponen']) ?>
        </span>
    </a>
    <?php endforeach; ?>
</div>

<!-- ── Quick Search Filter ─────────────────────────────────── -->
<div class="flex items-center justify-between gap-4">
    <div class="relative flex-1 max-w-md">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" id="indikatorSearch" placeholder="Cari uraian indikator, kode, atau bukti autentik..."
            class="w-full pl-9 pr-4 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
    </div>
    <div class="text-xs text-slate-400">
        Menampilkan <?= count($tugasList) ?> Tugas Utama di Komponen ini
    </div>
</div>

<!-- ── Tugas Utama & Indikator Accordions ───────────────────── -->
<div class="space-y-4" id="tugasContainer">
    <?php if (!empty($tugasList)): ?>
    <?php foreach ($tugasList as $t): ?>
    <?php $indikators = $indikatorByTugas[$t['id']] ?? []; ?>
    <div class="card overflow-hidden tugas-block" data-tugas="<?= strtolower($t['kode_tugas'] . ' ' . $t['nama_tugas']) ?>">

        <!-- Header Tugas Utama -->
        <div class="px-6 py-4 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <span class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs text-brand-700 bg-brand-50 border border-brand-200">
                    <?= e($t['kode_tugas']) ?>
                </span>
                <div>
                    <h3 class="text-sm font-bold text-slate-800"><?= e($t['nama_tugas']) ?></h3>
                    <p class="text-[11px] text-slate-400"><?= count($indikators) ?> Butir Indikator Penilaian</p>
                </div>
            </div>
            <button onclick="toggleTugas(<?= $t['id'] ?>)" class="text-slate-400 hover:text-slate-600 p-1 text-xs">
                <i class="fa-solid fa-chevron-down transition-transform duration-200" id="chevron-<?= $t['id'] ?>"></i>
            </button>
        </div>

        <!-- Table of Indicators -->
        <div id="tugas-content-<?= $t['id'] ?>" class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-white border-b border-slate-100 text-slate-400 uppercase text-[10px] font-semibold tracking-wider">
                    <tr>
                        <th class="px-6 py-3 w-20">Kode</th>
                        <th class="px-6 py-3 w-1/3">Uraian Indikator Kinerja</th>
                        <th class="px-6 py-3 w-1/4">Data Kinerja Diharapkan</th>
                        <th class="px-6 py-3">Bukti Autentik</th>
                        <th class="px-6 py-3 text-center w-20">Skor Max</th>
                        <th class="px-6 py-3 text-right w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if (!empty($indikators)): ?>
                    <?php foreach ($indikators as $ind): ?>
                    <tr class="hover:bg-slate-50/70 transition indikator-row"
                        data-search="<?= strtolower($ind['kode_indikator'] . ' ' . $ind['uraian_indikator'] . ' ' . $ind['data_kinerja_diharapkan'] . ' ' . $ind['bukti_autentik']) ?>">
                        <td class="px-6 py-4 align-top">
                            <span class="font-bold text-brand-600"><?= e($ind['kode_indikator']) ?></span>
                        </td>
                        <td class="px-6 py-4 align-top">
                            <div class="font-semibold text-slate-800 leading-relaxed"><?= e($ind['uraian_indikator']) ?></div>
                        </td>
                        <td class="px-6 py-4 align-top text-slate-600 leading-relaxed">
                            <?= e($ind['data_kinerja_diharapkan'] ?: '—') ?>
                        </td>
                        <td class="px-6 py-4 align-top text-slate-500 leading-relaxed">
                            <?= e($ind['bukti_autentik'] ?: '—') ?>
                        </td>
                        <td class="px-6 py-4 align-top text-center font-bold text-slate-700">
                            <?= $ind['skor_maksimal'] ?>
                        </td>
                        <td class="px-6 py-4 align-top text-right">
                            <div class="flex items-center justify-end space-x-1.5">
                                <button onclick='editIndikator(<?= htmlspecialchars(json_encode($ind)) ?>)'
                                    title="Edit Indikator"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 border border-slate-200 text-slate-600 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 transition">
                                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                                </button>
                                <button onclick="delIndikator(<?= $ind['id'] ?>, '<?= e($ind['kode_indikator']) ?>')"
                                    title="Hapus Indikator"
                                    class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 border border-slate-200 text-slate-600 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
                                    <i class="fa-solid fa-trash text-xs"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-slate-400">
                            Belum ada butir indikator pada tugas utama ini.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="card p-12 text-center text-slate-400">
        <i class="fa-solid fa-book-open text-3xl mb-2 block"></i>
        Tidak ada data tugas utama pada komponen ini.
    </div>
    <?php endif; ?>
</div>

</div><!-- /space-y-6 -->

<!-- ── MODAL EDIT INDIKATOR ─────────────────────────────────── -->
<div id="modalEditInd" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 sm:p-6 overflow-y-auto">
<div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] my-auto">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
        <div>
            <h3 class="font-bold text-slate-800 text-base">Edit Indikator Penilaian</h3>
            <p id="editIndKode" class="text-xs text-brand-600 font-semibold mt-0.5"></p>
        </div>
        <button onclick="closeEditInd()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <form id="formEditInd" class="flex flex-col flex-1 min-h-0">
        <input type="hidden" name="id" id="editIndId">

        <div class="p-6 space-y-4 overflow-y-auto flex-1">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Uraian Indikator Kinerja <span class="text-red-500">*</span></label>
                <textarea name="uraian_indikator" id="editIndUraian" rows="3" required
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs leading-relaxed focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Data Kinerja yang Diharapkan</label>
                <textarea name="data_kinerja_diharapkan" id="editIndData" rows="2"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs leading-relaxed focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Bukti Autentik</label>
                <textarea name="bukti_autentik" id="editIndBukti" rows="4"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs leading-relaxed focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
            </div>

            <div class="w-1/3">
                <label class="block text-xs font-semibold text-slate-600 mb-1">Skor Maksimal</label>
                <input type="number" name="skor_maksimal" id="editIndSkor" min="1" max="10" value="4"
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
            </div>
        </div>

        <div class="flex justify-end space-x-2 px-6 py-4 border-t border-slate-100 bg-slate-50/50 shrink-0">
            <button type="button" onclick="closeEditInd()" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
            <button type="submit" id="btnEditIndSave" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white transition"
                style="background:linear-gradient(135deg,#6366f1,#4338ca);">Simpan Perubahan</button>
        </div>
    </form>
</div>
</div>

<!-- ── MODAL TAMBAH INDIKATOR ───────────────────────────────── -->
<div id="modalAddInd" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 sm:p-6 overflow-y-auto">
<div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] my-auto">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
        <h3 class="font-bold text-slate-800 text-base">Tambah Indikator Baru</h3>
        <button onclick="closeAddInd()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <form id="formAddInd" class="flex flex-col flex-1 min-h-0">
        <div class="p-6 space-y-4 overflow-y-auto flex-1">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tugas Utama <span class="text-red-500">*</span></label>
                    <select name="tugas_id" id="addIndTugas" required class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">-- Pilih Tugas Utama --</option>
                        <?php foreach ($tugasList as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= e($t['kode_tugas']) ?> — <?= e($t['nama_tugas']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kode Indikator <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_indikator" required placeholder="cth. 1.1.5"
                        class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Uraian Indikator Kinerja <span class="text-red-500">*</span></label>
                <textarea name="uraian_indikator" rows="3" required placeholder="Uraian indikator..."
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs leading-relaxed focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Data Kinerja yang Diharapkan</label>
                <textarea name="data_kinerja_diharapkan" rows="2" placeholder="Dokumen atau data kinerja yang diharapkan..."
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs leading-relaxed focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Bukti Autentik</label>
                <textarea name="bukti_autentik" rows="3" placeholder="Pedoman observasi / dokumen bukti fisik..."
                    class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs leading-relaxed focus:ring-2 focus:ring-brand-500 outline-none"></textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nomor Sub / Urutan</label>
                    <input type="number" name="nomor_sub" min="1" value="1"
                        class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Skor Maksimal</label>
                    <input type="number" name="skor_maksimal" min="1" max="10" value="4"
                        class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-2 px-6 py-4 border-t border-slate-100 bg-slate-50/50 shrink-0">
            <button type="button" onclick="closeAddInd()" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
            <button type="submit" id="btnAddIndSave" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white transition"
                style="background:linear-gradient(135deg,#6366f1,#4338ca);">Simpan Indikator</button>
        </div>
    </form>
</div>
</div>

<script>
// Toggle accordion
function toggleTugas(id) {
    const content = document.getElementById('tugas-content-' + id);
    const chevron = document.getElementById('chevron-' + id);
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        chevron.classList.remove('-rotate-90');
    } else {
        content.classList.add('hidden');
        chevron.classList.add('-rotate-90');
    }
}

// Search filter
document.getElementById('indikatorSearch').addEventListener('input', function(e) {
    const q = e.target.value.toLowerCase().trim();
    const rows = document.querySelectorAll('.indikator-row');
    const tugasBlocks = document.querySelectorAll('.tugas-block');

    rows.forEach(row => {
        const text = row.getAttribute('data-search') || '';
        const match = text.includes(q);
        row.style.display = match ? '' : 'none';
    });

    tugasBlocks.forEach(block => {
        const visibleRows = block.querySelectorAll('.indikator-row:not([style*="display: none"])');
        block.style.display = (visibleRows.length > 0 || q === '') ? '' : 'none';
    });
});

// Edit Indikator Modal
function editIndikator(ind) {
    document.getElementById('modalEditInd').classList.remove('hidden');
    document.getElementById('modalEditInd').classList.add('flex');
    document.getElementById('editIndId').value = ind.id;
    document.getElementById('editIndKode').innerText = 'Kode Indikator: ' + ind.kode_indikator;
    document.getElementById('editIndUraian').value = ind.uraian_indikator || '';
    document.getElementById('editIndData').value = ind.data_kinerja_diharapkan || '';
    document.getElementById('editIndBukti').value = ind.bukti_autentik || '';
    document.getElementById('editIndSkor').value = ind.skor_maksimal || 4;
}

function closeEditInd() {
    document.getElementById('modalEditInd').classList.add('hidden');
    document.getElementById('modalEditInd').classList.remove('flex');
    document.getElementById('formEditInd').reset();
}

document.getElementById('formEditInd').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnEditIndSave');
    btn.disabled = true;
    btn.innerText = 'Menyimpan...';

    const res = await fetch('<?= base_url('admin/instrumen/indikator/update') ?>', {
        method: 'POST',
        body: new FormData(this)
    }).then(r => r.json());

    Swal.fire(res.success ? 'Berhasil' : 'Gagal', res.message, res.success ? 'success' : 'error')
        .then(() => { if (res.success) { closeEditInd(); location.reload(); } });

    btn.disabled = false;
    btn.innerText = 'Simpan Perubahan';
});

// Add Indikator Modal
function openAddIndikator() {
    document.getElementById('modalAddInd').classList.remove('hidden');
    document.getElementById('modalAddInd').classList.add('flex');
    document.getElementById('formAddInd').reset();
}

function closeAddInd() {
    document.getElementById('modalAddInd').classList.add('hidden');
    document.getElementById('modalAddInd').classList.remove('flex');
    document.getElementById('formAddInd').reset();
}

document.getElementById('formAddInd').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnAddIndSave');
    btn.disabled = true;
    btn.innerText = 'Menyimpan...';

    const res = await fetch('<?= base_url('admin/instrumen/indikator/store') ?>', {
        method: 'POST',
        body: new FormData(this)
    }).then(r => r.json());

    Swal.fire(res.success ? 'Berhasil' : 'Gagal', res.message, res.success ? 'success' : 'error')
        .then(() => { if (res.success) { closeAddInd(); location.reload(); } });

    btn.disabled = false;
    btn.innerText = 'Simpan Indikator';
});

// Delete Indikator
async function delIndikator(id, kode) {
    const c = await Swal.fire({
        title: 'Hapus Indikator?',
        text: `Indikator ${kode} akan dihapus dari instrumen master.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    });
    if (!c.isConfirmed) return;

    const fd = new FormData();
    fd.append('id', id);
    const res = await fetch('<?= base_url('admin/instrumen/indikator/delete') ?>', {
        method: 'POST',
        body: fd
    }).then(r => r.json());

    Swal.fire(res.success ? 'Berhasil' : 'Gagal', res.message, res.success ? 'success' : 'error')
        .then(() => { if (res.success) location.reload(); });
}
</script>

<?php view('layout.footer'); ?>

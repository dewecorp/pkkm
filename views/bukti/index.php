<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">

<!-- ── Page Header ─────────────────────────────────────────── -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <div class="flex items-center space-x-2.5">
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Bukti Dukung Kinerja</h1>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-brand-50 text-brand-700 border border-brand-200">
                <?= $totalUploaded ?> Berkas Terunggah
            </span>
        </div>
        <p class="text-sm text-slate-500 mt-0.5">Unggah berkas bukti autentik langsung pada butir indikator per komponen (mendukung multi upload berkas sekaligus)</p>
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

<!-- ── Ringkasan Komponen Tab Bar (Menyesuaikan Jumlah Komponen Dinamis) ── -->
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-<?= max(1, min(count($komponenList), 6)) ?> gap-2.5 bg-slate-100/70 p-2 rounded-2xl">
    <?php foreach ($komponenList as $k): ?>
    <?php $isActive = ($k['id'] == $activeKompId); ?>
    <a href="?periode_id=<?= $currentPeriodeId ?>&komponen_id=<?= $k['id'] ?>"
       class="flex flex-col p-3 rounded-xl transition text-left <?= $isActive ? 'bg-white shadow text-brand-600 font-bold border border-slate-100' : 'text-slate-600 hover:bg-white/60 font-medium' ?>">
        <div class="flex items-center justify-between mb-1.5">
            <span class="text-[10px] uppercase tracking-wider font-extrabold px-1.5 py-0.5 rounded <?= $isActive ? 'bg-brand-50 text-brand-700' : 'bg-slate-200/60 text-slate-500' ?>">
                Komp. <?= $k['nomor_komponen'] ?>
            </span>
            <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full <?= $k['total_bukti'] > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'text-slate-400' ?>">
                <?= $k['total_bukti'] ?> Berkas
            </span>
        </div>
        <span class="text-xs leading-snug line-clamp-2 <?= $isActive ? 'text-slate-800' : 'text-slate-600' ?>">
            <?= e($k['nama_komponen']) ?>
        </span>
    </a>
    <?php endforeach; ?>
</div>

<!-- ── Search Filter ───────────────────────────────────────── -->
<div class="flex items-center justify-between gap-4">
    <div class="relative flex-1 max-w-md">
        <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
        <input type="text" id="buktiSearch" placeholder="Cari kode indikator, uraian, atau nama berkas..."
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
            <table class="table-stack w-full text-left text-xs">
                <thead class="bg-white border-b border-slate-100 text-slate-400 uppercase text-[10px] font-semibold tracking-wider">
                    <tr>
                        <th class="px-6 py-3 w-16">Kode</th>
                        <th class="px-6 py-3 w-1/3">Uraian Indikator & Bukti Diharapkan</th>
                        <th class="px-6 py-3">Berkas Bukti Terunggah</th>
                        <?php if (\App\Core\Auth::hasAnyRole(['admin', 'kamad'])): ?>
                        <th class="px-6 py-3 text-right w-40">Aksi</th>
                        <?php endif; ?>
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
                        <td class="px-6 py-4 align-top" data-label="Kode">
                            <span class="font-bold text-brand-600 text-xs"><?= e($ind['kode_indikator']) ?></span>
                        </td>

                        <!-- Uraian -->
                        <td class="px-6 py-4 align-top space-y-1.5" data-label="Uraian Indikator & Bukti Diharapkan">
                            <div class="font-semibold text-slate-800 leading-relaxed"><?= e($ind['uraian_indikator']) ?></div>
                            <div class="text-[11px] text-slate-500 leading-relaxed bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                                <span class="font-semibold text-slate-600">Bukti Autentik:</span> <?= e($ind['bukti_autentik']) ?>
                            </div>
                        </td>

                        <!-- Berkas Terunggah -->
                        <td class="px-6 py-4 align-top" data-label="Berkas Bukti Terunggah">
                            <?php if (!empty($ind['files'])): ?>
                            <div class="space-y-2">
                                <?php foreach ($ind['files'] as $fIdx => $f): ?>
                                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-200/70 hover:border-brand-300 transition group">
                                    <div class="flex items-center space-x-2.5 min-w-0 pr-2">
                                        <!-- Nomor Urut Bukti -->
                                        <span class="w-6 h-6 rounded-lg bg-white border border-slate-200 text-slate-700 font-bold text-[11px] flex items-center justify-center shrink-0 shadow-xs">
                                            <?= $fIdx + 1 ?>
                                        </span>
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-[10px] uppercase bg-brand-50 text-brand-600 shrink-0">
                                            <?= e($f['file_ext']) ?>
                                        </div>
                                        <div class="min-w-0">
                                            <?php if (\App\Core\Auth::hasAnyRole(['admin', 'kamad'])): ?>
                                            <a href="<?= base_url('bukti/preview/' . $f['id']) ?>" target="_blank"
                                                class="font-semibold text-slate-800 hover:text-brand-600 text-xs truncate block"
                                                title="Buka preview: <?= e($f['nama_dokumen']) ?>">
                                                <?= e($f['nama_dokumen']) ?>
                                            </a>
                                            <?php else: ?>
                                            <span class="font-semibold text-slate-800 text-xs truncate block" title="<?= e($f['nama_dokumen']) ?>">
                                                <?= e($f['nama_dokumen']) ?>
                                            </span>
                                            <?php endif; ?>
                                            <div class="text-[10px] text-slate-400">
                                                <?= round($f['file_size'] / 1024, 1) ?> KB · <?= e($f['kategori'] ?? 'Umum') ?> · <?= e($f['pengunggah']) ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (\App\Core\Auth::hasAnyRole(['admin', 'kamad'])): ?>
                                    <div class="flex items-center space-x-1 shrink-0">
                                        <!-- Preview Tab Baru -->
                                        <a href="<?= base_url('bukti/preview/' . $f['id']) ?>" target="_blank"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition"
                                            title="Buka Preview di Tab Baru">
                                            <i class="fa-solid fa-arrow-up-right-from-square text-[11px]"></i>
                                        </a>
                                        <!-- Edit Berkas / Informasi -->
                                        <button type="button" onclick="openEditModal(<?= htmlspecialchars(json_encode([
                                            'id' => $f['id'],
                                            'nama_dokumen' => $f['nama_dokumen'],
                                            'kategori' => $f['kategori'],
                                            'deskripsi' => $f['deskripsi'] ?? '',
                                            'file_name' => $f['file_name'],
                                            'file_ext' => $f['file_ext'],
                                            'file_size' => round($f['file_size'] / 1024, 1) . ' KB'
                                        ]), ENT_QUOTES, 'UTF-8') ?>)"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition"
                                            title="Ubah Berkas / Keterangan">
                                            <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                                        </button>
                                        <!-- Hapus Berkas -->
                                        <button type="button" onclick="delBukti(<?= $f['id'] ?>, '<?= e($f['nama_dokumen']) ?>')"
                                            class="w-7 h-7 rounded-lg flex items-center justify-center text-slate-400 hover:text-red-600 hover:bg-red-50 transition"
                                            title="Hapus Berkas">
                                            <i class="fa-solid fa-trash text-[11px]"></i>
                                        </button>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php else: ?>
                            <span class="text-xs text-slate-400 italic">Belum ada berkas diunggah</span>
                            <?php endif; ?>
                        </td>

                        <?php if (\App\Core\Auth::hasAnyRole(['admin', 'kamad'])): ?>
                        <!-- Aksi Upload -->
                        <td class="px-6 py-4 align-top text-right" data-label="Aksi">
                            <div class="table-stack-actions">
                            <button onclick="openUploadModal(<?= $ind['id'] ?>, '<?= e($ind['kode_indikator']) ?>', '<?= htmlspecialchars(addslashes($ind['uraian_indikator'])) ?>')"
                                class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-brand-700 bg-brand-50 hover:bg-brand-100 border border-brand-200 transition shadow-sm"
                                title="Unggah berkas untuk butir ini">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                                <span>Upload</span>
                            </button>
                            </div>
                        </td>
                        <?php endif; ?>
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

<!-- ── MODAL MULTI UPLOAD BUKTI DUKUNG BUTIR ─────────────────── -->
<div id="uploadModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 sm:p-6 overflow-y-auto">
<div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[92vh] my-auto">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
        <div>
            <h3 class="font-bold text-slate-800 text-base flex items-center space-x-2">
                <i class="fa-solid fa-cloud-arrow-up text-brand-600"></i>
                <span>Unggah Bukti Dukung</span>
            </h3>
            <p id="modalIndikatorBadge" class="text-xs text-brand-600 font-semibold mt-0.5"></p>
        </div>
        <button onclick="closeUploadModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Form -->
    <form id="uploadForm" class="flex flex-col flex-1 min-h-0" enctype="multipart/form-data">
        <input type="hidden" name="periode_id" value="<?= $currentPeriodeId ?>">
        <input type="hidden" name="indikator_id" id="uploadIndikatorId">

        <div class="p-6 space-y-4 overflow-y-auto flex-1">
            <!-- Indikator Target Preview -->
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-600 leading-relaxed" id="modalIndikatorText">
            </div>

            <!-- Dynamic Upload Fields Container -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <label class="block text-xs font-bold text-slate-700">
                            Berkas Bukti Dukung
                        </label>
                        <p class="text-[11px] text-slate-400 mt-0.5">Tambah baris berkas sesuai kebutuhan (PDF, DOC, DOCX, XLS, XLSX, JPG, PNG maks 10MB)</p>
                    </div>
                    <button type="button" onclick="addUploadRow()"
                        class="inline-flex items-center space-x-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-white shadow transition"
                        style="background:linear-gradient(135deg,#1e6347,#17503a);">
                        <i class="fa-solid fa-plus text-[10px]"></i>
                        <span>Tambah Berkas</span>
                    </button>
                </div>

                <!-- Container baris-baris upload -->
                <div id="uploadRowsContainer" class="space-y-3">
                    <!-- Dynamic Rows appended via JavaScript -->
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-between items-center px-6 py-4 border-t border-slate-100 bg-slate-50/50 shrink-0">
            <span class="text-[11px] text-slate-500 font-medium" id="totalRowsCount">Total: 1 berkas</span>
            <div class="flex space-x-2">
                <button type="button" onclick="closeUploadModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
                <button type="submit" id="btnUploadSubmit" class="px-5 py-2 rounded-xl text-xs font-bold text-white transition flex items-center space-x-1.5"
                    style="background:linear-gradient(135deg,#1e6347,#17503a);">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Unggah Semua Berkas</span>
                </button>
            </div>
        </div>
    </form>
</div>
</div>

<!-- ── MODAL EDIT / GANTI BERKAS BUKTI DUKUNG ─────────────────── -->
<div id="editModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 sm:p-6 overflow-y-auto">
<div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col my-auto">
    <!-- Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
        <h3 class="font-bold text-slate-800 text-base flex items-center space-x-2">
            <i class="fa-solid fa-pen-to-square text-amber-500"></i>
            <span>Ubah Berkas Bukti Dukung</span>
        </h3>
        <button onclick="closeEditModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Form -->
    <form id="editForm" class="p-6 space-y-4" enctype="multipart/form-data">
        <input type="hidden" name="id" id="editBuktiId">

        <!-- Info Berkas Saat Ini -->
        <div class="p-3 bg-amber-50/70 border border-amber-200/60 rounded-xl flex items-center space-x-3 text-xs">
            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 font-bold text-xs flex items-center justify-center uppercase shrink-0" id="editCurExt">
                PDF
            </div>
            <div class="min-w-0 flex-1">
                <p class="font-semibold text-slate-800 truncate" id="editCurFileName">-</p>
                <p class="text-[11px] text-slate-500" id="editCurFileSize">-</p>
            </div>
        </div>

        <!-- Nama Dokumen -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
                Nama Dokumen <span class="text-red-500">*</span>
            </label>
            <input type="text" name="nama_dokumen" id="editNamaDokumen" required
                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
        </div>

        <!-- Kategori Dokumen -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Kategori Dokumen</label>
            <select name="kategori" id="editKategori" class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                <option value="SK / Kebijakan">SK / Kebijakan</option>
                <option value="Laporan Kegiatan">Laporan Kegiatan</option>
                <option value="Foto Dokumentasi">Foto Dokumentasi</option>
                <option value="Kurikulum & Pembelajaran">Kurikulum & Pembelajaran</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>

        <!-- Catatan / Deskripsi -->
        <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Catatan Tambahan (Opsional)</label>
            <textarea name="deskripsi" id="editDeskripsi" rows="2" placeholder="Keterangan singkat berkas..."
                class="w-full px-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none resize-none"></textarea>
        </div>

        <!-- Ganti File Fisik (Opsional) -->
        <div class="p-4 bg-slate-50 rounded-xl border border-slate-200/80 space-y-1.5">
            <label class="block text-xs font-bold text-slate-700">
                Ganti Berkas / File Baru <span class="text-slate-400 font-normal">(opsional)</span>
            </label>
            <p class="text-[11px] text-slate-500 mb-2">Pilih file baru jika salah mengunggah berkas sebelumnya (PDF, DOC, DOCX, XLS, XLSX, JPG, PNG maks 10MB). Biarkan kosong jika file sudah benar.</p>
            <input type="file" name="file" id="editFileInput" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-800 hover:file:bg-amber-100 cursor-pointer">
        </div>

        <!-- Footer -->
        <div class="flex justify-end items-center space-x-2 pt-3 border-t border-slate-100">
            <button type="button" onclick="closeEditModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
            <button type="submit" id="btnEditSubmit" class="px-5 py-2 rounded-xl text-xs font-bold text-white transition flex items-center space-x-1.5 bg-amber-600 hover:bg-amber-700 shadow">
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>
</div>

<script>
let rowSequence = 0;

function createUploadRowHtml(num) {
    return `
    <div class="upload-row p-4 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-white hover:border-brand-300 transition space-y-3" data-row-id="${num}">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
                <span class="row-badge w-6 h-6 rounded-lg bg-brand-600 text-white font-black text-xs flex items-center justify-center shrink-0">
                    ${num}
                </span>
                <span class="text-xs font-bold text-slate-700 row-title">Berkas #${num}</span>
            </div>
            <button type="button" onclick="removeUploadRow(this)"
                class="btn-remove-row text-xs font-semibold text-red-500 hover:text-red-700 flex items-center space-x-1 px-2 py-1 rounded-lg hover:bg-red-50 transition"
                title="Hapus baris ini">
                <i class="fa-solid fa-trash-can text-[11px]"></i>
                <span class="text-[11px]">Hapus</span>
            </button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <!-- Nama Dokumen -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">
                    Nama Dokumen <span class="text-slate-400 font-normal">(opsional, default: nama file)</span>
                </label>
                <input type="text" name="nama_dokumen[]" placeholder="Contoh: SK Tim Pengembang #${num}"
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
            </div>

            <!-- Kategori -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Kategori Dokumen</label>
                <select name="kategori[]" class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                    <option value="SK / Kebijakan">SK / Kebijakan</option>
                    <option value="Laporan Kegiatan">Laporan Kegiatan</option>
                    <option value="Foto Dokumentasi">Foto Dokumentasi</option>
                    <option value="Kurikulum & Pembelajaran">Kurikulum & Pembelajaran</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-start">
            <!-- File Input -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-700 mb-1">
                    Pilih File <span class="text-red-500">*</span>
                </label>
                <input type="file" name="files[]" required
                    accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png"
                    onchange="handleFileInputChange(this)"
                    class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 cursor-pointer">
            </div>

            <!-- Catatan / Deskripsi -->
            <div>
                <label class="block text-[11px] font-semibold text-slate-600 mb-1">Catatan Tambahan (Opsional)</label>
                <input type="text" name="deskripsi[]" placeholder="Keterangan singkat..."
                    class="w-full px-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
            </div>
        </div>
    </div>
    `;
}

function reindexUploadRows() {
    const rows = document.querySelectorAll('#uploadRowsContainer .upload-row');
    rows.forEach((row, idx) => {
        const num = idx + 1;
        const badge = row.querySelector('.row-badge');
        const title = row.querySelector('.row-title');
        const removeBtn = row.querySelector('.btn-remove-row');

        if (badge) badge.innerText = num;
        if (title) title.innerText = 'Berkas #' + num;

        // Jangan izinkan hapus jika hanya tersisa 1 baris (default baris 1)
        if (removeBtn) {
            if (rows.length === 1) {
                removeBtn.classList.add('hidden');
            } else {
                removeBtn.classList.remove('hidden');
            }
        }
    });

    const totalCounter = document.getElementById('totalRowsCount');
    if (totalCounter) {
        totalCounter.innerText = `Total: ${rows.length} berkas`;
    }
}

function addUploadRow() {
    const container = document.getElementById('uploadRowsContainer');
    const currentRows = container.querySelectorAll('.upload-row').length;
    const nextNum = currentRows + 1;

    const div = document.createElement('div');
    div.innerHTML = createUploadRowHtml(nextNum).trim();
    container.appendChild(div.firstChild);
    reindexUploadRows();
}

function removeUploadRow(btn) {
    const row = btn.closest('.upload-row');
    if (row) {
        row.remove();
        reindexUploadRows();
    }
}

function handleFileInputChange(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const row = input.closest('.upload-row');
        if (row) {
            const namaInput = row.querySelector('input[name="nama_dokumen[]"]');
            if (namaInput && !namaInput.value.trim()) {
                const baseName = file.name.substring(0, file.name.lastIndexOf('.')) || file.name;
                namaInput.value = baseName;
            }
        }
    }
}

function resetUploadModalToDefault() {
    const container = document.getElementById('uploadRowsContainer');
    container.innerHTML = createUploadRowHtml(1);
    reindexUploadRows();
}

function openUploadModal(indId, kode, uraian) {
    document.getElementById('uploadModal').classList.remove('hidden');
    document.getElementById('uploadModal').classList.add('flex');
    document.getElementById('uploadIndikatorId').value = indId;
    document.getElementById('modalIndikatorBadge').innerText = 'Butir Indikator: ' + kode;
    document.getElementById('modalIndikatorText').innerText = uraian;
    resetUploadModalToDefault();
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadModal').classList.remove('flex');
    document.getElementById('uploadForm').reset();
    resetUploadModalToDefault();
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

    const fileInputs = this.querySelectorAll('input[type="file"][name="files[]"]');
    let hasAnyFile = false;
    for (let fi of fileInputs) {
        if (fi.files && fi.files.length > 0) {
            hasAnyFile = true;
            break;
        }
    }

    if (!hasAnyFile) {
        Swal.fire('Peringatan', 'Pilih minimal satu file bukti dukung!', 'warning');
        return;
    }

    const btn = document.getElementById('btnUploadSubmit');
    const oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Mengunggah...';

    const formData = new FormData(this);
    try {
        const response = await fetch('<?= base_url('bukti/upload') ?>', { method: 'POST', body: formData });
        const res = await response.json();

        if (res.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1600, showConfirmButton: false })
                .then(() => { closeUploadModal(); location.reload(); });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Kesalahan Sistem', text: 'Gagal mengunggah berkas bukti.' });
        btn.disabled = false;
        btn.innerHTML = oldHtml;
    }
});

// Edit Modal Handlers
function openEditModal(data) {
    document.getElementById('editBuktiId').value = data.id;
    document.getElementById('editNamaDokumen').value = data.nama_dokumen || '';
    document.getElementById('editKategori').value = data.kategori || 'Umum';
    document.getElementById('editDeskripsi').value = data.deskripsi || '';
    document.getElementById('editCurExt').innerText = (data.file_ext || 'FILE').toUpperCase();
    document.getElementById('editCurFileName').innerText = data.file_name || data.nama_dokumen;
    document.getElementById('editCurFileSize').innerText = 'Ukuran saat ini: ' + (data.file_size || '-');
    document.getElementById('editFileInput').value = '';

    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').classList.add('flex');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').classList.remove('flex');
    document.getElementById('editForm').reset();
}

document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const id = document.getElementById('editBuktiId').value;
    const btn = document.getElementById('btnEditSubmit');
    const oldHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Menyimpan...';

    const formData = new FormData(this);
    try {
        const response = await fetch('<?= base_url('bukti/update/') ?>' + id, {
            method: 'POST',
            body: formData
        });
        const res = await response.json();

        if (res.success) {
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false })
                .then(() => { closeEditModal(); location.reload(); });
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Kesalahan Sistem', text: 'Gagal memperbarui bukti dukung.' });
        btn.disabled = false;
        btn.innerHTML = oldHtml;
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

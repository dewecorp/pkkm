<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">

<!-- Page Header -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Data Kepala Madrasah</h1>
        <p class="text-sm text-slate-500 mt-0.5">Kelola profil kepala madrasah beserta akun login mereka</p>
    </div>
    <button onclick="openAdd()"
        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white shadow"
        style="background:linear-gradient(135deg,#6366f1,#4338ca);">
        <i class="fa-solid fa-plus"></i><span>Tambah Kepala Madrasah</span>
    </button>
</div>

<!-- Table -->
<div class="card overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-left text-xs">
    <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase text-[10px] font-semibold tracking-wider">
        <tr>
            <th class="px-5 py-3.5">Nama Kepala Madrasah</th>
            <th class="px-5 py-3.5">Madrasah</th>
            <th class="px-5 py-3.5">NIP / NPK</th>
            <th class="px-5 py-3.5">Pangkat / Golongan</th>
            <th class="px-5 py-3.5">TMT Kamad</th>
            <th class="px-5 py-3.5">Status</th>
            <th class="px-5 py-3.5 text-right">Aksi</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-50">
    <?php if (!empty($rows)): ?>
    <?php foreach ($rows as $r): ?>
    <tr class="hover:bg-slate-50 transition group">
        <td class="px-5 py-4">
            <div class="flex items-center space-x-3">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm text-white shrink-0"
                     style="background:linear-gradient(135deg,#6366f1,#4338ca);">
                    <?= strtoupper(substr($r['nama_lengkap'], 0, 1)) ?>
                </div>
                <div>
                    <div class="font-semibold text-slate-800"><?= e($r['nama_lengkap']) ?></div>
                    <div class="text-[11px] text-slate-400"><?= e($r['pendidikan_terakhir'] ?: '-') ?></div>
                </div>
            </div>
        </td>
        <td class="px-5 py-4 text-slate-600 max-w-[160px]">
            <div class="truncate"><?= e($r['nama_madrasah']) ?></div>
        </td>
        <td class="px-5 py-4">
            <div class="text-slate-700"><?= e($r['nip'] ?: '-') ?></div>
            <div class="text-[11px] text-slate-400">NPK: <?= e($r['npk'] ?: '-') ?></div>
        </td>
        <td class="px-5 py-4 text-slate-600"><?= e($r['pangkat_golongan'] ?: '-') ?></td>
        <td class="px-5 py-4 text-slate-600"><?= e($r['tmt_kamad'] ?: '-') ?></td>
        <td class="px-5 py-4">
            <?php if ($r['status'] === 'aktif'): ?>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
            <?php else: ?>
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-500">Nonaktif</span>
            <?php endif; ?>
        </td>
        <td class="px-5 py-4 text-right">
            <div class="flex items-center justify-end space-x-1.5">
                <button onclick='editK(<?= htmlspecialchars(json_encode($r)) ?>)'
                    class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 border border-slate-200 text-slate-600 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 transition"
                    title="Edit Kepala Madrasah">
                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                </button>
                <button onclick="delK(<?= $r['id'] ?>, '<?= e($r['nama_lengkap']) ?>')"
                    class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 border border-slate-200 text-slate-600 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition"
                    title="Hapus Kepala Madrasah">
                    <i class="fa-solid fa-trash text-xs"></i>
                </button>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
    <?php else: ?>
    <tr>
        <td colspan="7" class="px-5 py-16 text-center">
            <div class="flex flex-col items-center space-y-2 text-slate-400">
                <i class="fa-solid fa-user-slash text-3xl"></i>
                <div class="text-sm font-medium">Belum ada data kepala madrasah</div>
                <div class="text-xs">Klik tombol "Tambah Kepala Madrasah" untuk menambahkan data pertama</div>
            </div>
        </td>
    </tr>
    <?php endif; ?>
    </tbody>
</table>
</div>
</div>
</div>

<!-- ===================== MODAL FORM ===================== -->
<div id="kModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 p-4 overflow-y-auto">
<div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl my-8 overflow-hidden">

    <!-- Modal Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
        <h3 id="kTitle" class="font-bold text-slate-800 text-base"></h3>
        <button onclick="closeK()"
            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>

    <!-- Modal Body -->
    <form id="kForm" class="p-6 space-y-5 overflow-y-auto" style="max-height:75vh;">
        <input type="hidden" name="id"   id="kId">
        <input type="hidden" name="mode" id="kMode">

        <!-- Profil Kepala -->
        <div>
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3 flex items-center space-x-2">
                <i class="fa-solid fa-user text-brand-500"></i><span>Profil Kepala Madrasah</span>
            </h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_lengkap" id="kNama" required
                        placeholder="Nama lengkap kepala madrasah"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Madrasah <span class="text-red-500">*</span></label>
                    <select name="madrasah_id" id="kMadrasah" required
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">-- Pilih Madrasah --</option>
                        <?php foreach ($madrasahList as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= e($m['nama_madrasah']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Kelamin</label>
                    <select name="jenis_kelamin" id="kJK"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">NIP</label>
                    <input type="text" name="nip" id="kNip" placeholder="Nomor Induk Pegawai"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">NPK</label>
                    <input type="text" name="npk" id="kNpk" placeholder="Nomor Pokok Karyawan"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">NUPTK</label>
                    <input type="text" name="nuptk" id="kNuptk" placeholder="NUPTK (16 digit)"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Pangkat / Golongan</label>
                    <input type="text" name="pangkat_golongan" id="kGol" placeholder="cth. Pembina, IV/a"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Pendidikan Terakhir</label>
                    <input type="text" name="pendidikan_terakhir" id="kPend" placeholder="cth. S2 Manajemen Pendidikan"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" id="kTempat" placeholder="Kota kelahiran"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="kTgl"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">TMT Kepala Madrasah</label>
                    <input type="date" name="tmt_kamad" id="kTmt"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Masa Kerja</label>
                    <input type="text" name="masa_kerja" id="kMasa" placeholder="cth. 4 Tahun 2 Bulan"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div id="kStatusField" class="hidden">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                    <select name="status" id="kStatus"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="aktif">Aktif</option>
                        <option value="nonaktif">Nonaktif</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Akun Login (hanya saat tambah baru) -->
        <div id="kAkunSection" class="border border-dashed border-slate-200 rounded-2xl p-4 space-y-4">
            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center space-x-2">
                <i class="fa-solid fa-key text-amber-500"></i>
                <span>Akun Login (opsional — untuk akses dashboard Kepala Madrasah)</span>
            </h4>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Username</label>
                    <input type="text" name="username" placeholder="username login" autocomplete="off"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Password</label>
                    <input type="password" name="password" placeholder="Min. 6 karakter" autocomplete="new-password"
                        class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
            </div>
            <p class="text-[11px] text-slate-400">
                <i class="fa-solid fa-circle-info mr-1"></i>
                Kosongkan jika tidak perlu akun login. Akun dapat dibuat atau direset dari menu Pengguna.
            </p>
        </div>

        <!-- Footer Buttons -->
        <div class="flex justify-end space-x-2 pt-2 border-t border-slate-100">
            <button type="button" onclick="closeK()"
                class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                Batal
            </button>
            <button type="submit" id="kSave"
                class="px-6 py-2.5 rounded-xl text-sm font-bold text-white transition"
                style="background:linear-gradient(135deg,#6366f1,#4338ca);">
                Simpan
            </button>
        </div>
    </form>
</div>
</div>

<script>
const kFields = {
    nama_lengkap:      'kNama',
    madrasah_id:       'kMadrasah',
    nip:               'kNip',
    npk:               'kNpk',
    nuptk:             'kNuptk',
    pangkat_golongan:  'kGol',
    pendidikan_terakhir:'kPend',
    tempat_lahir:      'kTempat',
    tanggal_lahir:     'kTgl',
    tmt_kamad:         'kTmt',
    masa_kerja:        'kMasa',
    jenis_kelamin:     'kJK',
    status:            'kStatus',
};

function openAdd() {
    document.getElementById('kModal').classList.replace('hidden','flex');
    document.getElementById('kTitle').innerText = 'Tambah Kepala Madrasah';
    document.getElementById('kMode').value = 'tambah';
    document.getElementById('kId').value = '';
    document.getElementById('kForm').reset();
    document.getElementById('kStatusField').classList.add('hidden');
    document.getElementById('kAkunSection').classList.remove('hidden');
}

function closeK() {
    document.getElementById('kModal').classList.replace('flex','hidden');
    document.getElementById('kForm').reset();
}

function editK(r) {
    document.getElementById('kModal').classList.replace('hidden','flex');
    document.getElementById('kTitle').innerText = 'Edit Kepala Madrasah';
    document.getElementById('kMode').value = 'edit';
    document.getElementById('kId').value = r.id;
    document.getElementById('kStatusField').classList.remove('hidden');
    document.getElementById('kAkunSection').classList.add('hidden');

    Object.entries(kFields).forEach(([key, elId]) => {
        const el = document.getElementById(elId);
        if (el) el.value = r[key] || '';
    });
}

async function delK(id, nama) {
    const c = await Swal.fire({
        title: 'Hapus Kepala Madrasah?',
        text: `Data "${nama}" akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    });
    if (!c.isConfirmed) return;

    const fd = new FormData();
    fd.append('id', id);
    const res = await fetch('<?= base_url('admin/kepala-madrasah/delete') ?>', {
        method: 'POST', body: fd
    }).then(r => r.json());

    Swal.fire(res.success ? 'Berhasil' : 'Gagal', res.message, res.success ? 'success' : 'error')
        .then(() => { if (res.success) location.reload(); });
}

document.getElementById('kForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const mode = document.getElementById('kMode').value;
    const url  = mode === 'tambah'
        ? '<?= base_url('admin/kepala-madrasah/store') ?>'
        : '<?= base_url('admin/kepala-madrasah/update') ?>';

    const btn = document.getElementById('kSave');
    btn.disabled = true;
    btn.innerText = 'Menyimpan...';

    const res = await fetch(url, {
        method: 'POST',
        body: new FormData(this)
    }).then(r => r.json());

    Swal.fire(res.success ? 'Berhasil' : 'Gagal', res.message, res.success ? 'success' : 'error')
        .then(() => { if (res.success) { closeK(); location.reload(); } });

    btn.disabled = false;
    btn.innerText = 'Simpan';
});
</script>

<?php view('layout.footer'); ?>

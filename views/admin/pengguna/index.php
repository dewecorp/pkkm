<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Pengguna & Penilai</h1>
        <p class="text-sm text-slate-500 mt-0.5">Kelola akun pengguna sistem dan penugasan kelompok penilai</p>
    </div>
    <button onclick="openAdd()" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white shadow" style="background:linear-gradient(135deg,#1e6347,#17503a);">
        <i class="fa-solid fa-user-plus"></i><span>Tambah Pengguna</span>
    </button>
</div>

<div class="card overflow-hidden">
<div class="overflow-x-auto">
<table class="w-full text-left text-xs">
    <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase text-[10px] font-semibold tracking-wider">
        <tr>
            <th class="px-5 py-3.5">Pengguna</th>
            <th class="px-5 py-3.5">NIP / Jabatan</th>
            <th class="px-5 py-3.5">Role</th>
            <th class="px-5 py-3.5">Kelompok</th>
            <th class="px-5 py-3.5">Status</th>
            <th class="px-5 py-3.5 text-right">Aksi</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-50">
    <?php foreach ($rows as $r): ?>
    <tr class="hover:bg-slate-50 transition group">
        <td class="px-5 py-4">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-xs text-white shrink-0" style="background:linear-gradient(135deg,#1e6347,#17503a);">
                    <?= strtoupper(substr($r['nama_lengkap'],0,1)) ?>
                </div>
                <div>
                    <div class="font-semibold text-slate-800"><?= e($r['nama_lengkap']) ?></div>
                    <div class="text-slate-400 text-[11px]">@<?= e($r['username']) ?></div>
                </div>
            </div>
        </td>
        <td class="px-5 py-4">
            <div class="text-slate-700"><?= e($r['nip'] ?: '-') ?></div>
            <div class="text-slate-400 text-[11px]"><?= e($r['jabatan'] ?: '-') ?></div>
        </td>
        <td class="px-5 py-4">
            <?php foreach (explode(', ', $r['roles_text']??'') as $rl): if(!$rl) continue; ?>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-brand-50 text-brand-700 border border-brand-200 mr-1"><?= e($rl) ?></span>
            <?php endforeach; ?>
        </td>
        <td class="px-5 py-4 text-slate-500 text-[11px]"><?= e($r['kelompok_text'] ?: '—') ?></td>
        <td class="px-5 py-4">
            <button onclick="toggleStatus(<?= $r['id'] ?>, this)"
                class="px-2.5 py-1 rounded-full text-[10px] font-bold transition <?= $r['status']==='aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500' ?>">
                <?= $r['status']==='aktif' ? 'Aktif' : 'Nonaktif' ?>
            </button>
        </td>
        <td class="px-5 py-4 text-right">
            <div class="flex items-center justify-end space-x-1.5">
                <button onclick='editUser(<?= htmlspecialchars(json_encode($r)) ?>)' title="Edit Pengguna"
                    class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 border border-slate-200 text-slate-600 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 transition">
                    <i class="fa-solid fa-pen-to-square text-xs"></i>
                </button>
                <button onclick="resetPwd(<?= $r['id'] ?>, '<?= e($r['nama_lengkap']) ?>')" title="Reset Password"
                    class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 border border-slate-200 text-slate-600 hover:bg-amber-50 hover:text-amber-600 hover:border-amber-200 transition">
                    <i class="fa-solid fa-key text-xs"></i>
                </button>
                <?php if ($r['id'] != 1): ?>
                <button onclick="delUser(<?= $r['id'] ?>, '<?= e($r['nama_lengkap']) ?>')" title="Hapus Pengguna"
                    class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 border border-slate-200 text-slate-600 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition">
                    <i class="fa-solid fa-trash text-xs"></i>
                </button>
                <?php endif; ?>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
</div>
</div>

<!-- Modal Tambah & Edit Pengguna -->
<div id="pgModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 sm:p-6 overflow-y-auto">
<div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden flex flex-col max-h-[90vh] my-auto">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
        <h3 id="pgTitle" class="font-bold text-slate-800 text-base">Tambah Pengguna Baru</h3>
        <button onclick="closePg()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="pgForm" class="flex flex-col flex-1 min-h-0">
        <input type="hidden" name="id" id="pgId">
        <input type="hidden" name="mode" id="pgMode">

        <div class="p-6 space-y-4 overflow-y-auto flex-1">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_lengkap" id="pgNama" required placeholder="Nama lengkap" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" id="pgUsername" required placeholder="username" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">
                        Password <span id="pgPwdReq" class="text-red-500">*</span>
                        <span id="pgPwdNote" class="text-[10px] text-slate-400 font-normal hidden">(opsional)</span>
                    </label>
                    <input type="password" name="password" id="pgPassword" placeholder="Min. 6 karakter" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">NIP</label>
                    <input type="text" name="nip" id="pgNip" placeholder="NIP" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">NPK</label>
                    <input type="text" name="npk" id="pgNpk" placeholder="NPK" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Jabatan</label>
                    <input type="text" name="jabatan" id="pgJabatan" placeholder="Jabatan" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Pangkat/Golongan</label>
                    <input type="text" name="pangkat_golongan" id="pgGol" placeholder="Pangkat/Golongan" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Unit Kerja</label>
                    <input type="text" name="unit_kerja" id="pgUnit" placeholder="Unit/Instansi" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Role <span class="text-red-500">*</span></label>
                    <select name="role_id[]" id="pgRole" multiple class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none h-24">
                        <?php foreach ($roles as $rl): ?>
                        <option value="<?= $rl['id'] ?>"><?= e($rl['nama_role']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="text-[10px] text-slate-400 mt-1">Gunakan Ctrl + Klik untuk memilih lebih dari 1 role</p>
                </div>
            </div>

            <!-- Penugasan Penilai (opsional) -->
            <div class="border border-slate-200 rounded-xl p-4 space-y-3">
                <h4 class="text-xs font-bold text-slate-600 flex items-center space-x-1.5">
                    <i class="fa-solid fa-clipboard-user text-brand-500"></i>
                    <span>Penugasan Penilai (Opsional)</span>
                </h4>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Periode</label>
                        <select name="periode_id" id="pgPeriode" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                            <option value="">-- Tidak Ditugaskan --</option>
                            <?php foreach ($periode as $pd): ?>
                            <option value="<?= $pd['id'] ?>"><?= e($pd['nama_periode']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Kelompok</label>
                        <select name="kelompok_id" id="pgKelompok" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                            <option value="">-- Pilih Kelompok --</option>
                            <?php foreach ($kelompok as $kg): ?>
                            <option value="<?= $kg['id'] ?>"><?= e($kg['kode_kelompok']) ?> — <?= e($kg['nama_kelompok']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">Urutan Penilai</label>
                        <input type="number" name="urutan_penilai" id="pgUrutan" min="1" max="10" value="1" class="w-full px-3 py-2 border border-slate-200 rounded-lg text-xs focus:ring-2 focus:ring-brand-500 outline-none">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-2 px-6 py-4 border-t border-slate-100 bg-slate-50/50 shrink-0">
            <button type="button" onclick="closePg()" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
            <button type="submit" id="btnPgSave" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white transition" style="background:linear-gradient(135deg,#1e6347,#17503a);">Simpan Pengguna</button>
        </div>
    </form>
</div>
</div>

<script>
function openAdd() {
    document.getElementById('pgModal').classList.remove('hidden');
    document.getElementById('pgModal').classList.add('flex');
    document.getElementById('pgTitle').innerText = 'Tambah Pengguna Baru';
    document.getElementById('pgMode').value = 'tambah';
    document.getElementById('pgId').value = '';
    document.getElementById('pgForm').reset();
    document.getElementById('pgPassword').required = true;
    document.getElementById('pgPwdReq').classList.remove('hidden');
    document.getElementById('pgPwdNote').classList.add('hidden');
}

function closePg() {
    document.getElementById('pgModal').classList.add('hidden');
    document.getElementById('pgModal').classList.remove('flex');
    document.getElementById('pgForm').reset();
}

function editUser(r) {
    document.getElementById('pgModal').classList.remove('hidden');
    document.getElementById('pgModal').classList.add('flex');
    document.getElementById('pgTitle').innerText = 'Edit Pengguna';
    document.getElementById('pgMode').value = 'edit';
    document.getElementById('pgId').value = r.id;

    document.getElementById('pgNama').value = r.nama_lengkap || '';
    document.getElementById('pgUsername').value = r.username || '';
    document.getElementById('pgPassword').value = '';
    document.getElementById('pgPassword').required = false;
    document.getElementById('pgPwdReq').classList.add('hidden');
    document.getElementById('pgPwdNote').classList.remove('hidden');

    document.getElementById('pgNip').value = r.nip || '';
    document.getElementById('pgNpk').value = r.npk || '';
    document.getElementById('pgJabatan').value = r.jabatan || '';
    document.getElementById('pgGol').value = r.pangkat_golongan || '';
    document.getElementById('pgUnit').value = r.unit_kerja || '';

    // Set roles select
    const roleSelect = document.getElementById('pgRole');
    const roleIds = (r.role_ids || '').split(',');
    Array.from(roleSelect.options).forEach(opt => {
        opt.selected = roleIds.includes(opt.value);
    });

    // Set penugasan
    document.getElementById('pgPeriode').value = r.periode_id || '';
    document.getElementById('pgKelompok').value = r.kelompok_id || '';
    document.getElementById('pgUrutan').value = r.urutan_penilai || 1;
}

document.getElementById('pgForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const mode = document.getElementById('pgMode').value;
    const url = mode === 'tambah'
        ? '<?= base_url('admin/pengguna/store') ?>'
        : '<?= base_url('admin/pengguna/update') ?>';

    const btn = document.getElementById('btnPgSave');
    btn.disabled = true;
    btn.innerText = 'Menyimpan...';

    const res = await fetch(url, { method: 'POST', body: new FormData(this) }).then(r => r.json());
    Swal.fire(res.success ? 'Berhasil' : 'Gagal', res.message, res.success ? 'success' : 'error')
        .then(() => { if (res.success) { closePg(); location.reload(); } });
    btn.disabled = false;
    btn.innerText = 'Simpan Pengguna';
});

async function resetPwd(id, nama) {
    const { value: pwd } = await Swal.fire({
        title: 'Reset Password',
        text: `Pengguna: ${nama}`,
        input: 'password',
        inputPlaceholder: 'Password baru (min. 6 karakter)',
        showCancelButton: true,
        confirmButtonColor: '#1e6347',
        cancelButtonText: 'Batal',
        confirmButtonText: 'Simpan',
        inputValidator: v => !v ? 'Wajib diisi!' : (v.length < 6 ? 'Minimal 6 karakter!' : null)
    });
    if (!pwd) return;
    const fd = new FormData(); fd.append('id', id); fd.append('password', pwd);
    const res = await fetch('<?= base_url('admin/pengguna/reset-password') ?>', { method: 'POST', body: fd }).then(r => r.json());
    Swal.fire(res.success ? 'Berhasil' : 'Gagal', res.message, res.success ? 'success' : 'error');
}

async function toggleStatus(id, btn) {
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('<?= base_url('admin/pengguna/toggle-status') ?>', { method: 'POST', body: fd }).then(r => r.json());
    if (res.success) {
        btn.innerText = res.new_status === 'aktif' ? 'Aktif' : 'Nonaktif';
        btn.className = `px-2.5 py-1 rounded-full text-[10px] font-bold transition ${res.new_status === 'aktif' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500'}`;
        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: res.message, timer: 2000, showConfirmButton: false });
    }
}

async function delUser(id, nama) {
    const c = await Swal.fire({
        title: 'Hapus Pengguna?',
        text: `Pengguna "${nama}" akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal'
    });
    if (!c.isConfirmed) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('<?= base_url('admin/pengguna/delete') ?>', { method: 'POST', body: fd }).then(r => r.json());
    Swal.fire(res.success ? 'Berhasil' : 'Gagal', res.message, res.success ? 'success' : 'error')
        .then(() => { if (res.success) location.reload(); });
}
</script>
<?php view('layout.footer'); ?>

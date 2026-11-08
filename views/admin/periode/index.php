<?php view('layout.header', ['title' => $title]); ?>
<?php
// Re-usable modal + table pattern
$jenisList = [['tahunan','Tahunan'],['empat_tahunan','4 Tahunan']];
$statusList = [['draft','Draft'],['aktif','Aktif'],['selesai','Selesai'],['arsip','Arsip']];
?>

<div class="space-y-6">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Periode Penilaian</h1>
        <p class="text-sm text-slate-500 mt-0.5">Kelola periode dan tahun penilaian kinerja kepala madrasah</p>
    </div>
    <button onclick="openModal('tambah')"
        class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white shadow"
        style="background:linear-gradient(135deg,#1e6347,#17503a);">
        <i class="fa-solid fa-plus"></i><span>Tambah Periode</span>
    </button>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-left text-xs">
        <thead class="text-slate-500 uppercase tracking-wider font-semibold text-[10px] border-b border-slate-100">
            <tr class="bg-slate-50">
                <th class="px-5 py-3.5">Nama Periode</th>
                <th class="px-5 py-3.5">Madrasah</th>
                <th class="px-5 py-3.5">Kepala Madrasah</th>
                <th class="px-5 py-3.5">Tahun</th>
                <th class="px-5 py-3.5">Jenis</th>
                <th class="px-5 py-3.5">Versi</th>
                <th class="px-5 py-3.5">Status</th>
                <th class="px-5 py-3.5 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
        <?php if (!empty($rows)): ?>
        <?php foreach ($rows as $r): ?>
        <?php
            $sc = ['draft'=>'bg-slate-100 text-slate-600','aktif'=>'bg-emerald-50 text-emerald-700 border border-emerald-200',
                   'selesai'=>'bg-blue-50 text-blue-700 border border-blue-200','arsip'=>'bg-amber-50 text-amber-700 border border-amber-200'];
            $cls = $sc[$r['status']] ?? 'bg-slate-100 text-slate-600';
        ?>
        <tr class="hover:bg-slate-50/70 transition group">
            <td class="px-5 py-4 font-semibold text-slate-800"><?= e($r['nama_periode']) ?></td>
            <td class="px-5 py-4 text-slate-600 max-w-[150px] truncate"><?= e($r['nama_madrasah']) ?></td>
            <td class="px-5 py-4 text-slate-600 max-w-[150px] truncate"><?= e($r['nama_kamad']) ?></td>
            <td class="px-5 py-4 font-semibold text-slate-700"><?= e($r['tahun_penilaian']) ?></td>
            <td class="px-5 py-4"><?= $r['jenis_penilaian']==='tahunan' ? 'Tahunan' : '4 Tahunan' ?></td>
            <td class="px-5 py-4 text-slate-500">v<?= e($r['nomor_versi']) ?></td>
            <td class="px-5 py-4">
                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $cls ?>"><?= ucfirst($r['status']) ?></span>
            </td>
            <td class="px-5 py-4 text-right">
                <div class="flex items-center justify-end space-x-1.5">
                    <button onclick="editPeriode(<?= htmlspecialchars(json_encode($r)) ?>)"
                        class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 border border-slate-200 text-slate-600 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 transition"
                        title="Edit Periode">
                        <i class="fa-solid fa-pen-to-square text-xs"></i>
                    </button>
                    <button onclick="deletePeriode(<?= $r['id'] ?>, '<?= e($r['nama_periode']) ?>')"
                        class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 border border-slate-200 text-slate-600 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition"
                        title="Hapus Periode">
                        <i class="fa-solid fa-trash text-xs"></i>
                    </button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="8" class="px-5 py-12 text-center text-slate-400">
            <i class="fa-solid fa-calendar-xmark text-3xl mb-2 block"></i>Belum ada periode penilaian
        </td></tr>
        <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
</div>

<!-- MODAL FORM (Tambah & Edit) -->
<div id="periodeModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 sm:p-6 overflow-y-auto">
<div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] my-auto">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
        <h3 id="modalTitle" class="font-bold text-slate-800 text-base"></h3>
        <button onclick="closeModal()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="periodeForm" class="flex flex-col flex-1 min-h-0">
        <input type="hidden" name="id" id="fId">
        <input type="hidden" name="mode" id="fMode">

        <div class="p-6 overflow-y-auto flex-1">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Periode <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_periode" id="fNama" required placeholder="cth. PKKM 4 Tahunan 2024" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Madrasah <span class="text-red-500">*</span></label>
                    <select name="madrasah_id" id="fMadrasah" required class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">-- Pilih Madrasah --</option>
                        <?php foreach ($madrasahList as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= e($m['nama_madrasah']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Kepala Madrasah <span class="text-red-500">*</span></label>
                    <select name="kamad_id" id="fKamad" required class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">-- Pilih Kepala Madrasah --</option>
                        <?php foreach ($kamadList as $k): ?>
                        <option value="<?= $k['id'] ?>"><?= e($k['nama_lengkap']) ?> (<?= e($k['nama_madrasah']) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tahun Penilaian <span class="text-red-500">*</span></label>
                    <input type="number" name="tahun_penilaian" id="fTahun" min="2020" max="2040" value="<?= date('Y') ?>" required class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Jenis Penilaian</label>
                    <select name="jenis_penilaian" id="fJenis" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                        <?php foreach ($jenisList as [$v,$l]): ?>
                        <option value="<?= $v ?>"><?= $l ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tahun Ke-</label>
                    <select name="tahun_ke" id="fTahunKe" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                        <?php for ($i=1;$i<=4;$i++): ?><option value="<?= $i ?>" <?= $i==4?'selected':'' ?>><?= $i ?></option><?php endfor; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Versi Instrumen <span class="text-red-500">*</span></label>
                    <select name="instrumen_versi_id" id="fVersi" required class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                        <option value="">-- Pilih Versi --</option>
                        <?php foreach ($versiList as $v): ?>
                        <option value="<?= $v['id'] ?>">v<?= e($v['nomor_versi']) ?> — <?= e($v['nama_versi']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" id="fMulai" required class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" id="fSelesai" required class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tempat Penilaian</label>
                    <input type="text" name="tempat_penilaian" id="fTempat" placeholder="Jepara" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>

                <div id="statusField" class="hidden">
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status</label>
                    <select name="status" id="fStatus" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                        <?php foreach ($statusList as [$v,$l]): ?><option value="<?= $v ?>"><?= $l ?></option><?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-2 px-6 py-4 border-t border-slate-100 bg-slate-50/50 shrink-0">
            <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
            <button type="submit" id="btnSave" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white transition"
                style="background:linear-gradient(135deg,#1e6347,#17503a);">Simpan</button>
        </div>
    </form>
</div>
</div>

<script>
function openModal(mode) {
    document.getElementById('periodeModal').classList.remove('hidden');
    document.getElementById('periodeModal').classList.add('flex');
    document.getElementById('fMode').value = mode;
    document.getElementById('statusField').classList.toggle('hidden', mode==='tambah');
    document.getElementById('modalTitle').innerText = mode==='tambah' ? 'Tambah Periode Baru' : 'Edit Periode';
}
function closeModal() {
    document.getElementById('periodeModal').classList.add('hidden');
    document.getElementById('periodeModal').classList.remove('flex');
    document.getElementById('periodeForm').reset();
}
function editPeriode(r) {
    openModal('edit');
    document.getElementById('fId').value        = r.id;
    document.getElementById('fNama').value      = r.nama_periode;
    document.getElementById('fTahun').value     = r.tahun_penilaian;
    document.getElementById('fJenis').value     = r.jenis_penilaian;
    document.getElementById('fTahunKe').value   = r.tahun_ke;
    document.getElementById('fMulai').value     = r.tanggal_mulai;
    document.getElementById('fSelesai').value   = r.tanggal_selesai;
    document.getElementById('fTempat').value    = r.tempat_penilaian;
    document.getElementById('fStatus').value    = r.status;
    document.getElementById('fMadrasah').value  = r.madrasah_id;
    document.getElementById('fKamad').value     = r.kamad_id;
    document.getElementById('fVersi').value     = r.instrumen_versi_id;
}
async function deletePeriode(id, nama) {
    const c = await Swal.fire({ title:'Hapus Periode?', text:`Periode "${nama}" akan dihapus permanen.`, icon:'warning',
        showCancelButton:true, confirmButtonText:'Ya, Hapus', cancelButtonText:'Batal', confirmButtonColor:'#dc2626' });
    if (!c.isConfirmed) return;
    const fd = new FormData(); fd.append('id', id);
    const res = await fetch('<?= base_url('admin/periode/delete') ?>', { method:'POST', body:fd }).then(r=>r.json());
    Swal.fire(res.success?'Berhasil':'Gagal', res.message, res.success?'success':'error')
        .then(()=>{ if(res.success) location.reload(); });
}
document.getElementById('periodeForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const mode = document.getElementById('fMode').value;
    const url  = mode==='tambah' ? '<?= base_url('admin/periode/store') ?>' : '<?= base_url('admin/periode/update') ?>';
    const btn  = document.getElementById('btnSave');
    btn.disabled = true; btn.innerText = 'Menyimpan...';
    const res = await fetch(url, { method:'POST', body: new FormData(this) }).then(r=>r.json());
    Swal.fire(res.success?'Berhasil':'Gagal', res.message, res.success?'success':'error')
        .then(()=>{ if(res.success){ closeModal(); location.reload(); } });
    btn.disabled = false; btn.innerText = 'Simpan';
});
</script>

<?php view('layout.footer'); ?>

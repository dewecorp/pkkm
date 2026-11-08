<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Data Madrasah</h1>
        <p class="text-sm text-slate-500 mt-0.5">Kelola profil dan identitas madrasah</p>
    </div>
    <button onclick="openAdd()" class="inline-flex items-center space-x-2 px-4 py-2.5 rounded-xl text-sm font-bold text-white shadow" style="background:linear-gradient(135deg,#1e6347,#17503a);">
        <i class="fa-solid fa-plus"></i><span>Tambah Madrasah</span>
    </button>
</div>

<div class="card overflow-hidden">
<div class="overflow-x-auto">
<table class="table-stack w-full text-left text-xs">
    <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase text-[10px] font-semibold tracking-wider">
        <tr>
            <th class="px-5 py-3.5">Nama Madrasah</th>
            <th class="px-5 py-3.5">NPSN</th>
            <th class="px-5 py-3.5">Akreditasi</th>
            <th class="px-5 py-3.5">Kabupaten</th>
            <th class="px-5 py-3.5">Provinsi</th>
            <th class="px-5 py-3.5 text-right">Aksi</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-50">
    <?php foreach ($rows as $r): ?>
    <tr class="hover:bg-slate-50 transition group">
        <td class="px-5 py-4 font-semibold text-slate-800" data-label="Nama Madrasah"><?= e($r['nama_madrasah']) ?></td>
        <td class="px-5 py-4 text-slate-500" data-label="NPSN"><?= e($r['npsn']) ?></td>
        <td class="px-5 py-4 font-semibold text-slate-700" data-label="Akreditasi"><?= e($r['status_akreditasi']) ?> (<?= e($r['tahun_akreditasi']) ?>)</td>
        <td class="px-5 py-4 text-slate-600" data-label="Kabupaten"><?= e($r['kabupaten_kota']) ?></td>
        <td class="px-5 py-4 text-slate-600" data-label="Provinsi"><?= e($r['provinsi']) ?></td>
        <td class="px-5 py-4 text-right" data-label="Aksi">
            <div class="flex items-center justify-end space-x-1.5 table-stack-actions">
                <button onclick='editM(<?= htmlspecialchars(json_encode($r)) ?>)' class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 border border-slate-200 text-slate-600 hover:bg-brand-50 hover:text-brand-600 hover:border-brand-200 transition" title="Edit Madrasah"><i class="fa-solid fa-pen-to-square text-xs"></i></button>
                <button onclick="delM(<?= $r['id'] ?>, '<?= e($r['nama_madrasah']) ?>')" class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-50 border border-slate-200 text-slate-600 hover:bg-red-50 hover:text-red-600 hover:border-red-200 transition" title="Hapus Madrasah"><i class="fa-solid fa-trash text-xs"></i></button>
            </div>
        </td>
    </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
</div>
</div>

<!-- Modal -->
<div id="mModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 sm:p-6 overflow-y-auto">
<div class="bg-white rounded-2xl w-full max-w-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] my-auto">
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 shrink-0">
        <h3 id="mTitle" class="font-bold text-slate-800 text-base"></h3>
        <button onclick="closeM()" class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <form id="mForm" class="flex flex-col flex-1 min-h-0">
        <input type="hidden" name="id" id="mId">
        <input type="hidden" name="mode" id="mMode">
        <div class="p-6 overflow-y-auto flex-1">
            <div class="grid grid-cols-2 gap-4">
                <?php
                $fields = [
                    ['nama_madrasah','Nama Madrasah','text',true,'col-span-2'],
                    ['nsm','NSM','text',false,''],
                    ['npsn','NPSN','text',false,''],
                    ['status_akreditasi','Status Akreditasi','text',false,''],
                    ['tahun_akreditasi','Tahun Akreditasi','number',false,''],
                    ['alamat','Alamat','text',false,'col-span-2'],
                    ['desa_kelurahan','Desa/Kelurahan','text',false,''],
                    ['kecamatan','Kecamatan','text',false,''],
                    ['kabupaten_kota','Kabupaten/Kota','text',false,''],
                    ['provinsi','Provinsi','text',false,''],
                    ['telp_fax','Telp/Fax','text',false,''],
                    ['email','Email','email',false,''],
                    ['website','Website','text',false,'col-span-2'],
                ];
                foreach ($fields as [$n,$l,$t,$req,$cs]):
                ?>
                <div class="<?= $cs ?>">
                    <label class="block text-xs font-semibold text-slate-600 mb-1"><?= $l ?><?= $req?' <span class="text-red-500">*</span>':'' ?></label>
                    <input type="<?= $t ?>" name="<?= $n ?>" id="m_<?= $n ?>" <?= $req?'required':'' ?> placeholder="<?= $l ?>" class="w-full px-3 py-2.5 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="flex justify-end space-x-2 px-6 py-4 border-t border-slate-100 bg-slate-50/50 shrink-0">
            <button type="button" onclick="closeM()" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">Batal</button>
            <button type="submit" id="mSave" class="px-5 py-2.5 rounded-xl text-sm font-bold text-white transition" style="background:linear-gradient(135deg,#1e6347,#17503a);">Simpan</button>
        </div>
    </form>
</div>
</div>

<script>
const mFields = ['nama_madrasah','nsm','npsn','status_akreditasi','tahun_akreditasi','alamat','desa_kelurahan','kecamatan','kabupaten_kota','provinsi','telp_fax','email','website'];
function openAdd() {
    document.getElementById('mModal').classList.replace('hidden','flex');
    document.getElementById('mTitle').innerText = 'Tambah Madrasah';
    document.getElementById('mMode').value = 'tambah';
    document.getElementById('mForm').reset();
}
function closeM() { document.getElementById('mModal').classList.replace('flex','hidden'); }
function editM(r) {
    document.getElementById('mModal').classList.replace('hidden','flex');
    document.getElementById('mTitle').innerText = 'Edit Madrasah';
    document.getElementById('mMode').value = 'edit';
    document.getElementById('mId').value = r.id;
    mFields.forEach(f => { const el = document.getElementById('m_'+f); if(el) el.value = r[f]||''; });
}
async function delM(id, nama) {
    const c = await Swal.fire({ title:'Hapus?', text:nama, icon:'warning', showCancelButton:true, confirmButtonColor:'#dc2626', confirmButtonText:'Hapus' });
    if (!c.isConfirmed) return;
    const fd = new FormData(); fd.append('id',id);
    const res = await fetch('<?= base_url('admin/madrasah/delete') ?>',{method:'POST',body:fd}).then(r=>r.json());
    Swal.fire(res.success?'Berhasil':'Gagal',res.message,res.success?'success':'error').then(()=>{ if(res.success) location.reload(); });
}
document.getElementById('mForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const mode = document.getElementById('mMode').value;
    const url  = mode==='tambah' ? '<?= base_url('admin/madrasah/store') ?>' : '<?= base_url('admin/madrasah/update') ?>';
    const res = await fetch(url,{method:'POST',body:new FormData(this)}).then(r=>r.json());
    Swal.fire(res.success?'Berhasil':'Gagal',res.message,res.success?'success':'error').then(()=>{ if(res.success){closeM();location.reload();} });
});
</script>
<?php view('layout.footer'); ?>

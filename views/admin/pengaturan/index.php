<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6 max-w-4xl">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-slate-800 tracking-tight">Pengaturan Tampilan & Sistem</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola gambar background halaman login dan teks identitas instansi</p>
        </div>
    </div>

    <!-- Card Form Pengaturan -->
    <div class="card p-6 sm:p-8 space-y-6">
        <form id="settingsForm" enctype="multipart/form-data" class="space-y-6">
            <?= csrf_field() ?>

            <!-- Preview & Upload Logo Aplikasi -->
            <div class="space-y-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Logo Aplikasi / Instansi
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    <!-- Preview Logo -->
                    <div class="flex items-center gap-4 p-4 rounded-2xl border border-slate-200 bg-emerald-900/90 shadow-sm">
                        <?php 
                        $currentLogo = $settings['app_logo'] ?? 'img/logo-kemenag.svg';
                        ?>
                        <div class="w-16 h-16 rounded-xl bg-white/10 border border-white/20 p-2 flex items-center justify-center shrink-0">
                            <img id="logoPreview" 
                                 src="<?= base_url($currentLogo) ?>" 
                                 alt="Logo Aplikasi" 
                                 class="max-w-full max-h-full object-contain">
                        </div>
                        <div class="text-white min-w-0">
                            <div class="text-xs font-bold leading-tight">Preview di Navbar</div>
                            <p class="text-[11px] text-emerald-200/90 mt-0.5 leading-snug">
                                Ditampilkan di navbar atas, mobile app bar, dan form login
                            </p>
                        </div>
                    </div>

                    <!-- Upload Logo Controls -->
                    <div class="space-y-4">
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Unggah logo resmi Kemenag / Madrasah (format PNG transparan atau SVG direkomendasikan, JPG/WEBP diperbolehkan, maksimal 2MB).
                        </p>

                        <div>
                            <input type="file" 
                                   name="app_logo_file" 
                                   id="appLogoFile" 
                                   accept="image/png,image/svg+xml,image/jpeg,image/webp"
                                   onchange="previewLogo(event)" 
                                   class="block w-full text-xs text-slate-500
                                          file:mr-4 file:py-2.5 file:px-4
                                          file:rounded-xl file:border-0
                                          file:text-xs file:font-bold
                                          file:bg-brand-50 file:text-brand-700
                                          hover:file:bg-brand-100 transition cursor-pointer">
                        </div>

                        <div class="flex items-center space-x-2">
                            <button type="button" 
                                    onclick="resetLogoDefault()"
                                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                                <i class="fa-solid fa-rotate-left text-[11px]"></i>
                                <span>Kembalikan Logo Default</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview & Upload Background Login -->
            <div class="border-t border-slate-100 pt-5 space-y-3">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Background Halaman Login
                </label>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                    <!-- Preview Image -->
                    <div class="relative rounded-2xl overflow-hidden border border-slate-200 shadow-sm aspect-video bg-slate-900 group">
                        <?php 
                        $currentBg = $settings['login_bg_image'] ?? 'img/gedung-kemenag.jpg';
                        ?>
                        <img id="bgPreview" 
                             src="<?= base_url($currentBg) ?>" 
                             alt="Background Login" 
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center text-white text-xs font-semibold">
                            Tampilan Saat Ini
                        </div>
                    </div>

                    <!-- Upload Controls -->
                    <div class="space-y-4">
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Ganti foto latar belakang halaman login (disarankan foto resolusi tinggi berasio horizontal, misal foto gedung kantor, format JPG/PNG/WEBP, maksimal 5MB).
                        </p>

                        <div>
                            <input type="file" 
                                   name="login_bg_file" 
                                   id="loginBgFile" 
                                   accept="image/jpeg,image/png,image/webp"
                                   onchange="previewImage(event)" 
                                   class="block w-full text-xs text-slate-500
                                          file:mr-4 file:py-2.5 file:px-4
                                          file:rounded-xl file:border-0
                                          file:text-xs file:font-bold
                                          file:bg-brand-50 file:text-brand-700
                                          hover:file:bg-brand-100 transition cursor-pointer">
                        </div>

                        <div class="flex items-center space-x-2">
                            <button type="button" 
                                    onclick="resetBgDefault()"
                                    class="inline-flex items-center space-x-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition">
                                <i class="fa-solid fa-rotate-left text-[11px]"></i>
                                <span>Kembalikan ke Default</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-100 pt-5 space-y-4">
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Teks Identitas Halaman Login
                </label>

                <!-- Judul -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-600">Judul Aplikasi</label>
                    <input type="text" 
                           name="login_title" 
                           value="<?= e($settings['login_title'] ?? 'Aplikasi Penilaian Kinerja Kepala Madrasah') ?>" 
                           class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Subtitle / Instansi -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-600">Nama Instansi / Kantor</label>
                    <input type="text" 
                           name="login_subtitle" 
                           value="<?= e($settings['login_subtitle'] ?? 'Kementerian Agama Kabupaten Jepara') ?>" 
                           class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>

                <!-- Deskripsi -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold text-slate-600">Deskripsi Singkat (Banner Kiri)</label>
                    <textarea name="login_desc" 
                              rows="3" 
                              class="w-full px-4 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500"><?= e($settings['login_desc'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" 
                        id="btnSave" 
                        class="px-6 py-2.5 rounded-xl text-xs font-bold text-white shadow-sm transition flex items-center space-x-2"
                        style="background:linear-gradient(135deg,#1e6347,#17503a);">
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span>Simpan Pengaturan</span>
                </button>
            </div>
        </form>
    </div>

</div>

<script>
function previewLogo(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

async function resetLogoDefault() {
    const confirm = await Swal.fire({
        title: 'Reset Logo?',
        text: 'Logo aplikasi akan dikembalikan ke logo default Kementerian Agama.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal'
    });
    if (!confirm.isConfirmed) return;

    try {
        const res = await fetch('<?= base_url('admin/pengaturan/reset-logo') ?>', { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Gagal mereset logo.', 'error');
    }
}

function previewImage(event) {
    const input = event.target;
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('bgPreview').src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

async function resetBgDefault() {
    const confirm = await Swal.fire({
        title: 'Reset Background?',
        text: 'Background login akan dikembalikan ke gambar default gedung Kemenag Jepara.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal'
    });
    if (!confirm.isConfirmed) return;

    try {
        const res = await fetch('<?= base_url('admin/pengaturan/reset-bg') ?>', { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Berhasil', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Gagal', data.message, 'error');
        }
    } catch (e) {
        Swal.fire('Error', 'Gagal mereset background.', 'error');
    }
}

document.getElementById('settingsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSave');
    const oldContent = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Menyimpan...';

    const formData = new FormData(this);
    try {
        const res = await fetch('<?= base_url('admin/pengaturan/update') ?>', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            Swal.fire('Tersimpan', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Peringatan', data.message, 'warning');
            btn.disabled = false;
            btn.innerHTML = oldContent;
        }
    } catch(err) {
        Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
        btn.disabled = false;
        btn.innerHTML = oldContent;
    }
});
</script>

<?php view('layout.footer'); ?>

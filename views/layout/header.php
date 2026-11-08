<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'PKKM') ?> — Sistem Informasi PKKM</title>
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    </noscript>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <script>
        // Global handler: Semua SweetAlert sukses otomatis auto close dengan timer
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Swal !== 'undefined') {
                const _origSwalFire = Swal.fire.bind(Swal);
                Swal.fire = function(...args) {
                    if (args.length === 3 && (args[2] === 'success' || args[0] === 'Berhasil' || args[0] === 'Berhasil!' || args[0] === 'Tersimpan')) {
                        return _origSwalFire({
                            icon: 'success',
                            title: args[0],
                            text: args[1],
                            timer: 1500,
                            timerProgressBar: true,
                            showConfirmButton: false
                        });
                    } else if (typeof args[0] === 'object' && args[0] !== null) {
                        const opt = args[0];
                        const isSuccess = opt.icon === 'success' || 
                                          opt.title === 'Berhasil' || 
                                          opt.title === 'Berhasil!' || 
                                          opt.title === 'Tersimpan' || 
                                          opt.title === 'Terhapus!';
                        if (isSuccess && opt.timer === undefined) {
                            opt.timer = 1500;
                            opt.timerProgressBar = true;
                            opt.showConfirmButton = false;
                        }
                        return _origSwalFire(opt);
                    }
                    return _origSwalFire(...args);
                };
            }
        });
    </script>
    <style>
        :root {
            --kemenag: #1e6347;
            --glass-bg: rgba(255, 255, 255, 0.55);
            --glass-border: rgba(255, 255, 255, 0.7);
            --glass-shadow: 0 24px 60px -32px rgba(11, 42, 30, 0.35);
        }
        * { font-family: 'Inter', sans-serif; }
        html { height: 100%; scroll-behavior: smooth; }
        body {
            min-height: 100%;
            background:
                radial-gradient(1100px 520px at 8% -8%, rgba(30, 99, 71, 0.20), transparent 62%),
                radial-gradient(900px 480px at 98% 4%, rgba(76, 157, 117, 0.18), transparent 58%),
                radial-gradient(900px 600px at 50% 110%, rgba(13, 148, 136, 0.12), transparent 62%),
                linear-gradient(160deg, #f3f7f4 0%, #eef3f0 45%, #e8f0eb 100%);
        }
        .sidebar-item { transition: background-color .15s, color .15s; }
        .sidebar-item:hover { background: rgba(30, 99, 71, 0.08); }
        .sidebar-item.active {
            background: linear-gradient(135deg, rgba(30, 99, 71, 0.15) 0%, rgba(23, 80, 58, 0.1) 100%);
            border-right: 2px solid #1e6347;
            color: #1e6347;
        }
        .sidebar-item.active i { color: #1e6347; }

        /* ── Glass surfaces ───────────────────────────────── */
        .glass-topbar {
            background: rgba(255, 255, 255, 0.78) !important;
            backdrop-filter: blur(12px) saturate(140%);
            -webkit-backdrop-filter: blur(12px) saturate(140%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.65) !important;
            box-shadow: 0 1px 3px rgba(11, 42, 30, 0.06) !important;
        }
        .glass-sidebar {
            background: rgba(255, 255, 255, 0.72) !important;
            backdrop-filter: blur(12px) saturate(140%);
            -webkit-backdrop-filter: blur(12px) saturate(140%);
            border-right: 1px solid rgba(255, 255, 255, 0.6) !important;
            box-shadow: 1px 0 0 rgba(255, 255, 255, 0.35) !important;
        }
        .glass-page {
            position: relative;
            width: calc(100% - 1.5rem);
            margin: 1rem auto 1.5rem;
            background: rgba(255, 255, 255, 0.66);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 24px;
            box-shadow: var(--glass-shadow);
        }
        @media (min-width: 1024px) {
            .glass-page {
                width: calc(100% - 3rem);
                margin: 1.5rem auto 2rem;
            }
        }
        .card {
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 16px;
            box-shadow: 0 8px 30px -18px rgba(11, 42, 30, 0.35);
        }
        .card-hover { transition: transform .2s, box-shadow .2s; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(11, 42, 30, 0.12); }

        .badge-role {
            font-size: 10px; font-weight: 700; letter-spacing: .05em;
            padding: 3px 8px; border-radius: 999px;
        }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .gradient-text {
            background: linear-gradient(135deg, #1e6347, #1a573f);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        /* Sidebar transition on mobile */
        #sidebar { transition: transform .25s ease; }

        /* Panduan skor (details/summary) */
        details > summary { list-style: none; }
        details > summary::-webkit-details-marker { display: none; }
        .panduan-chevron { transition: transform .2s ease; }
        details[open] .panduan-chevron { transform: rotate(180deg); }
        details[open] .panduan-open-label { display: none; }
        details:not([open]) .panduan-close-label { display: none; }

        @supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
            .glass-topbar, .glass-sidebar { background: rgba(255, 255, 255, 0.96) !important; }
            .glass-page { background: rgba(255, 255, 255, 0.9); }
            .card { background: rgba(255, 255, 255, 0.96); }
        }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
        }
    </style>
</head>
<body style="font-family:'Inter',sans-serif;">

<!-- ===== TOPBAR ===== -->
<header class="glass-topbar fixed top-0 left-0 right-0 z-40 h-16 border-b flex items-center px-4 lg:px-6 gap-4">

    <!-- Hamburger (mobile) -->
    <button onclick="toggleSidebar()" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 transition">
        <i class="fa-solid fa-bars text-base"></i>
    </button>

    <!-- Logo -->
    <a href="<?= base_url('dashboard') ?>" class="flex items-center space-x-2.5 shrink-0">
        <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-white text-sm shadow"
             style="background:linear-gradient(135deg,#1e6347,#17503a);">P</div>
        <div class="hidden sm:block">
            <div class="font-bold text-slate-800 text-sm leading-tight tracking-tight">PKKM</div>
            <div class="text-[10px] text-slate-400 font-medium leading-tight">Kinerja Kepala Madrasah</div>
        </div>
    </a>

    <div class="flex-1"></div>

    <?php if (\App\Core\Auth::hasAnyRole(['admin', 'kamad'])): ?>
    <!-- Notifikasi: hanya admin & kepala madrasah -->
    <div class="relative" id="notifWrap">
        <button type="button" onclick="toggleNotif()" aria-label="Notifikasi"
                class="relative w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 transition">
            <i class="fa-regular fa-bell text-base"></i>
            <span id="notifBadge"
                  class="hidden absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center">0</span>
        </button>
        <div id="notifPanel"
             class="hidden absolute right-0 mt-2 w-80 max-w-[calc(100vw-2rem)] bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden z-50">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
                <span class="text-xs font-bold text-slate-700">Notifikasi</span>
                <button type="button" onclick="bacaSemuaNotif()"
                        class="text-[11px] font-semibold text-brand-600 hover:underline">Tandai dibaca</button>
            </div>
            <div id="notifList" class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                <div class="px-4 py-6 text-center text-xs text-slate-400">Memuat…</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- User Chip -->
    <div class="flex items-center space-x-2.5 pl-2 border-l border-slate-100">
        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm text-white shrink-0"
             style="background:linear-gradient(135deg,#1e6347,#17503a);">
            <?= strtoupper(substr(\App\Core\Auth::user()['nama_lengkap'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="hidden sm:block">
            <div class="text-xs font-semibold text-slate-700 leading-tight max-w-[140px] truncate"><?= e(\App\Core\Auth::user()['nama_lengkap'] ?? 'Pengguna') ?></div>
            <?php $roles = \App\Core\Auth::roles(); ?>
            <div class="text-[10px] text-brand-600 font-semibold capitalize"><?= e(implode(', ', $roles)) ?></div>
        </div>
        <a href="<?= base_url('logout') ?>" data-logout class="ml-1 w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition" title="Keluar">
            <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
        </a>
    </div>
</header>

<!-- Sidebar Overlay (mobile) -->
<div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden"></div>

<!-- ===== SIDEBAR ===== -->
<aside id="sidebar" class="glass-sidebar fixed top-16 left-0 bottom-0 z-30 w-64 border-r flex flex-col overflow-y-auto scrollbar-hide
    -translate-x-full lg:translate-x-0">

    <!-- Nav Sections -->
    <nav class="flex-1 p-3 space-y-1">

        <?php
        $uri = $_SERVER['REQUEST_URI'];
        $isActive = fn($path) => strpos($uri, $path) !== false;

        $navAdmin = [
            ['icon'=>'fa-gauge-high',     'label'=>'Dashboard',        'url'=>'admin/dashboard',          'key'=>'dashboard'],
            ['icon'=>'fa-calendar-days',  'label'=>'Periode Penilaian','url'=>'admin/periode',            'key'=>'periode'],
            ['icon'=>'fa-school',         'label'=>'Data Madrasah',    'url'=>'admin/madrasah',           'key'=>'madrasah'],
            ['icon'=>'fa-user-tie',       'label'=>'Kepala Madrasah',  'url'=>'admin/kepala-madrasah',    'key'=>'kepala'],
            ['icon'=>'fa-users',          'label'=>'Pengguna & Penilai','url'=>'admin/pengguna',          'key'=>'pengguna'],
            ['icon'=>'fa-book-open',      'label'=>'Master Instrumen', 'url'=>'admin/instrumen',          'key'=>'instrumen'],
            ['icon'=>'fa-chart-pie',      'label'=>'Rekapitulasi',     'url'=>'laporan',                  'key'=>'laporan'],
            ['icon'=>'fa-folder-open',    'label'=>'Bukti Dukung',     'url'=>'bukti',                    'key'=>'bukti'],
            ['icon'=>'fa-file-lines',     'label'=>'Audit Log',        'url'=>'admin/audit-log',          'key'=>'audit'],
            ['icon'=>'fa-gear',           'label'=>'Pengaturan',       'url'=>'admin/pengaturan',         'key'=>'pengaturan'],
        ];
        $navKamad = [
            ['icon'=>'fa-gauge-high',     'label'=>'Dashboard',        'url'=>'kamad/dashboard',          'key'=>'dashboard'],
            ['icon'=>'fa-cloud-arrow-up', 'label'=>'Bukti Kinerja',    'url'=>'bukti',                    'key'=>'bukti'],
            ['icon'=>'fa-chart-bar',      'label'=>'Hasil Penilaian',  'url'=>'laporan',                  'key'=>'laporan'],
        ];
        $navPenilai = [
            ['icon'=>'fa-gauge-high',       'label'=>'Dashboard',         'url'=>'penilai/dashboard',        'key'=>'dashboard'],
            ['icon'=>'fa-pen-to-square',    'label'=>'Form Penilaian',    'url'=>'penilai/penilaian',        'key'=>'penilaian'],
            ['icon'=>'fa-folder-open',      'label'=>'Bukti Dukung',      'url'=>'bukti',                    'key'=>'bukti'],
        ];

        $nav = \App\Core\Auth::hasRole('admin') ? $navAdmin :
               (\App\Core\Auth::hasRole('kamad') ? $navKamad : $navPenilai);
        ?>

        <?php foreach ($nav as $item): ?>
            <?php $active = $isActive($item['key']); ?>
            <a href="<?= base_url($item['url']) ?>"
               class="sidebar-item <?= $active ? 'active' : '' ?> flex items-center space-x-3 px-3 py-2.5 rounded-xl text-sm font-medium <?= $active ? 'text-brand-600' : 'text-slate-600' ?>">
                <i class="fa-solid <?= $item['icon'] ?> w-4 text-center <?= $active ? 'text-brand-500' : 'text-slate-400' ?>"></i>
                <span><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Sidebar Footer -->
    <div class="p-3 border-t border-slate-100">
        <div class="flex items-center space-x-3 px-3 py-2.5 rounded-xl bg-slate-50">
            <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs text-white shrink-0"
                 style="background:linear-gradient(135deg,#1e6347,#17503a);">
                <?= strtoupper(substr(\App\Core\Auth::user()['nama_lengkap'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-semibold text-slate-700 truncate"><?= e(\App\Core\Auth::user()['nama_lengkap'] ?? '') ?></div>
                <div class="text-[10px] text-slate-400"><?= e(\App\Core\Auth::user()['jabatan'] ?? implode(', ', \App\Core\Auth::roles())) ?></div>
            </div>
        </div>
    </div>
</aside>

<!-- ===== MAIN CONTENT ===== -->
<div class="lg:ml-64 pt-16 min-h-screen flex flex-col">
    <main class="glass-page flex-1 p-4 lg:p-6 max-w-screen-xl">

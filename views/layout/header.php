<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'PKKM') ?> — Sistem Informasi PKKM</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            50:  '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            400: '#818cf8',
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Global handler: Semua SweetAlert sukses otomatis auto close dengan timer
        (function() {
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
        })();
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        html, body { height: 100%; }
        .sidebar-item { transition: all .15s; }
        .sidebar-item:hover { background: rgba(99,102,241,0.08); }
        .sidebar-item.active {
            background: linear-gradient(135deg, rgba(99,102,241,0.15) 0%, rgba(79,70,229,0.1) 100%);
            border-right: 2px solid #6366f1;
            color: #6366f1;
        }
        .sidebar-item.active i { color: #6366f1; }
        .card { background: #fff; border: 1px solid #f1f5f9; border-radius: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); }
        .card-hover { transition: all .2s; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
        .badge-role {
            font-size: 10px; font-weight: 700; letter-spacing: .05em;
            padding: 3px 8px; border-radius: 999px;
        }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .gradient-text {
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        /* Sidebar transition on mobile */
        #sidebar { transition: transform .25s ease; }
    </style>
</head>
<body class="bg-slate-50" style="font-family:'Inter',sans-serif;">

<!-- ===== TOPBAR ===== -->
<header class="fixed top-0 left-0 right-0 z-40 h-16 bg-white border-b border-slate-100 flex items-center px-4 lg:px-6 gap-4" style="box-shadow:0 1px 3px rgba(0,0,0,0.06);">

    <!-- Hamburger (mobile) -->
    <button onclick="toggleSidebar()" class="lg:hidden w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 transition">
        <i class="fa-solid fa-bars text-base"></i>
    </button>

    <!-- Logo -->
    <a href="<?= base_url('dashboard') ?>" class="flex items-center space-x-2.5 shrink-0">
        <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-white text-sm shadow"
             style="background:linear-gradient(135deg,#6366f1,#4338ca);">P</div>
        <div class="hidden sm:block">
            <div class="font-bold text-slate-800 text-sm leading-tight tracking-tight">PKKM</div>
            <div class="text-[10px] text-slate-400 font-medium leading-tight">Kinerja Kepala Madrasah</div>
        </div>
    </a>

    <div class="flex-1"></div>

    <!-- Notifications placeholder -->
    <button class="relative w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 transition">
        <i class="fa-regular fa-bell text-base"></i>
    </button>

    <!-- User Chip -->
    <div class="flex items-center space-x-2.5 pl-2 border-l border-slate-100">
        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm text-white shrink-0"
             style="background:linear-gradient(135deg,#6366f1,#4338ca);">
            <?= strtoupper(substr(\App\Core\Auth::user()['nama_lengkap'] ?? 'U', 0, 1)) ?>
        </div>
        <div class="hidden sm:block">
            <div class="text-xs font-semibold text-slate-700 leading-tight max-w-[140px] truncate"><?= e(\App\Core\Auth::user()['nama_lengkap'] ?? 'Pengguna') ?></div>
            <?php $roles = \App\Core\Auth::roles(); ?>
            <div class="text-[10px] text-brand-600 font-semibold capitalize"><?= e(implode(', ', $roles)) ?></div>
        </div>
        <a href="<?= base_url('logout') ?>" class="ml-1 w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition" title="Keluar">
            <i class="fa-solid fa-arrow-right-from-bracket text-sm"></i>
        </a>
    </div>
</header>

<!-- Sidebar Overlay (mobile) -->
<div id="sidebarOverlay" onclick="toggleSidebar()" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden"></div>

<!-- ===== SIDEBAR ===== -->
<aside id="sidebar" class="fixed top-16 left-0 bottom-0 z-30 w-64 bg-white border-r border-slate-100 flex flex-col overflow-y-auto scrollbar-hide
    -translate-x-full lg:translate-x-0"
    style="box-shadow:1px 0 0 #f1f5f9;">

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
                 style="background:linear-gradient(135deg,#6366f1,#4338ca);">
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
    <main class="flex-1 p-4 lg:p-6 max-w-screen-xl mx-auto w-full">

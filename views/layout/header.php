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
                            50:  '#eef7f1',
                            100: '#d6ece0',
                            200: '#aedcc2',
                            300: '#7cc39f',
                            400: '#4c9d75',
                            500: '#2b7d55',
                            600: '#1e6347',
                            700: '#17503a',
                            800: '#113d2c',
                            900: '#0b2a1e',
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
            background-attachment: fixed;
        }
        .sidebar-item { transition: all .15s; }
        .sidebar-item:hover { background: rgba(30, 99, 71, 0.08); }
        .sidebar-item.active {
            background: linear-gradient(135deg, rgba(30, 99, 71, 0.15) 0%, rgba(23, 80, 58, 0.1) 100%);
            border-right: 2px solid #1e6347;
            color: #1e6347;
        }
        .sidebar-item.active i { color: #1e6347; }

        /* ── Glass surfaces ───────────────────────────────── */
        .glass-topbar {
            background: rgba(255, 255, 255, 0.62) !important;
            backdrop-filter: blur(20px) saturate(150%);
            -webkit-backdrop-filter: blur(20px) saturate(150%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.65) !important;
            box-shadow: 0 1px 3px rgba(11, 42, 30, 0.06) !important;
        }
        .glass-sidebar {
            background: rgba(255, 255, 255, 0.52) !important;
            backdrop-filter: blur(24px) saturate(150%);
            -webkit-backdrop-filter: blur(24px) saturate(150%);
            border-right: 1px solid rgba(255, 255, 255, 0.6) !important;
            box-shadow: 1px 0 0 rgba(255, 255, 255, 0.35) !important;
        }
        .glass-page {
            position: relative;
            isolation: isolate;
            width: calc(100% - 1.5rem);
            margin: 1rem auto 1.5rem;
            background: rgba(255, 255, 255, 0.34);
            border: 1px solid rgba(255, 255, 255, 0.55);
            border-radius: 24px;
            box-shadow: var(--glass-shadow);
        }
        .glass-page::before {
            content: "";
            position: absolute;
            inset: 0;
            z-index: -1;
            border-radius: inherit;
            pointer-events: none;
            backdrop-filter: blur(22px) saturate(140%);
            -webkit-backdrop-filter: blur(22px) saturate(140%);
        }
        @media (min-width: 1024px) {
            .glass-page {
                width: calc(100% - 3rem);
                margin: 1.5rem auto 2rem;
            }
        }
        .card {
            background: rgba(255, 255, 255, 0.68);
            border: 1px solid rgba(255, 255, 255, 0.7);
            border-radius: 16px;
            box-shadow: 0 8px 30px -18px rgba(11, 42, 30, 0.35);
            backdrop-filter: blur(14px) saturate(130%);
            -webkit-backdrop-filter: blur(14px) saturate(130%);
        }
        .card-hover { transition: all .2s; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(11, 42, 30, 0.12); }

        /* ── Page transition ──────────────────────────────── */
        @keyframes pageIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes sectionIn {
            from { opacity: 0; transform: translateY(14px); }
            to   { opacity: 1; transform: none; }
        }
        .glass-page {
            animation: pageIn .4s ease both;
            transition: opacity .22s ease;
            will-change: opacity;
        }
        /* Modal (.fixed) sengaja dikecualikan agar tetap full viewport */
        .glass-page > *:not(.fixed) {
            animation: sectionIn .5s cubic-bezier(.22, 1, .36, 1) both;
        }
        .glass-page > *:not(.fixed):nth-child(1) { animation-delay: .04s; }
        .glass-page > *:not(.fixed):nth-child(2) { animation-delay: .1s; }
        .glass-page > *:not(.fixed):nth-child(3) { animation-delay: .16s; }
        .glass-page > *:not(.fixed):nth-child(n+4) { animation-delay: .22s; }
        @keyframes pageLeave {
            from { opacity: 1; }
            to   { opacity: 0; }
        }
        body.page-leaving .glass-page {
            animation: pageLeave .22s ease forwards;
        }
        body.page-leaving .glass-page > * { animation: none; }

        /* Progress bar saat pindah menu */
        #pageProgress {
            position: fixed;
            top: 0;
            left: 0;
            height: 2px;
            width: 0;
            z-index: 60;
            pointer-events: none;
            background: linear-gradient(90deg, #2b7d55, #4c9d75);
            box-shadow: 0 0 10px rgba(76, 157, 117, .7);
            opacity: 0;
            transition: width .25s ease, opacity .2s ease;
        }
        body.page-leaving #pageProgress { width: 78%; opacity: 1; }

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
        @keyframes pageInReduced {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes sectionFade {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @media (prefers-reduced-motion: reduce) {
            .glass-page { animation: pageInReduced .35s ease both !important; transition: opacity .2s ease; }
            .glass-page > *:not(.fixed) { animation: sectionFade .35s ease both !important; }
            body.page-leaving .glass-page { animation: pageLeave .18s ease forwards !important; opacity: 1; }
            html { scroll-behavior: auto; }
        }

        /* Stagger menu sidebar saat halaman dimuat */
        @keyframes navIn {
            from { opacity: 0; transform: translateX(-10px); }
            to   { opacity: 1; transform: none; }
        }
        .sidebar-item { animation: navIn .35s cubic-bezier(.22, 1, .36, 1) both; }
        .sidebar-item:nth-child(1) { animation-delay: .02s; }
        .sidebar-item:nth-child(2) { animation-delay: .06s; }
        .sidebar-item:nth-child(3) { animation-delay: .1s; }
        .sidebar-item:nth-child(4) { animation-delay: .14s; }
        .sidebar-item:nth-child(5) { animation-delay: .18s; }
        .sidebar-item:nth-child(6) { animation-delay: .22s; }
        .sidebar-item:nth-child(7) { animation-delay: .26s; }
        .sidebar-item:nth-child(8) { animation-delay: .3s; }
        .sidebar-item:nth-child(n+9) { animation-delay: .34s; }
        @media (prefers-reduced-motion: reduce) {
            .sidebar-item { animation: navInReduced .25s ease both !important; }
        }
        @keyframes navInReduced {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
    </style>
</head>
<body style="font-family:'Inter',sans-serif;">
<div id="pageProgress"></div>

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

    <!-- Notifications placeholder -->
    <button class="relative w-9 h-9 flex items-center justify-center rounded-lg text-slate-500 hover:bg-slate-100 transition">
        <i class="fa-regular fa-bell text-base"></i>
    </button>

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
        <a href="<?= base_url('logout') ?>" class="ml-1 w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition" title="Keluar">
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

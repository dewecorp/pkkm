<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#1e6347">
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
        * {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }
        html {
            height: 100%;
            scroll-behavior: smooth;
        }

        /* ── Scrollbar Halus Global ────────────────────────── */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.45);
            border-radius: 999px;
            transition: background 0.2s ease;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.75);
        }
        ::-webkit-scrollbar-button {
            display: none;
            width: 0;
            height: 0;
        }

        /* Firefox standard scrollbar (kecuali yang di-hide) */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.45) transparent;
        }

        /* Hilangkan total scrollbar di sidebar dan elemen scrollbar-hide */
        #sidebar, #sidebar nav, .scrollbar-hide {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }
        #sidebar::-webkit-scrollbar,
        #sidebar nav::-webkit-scrollbar,
        .scrollbar-hide::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        /* Khusus scroll notifikasi */
        #notifList {
            scroll-behavior: smooth;
            overscroll-behavior: contain;
        }
        #notifList::-webkit-scrollbar {
            width: 5px;
        }
        #notifList::-webkit-scrollbar-thumb {
            background: rgba(30, 99, 71, 0.35);
            border-radius: 999px;
        }
        #notifList::-webkit-scrollbar-thumb:hover {
            background: rgba(30, 99, 71, 0.65);
        }
        body {
            min-height: 100%;
            background:
                radial-gradient(1100px 520px at 8% -8%, rgba(30, 99, 71, 0.20), transparent 62%),
                radial-gradient(900px 480px at 98% 4%, rgba(76, 157, 117, 0.18), transparent 58%),
                radial-gradient(900px 600px at 50% 110%, rgba(13, 148, 136, 0.12), transparent 62%),
                linear-gradient(160deg, #f3f7f4 0%, #eef3f0 45%, #e8f0eb 100%);
        }
        .sidebar-item {
            color: rgba(255, 255, 255, 0.82) !important;
            transition: all .15s ease;
        }
        .sidebar-item i {
            color: rgba(167, 243, 208, 0.85) !important;
            transition: color .15s ease;
        }
        .sidebar-item:hover {
            background: rgba(255, 255, 255, 0.12) !important;
            color: #ffffff !important;
        }
        .sidebar-item:hover i {
            color: #ffffff !important;
        }
        .sidebar-item.active {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.22) 0%, rgba(255, 255, 255, 0.12) 100%) !important;
            border-right: 3px solid #34d399 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
        }
        .sidebar-item.active i {
            color: #6ee7b7 !important;
        }

        /* ── Glass surfaces (Kemenag Glassy) ──────────────── */
        .glass-topbar {
            background: rgba(30, 99, 71, 0.78) !important;
            backdrop-filter: blur(18px) saturate(180%);
            -webkit-backdrop-filter: blur(18px) saturate(180%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.22) !important;
            box-shadow: 0 4px 24px -2px rgba(11, 42, 30, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.3) !important;
        }
        .glass-topbar a, .glass-topbar button {
            color: #ffffff;
        }
        .glass-topbar i {
            color: #ffffff;
        }
        .glass-sidebar {
            background: rgba(22, 78, 55, 0.82) !important;
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-right: 1px solid rgba(255, 255, 255, 0.18) !important;
            box-shadow: 4px 0 28px rgba(11, 42, 30, 0.22), inset -1px 0 0 rgba(255, 255, 255, 0.14) !important;
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
            .glass-topbar { background: #17503a !important; }
            .glass-sidebar { background: #144633 !important; }
            .glass-page { background: rgba(255, 255, 255, 0.9); }
            .card { background: rgba(255, 255, 255, 0.96); }
        }
        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
        }
        /* Ruang aman bawah untuk bottom nav mobile */
        .safe-bottom { padding-bottom: env(safe-area-inset-bottom, 0px); }
        .mobile-tab { -webkit-tap-highlight-color: transparent; }

        /* ── Tabel jadi kartu di mobile (tanpa geser horizontal) ── */
        @media (max-width: 1023.98px) {
            table.table-stack { display: block; width: 100% !important; border-collapse: collapse; }
            table.table-stack thead { display: none; }
            table.table-stack tbody { display: block; width: 100%; }
            table.table-stack tfoot { display: block; width: 100%; }
            table.table-stack tbody > tr,
            table.table-stack tfoot > tr {
                display: block;
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 6px 14px;
                margin: 0 0 12px;
            }
            table.table-stack tbody > tr:last-child,
            table.table-stack tfoot > tr:last-child { margin-bottom: 0; }
            table.table-stack td,
            table.table-stack th {
                display: block;
                width: auto !important;
                max-width: none !important;
                padding: 9px 0 !important;
                border: 0 !important;
                text-align: left !important;
                white-space: normal !important;
                word-break: break-word;
            }
            table.table-stack td + td,
            table.table-stack th + th { border-top: 1px solid #f1f5f9 !important; }
            table.table-stack td[data-label]::before,
            table.table-stack th[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .04em;
                color: #94a3b8;
                margin-bottom: 4px;
            }
            table.table-stack td[data-label=""]::before { display: none; }
            table.table-stack td.table-stack-plain { padding-top: 0 !important; }
            table.table-stack .table-stack-actions { display: flex; flex-wrap: wrap; gap: 8px; }
        }
    </style>
</head>
<body style="font-family:'Inter',sans-serif;">

<?php
$uri = $_SERVER['REQUEST_URI'];
$isActive = fn($path) => strpos($uri, $path) !== false;
$nav = nav_items();
$isDashboard = strpos($uri, '/dashboard') !== false;

$dashboardUrl = \App\Core\Auth::hasRole('admin')
    ? 'admin/dashboard'
    : (\App\Core\Auth::hasRole('kamad') ? 'kamad/dashboard' : 'penilai/dashboard');

$roleLabel = \App\Core\Auth::hasRole('admin')
    ? 'Dashboard Admin'
    : (\App\Core\Auth::hasRole('kamad') ? 'Dashboard Kepala Madrasah' : 'Dashboard Penilai');

$appTitle = $title ?? 'PKKM';
$userName = \App\Core\Auth::user()['nama_lengkap'] ?? 'Pengguna';
$userInitial = strtoupper(substr($userName, 0, 1));
$bolehNotif = \App\Core\Auth::hasAnyRole(['admin', 'kamad']);
?>

<!-- ===== TOPBAR (desktop) ===== -->
<header class="glass-topbar hidden lg:flex fixed top-0 left-0 right-0 z-40 h-16 border-b items-center px-4 lg:px-6 gap-4">

    <!-- Logo -->
    <a href="<?= base_url('dashboard') ?>" class="flex items-center space-x-2.5 shrink-0 group">
        <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-sm shadow-md bg-white border border-white/40" style="color:#1e6347;">P</div>
        <div class="hidden sm:block">
            <div class="font-extrabold text-white text-sm leading-tight tracking-tight">PKKM</div>
            <div class="text-[10px] font-medium leading-tight" style="color:#a7f3d0;">Kinerja Kepala Madrasah</div>
        </div>
    </a>

    <div class="flex-1"></div>

    <?php if ($bolehNotif): ?>
    <!-- Notifikasi: hanya admin & kepala madrasah -->
    <button type="button" data-notif-toggle onclick="toggleNotif()" aria-label="Notifikasi"
            class="relative w-9 h-9 flex items-center justify-center rounded-xl transition hover:bg-white/15" style="color:#ffffff;">
        <i class="fa-regular fa-bell text-base" style="color:#ffffff;"></i>
        <span data-notif-badge
              class="hidden absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center border-2 border-[#1e6347]">0</span>
    </button>
    <?php endif; ?>

    <!-- User Chip (Pill Capsule rapi tanpa garis border pembatas aneh) -->
    <div class="flex items-center gap-2.5 px-3 py-1 rounded-full bg-white/15 border border-white/20 shadow-xs backdrop-blur-sm">
        <div class="w-7 h-7 rounded-full flex items-center justify-center font-bold text-xs bg-white shadow-xs shrink-0 border border-white/40" style="color:#1e6347;">
            <?= $userInitial ?>
        </div>
        <div class="hidden sm:block">
            <div class="text-xs font-bold text-white leading-tight max-w-[150px] truncate"><?= e($userName) ?></div>
            <?php $roles = \App\Core\Auth::roles(); ?>
            <div class="text-[10px] font-semibold capitalize leading-tight" style="color:#a7f3d0;"><?= e(implode(', ', $roles)) ?></div>
        </div>
    </div>

    <!-- Tombol Logout -->
    <a href="<?= base_url('logout') ?>" data-logout class="w-8 h-8 flex items-center justify-center rounded-xl transition hover:bg-white/15" style="color:#ffffff;" title="Keluar">
        <i class="fa-solid fa-arrow-right-from-bracket text-sm" style="color:#ffffff;"></i>
    </a>
</header>

<!-- ===== APP BAR (mobile) ===== -->
<header class="lg:hidden fixed top-0 left-0 right-0 z-40 h-16 rounded-b-3xl shadow-lg flex items-center gap-3 px-4 border-b border-white/15"
        style="background:linear-gradient(135deg,rgba(30,99,71,0.94) 0%,rgba(23,80,58,0.96) 100%);backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);">

    <?php if ($isDashboard): ?>
        <!-- Dashboard: logo + nama aplikasi -->
        <a href="<?= base_url($dashboardUrl) ?>" class="flex items-center gap-2.5 min-w-0">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center font-black text-white text-sm shrink-0 border border-white/25"
                 style="background:rgba(255,255,255,0.14);">P</div>
            <div class="min-w-0">
                <div class="text-white font-bold text-sm leading-tight tracking-tight">PKKM</div>
                <div class="text-white/70 text-[10px] leading-tight truncate"><?= e($roleLabel) ?></div>
            </div>
        </a>

        <div class="flex-1"></div>

        <?php if ($bolehNotif): ?>
        <button type="button" data-notif-toggle onclick="toggleNotif()" aria-label="Notifikasi"
                class="relative w-9 h-9 rounded-full flex items-center justify-center text-white shrink-0"
                style="background:rgba(255,255,255,0.14);">
            <i class="fa-regular fa-bell text-base"></i>
            <span data-notif-badge
                  class="hidden absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center">0</span>
        </button>
        <?php endif; ?>

        <button type="button" onclick="bukaAkun()" aria-label="Akun"
                class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm text-white shrink-0 border border-white/25"
                style="background:rgba(255,255,255,0.14);">
            <?= $userInitial ?>
        </button>

    <?php else: ?>
        <!-- Halaman menu: tombol kembali + judul halaman -->
        <button type="button" id="appBack" data-fallback="<?= base_url($dashboardUrl) ?>"
                onclick="kembaliHalaman()" aria-label="Kembali"
                class="relative z-10 w-9 h-9 rounded-full flex items-center justify-center text-white shrink-0"
                style="background:rgba(255,255,255,0.14);">
            <i class="fa-solid fa-chevron-left text-sm"></i>
        </button>

        <div class="absolute left-1/2 -translate-x-1/2 max-w-[60%] text-center">
            <div class="text-white font-bold text-sm leading-tight truncate"><?= e($appTitle) ?></div>
        </div>

        <div class="flex-1"></div>

        <button type="button" onclick="bukaAkun()" aria-label="Akun"
                class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-sm text-white shrink-0 border border-white/25"
                style="background:rgba(255,255,255,0.14);">
            <?= $userInitial ?>
        </button>
    <?php endif; ?>
</header>

<?php if ($bolehNotif): ?>
<!-- Panel notifikasi bersama (desktop & mobile) -->
<div id="notifPanel"
     class="hidden fixed top-[4.25rem] right-3 lg:right-6 w-80 max-w-[calc(100vw-1.5rem)] bg-white rounded-xl border border-slate-200 shadow-xl overflow-hidden z-50">
    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
        <span class="text-xs font-bold text-slate-700">Notifikasi</span>
        <button type="button" onclick="bacaSemuaNotif()"
                class="text-[11px] font-semibold text-brand-600 hover:underline">Tandai dibaca</button>
    </div>
    <div id="notifList" class="max-h-80 overflow-y-auto divide-y divide-slate-100">
        <div class="px-4 py-6 text-center text-xs text-slate-400">Memuat…</div>
    </div>
</div>
<?php endif; ?>

<!-- ===== SIDEBAR (desktop only) ===== -->
<aside id="sidebar" class="glass-sidebar fixed top-16 left-0 bottom-0 z-30 w-64 border-r hidden lg:flex flex-col overflow-hidden">

    <!-- Nav Sections (Tanpa scrollbar, menu kompak pas) -->
    <nav class="flex-1 p-2.5 space-y-0.5 overflow-y-auto scrollbar-hide">

        <?php foreach ($nav as $item): ?>
            <?php $active = $isActive($item['key']); ?>
            <a href="<?= base_url($item['url']) ?>"
               class="sidebar-item <?= $active ? 'active' : '' ?> flex items-center space-x-3 px-3 py-2 rounded-xl text-xs sm:text-sm font-medium">
                <i class="fa-solid <?= $item['icon'] ?> w-4 text-center"></i>
                <span><?= $item['label'] ?></span>
            </a>
        <?php endforeach; ?>
    </nav>

    <!-- Sidebar Footer: Tombol Keluar Selalu Terlihat (Pinned Bottom, shrink-0) -->
    <div class="p-2.5 border-t border-white/15 shrink-0 bg-transparent">
        <a href="<?= base_url('logout') ?>" data-logout
           class="flex items-center space-x-3 px-3 py-2 rounded-xl text-xs sm:text-sm font-bold text-rose-200 hover:text-white bg-rose-500/20 hover:bg-rose-600/35 border border-rose-400/35 transition shadow-xs group">
            <i class="fa-solid fa-arrow-right-from-bracket w-4 text-center text-rose-300 group-hover:text-white transition"></i>
            <span>Keluar Sistem</span>
        </a>
    </div>
</aside>

<?php
$navByKey = [];
foreach ($nav as $n) { $navByKey[$n['key']] = $n; }

$mk = function (string $key, string $label) use ($navByKey) {
    $n = $navByKey[$key];
    return ['type' => 'link', 'key' => $key, 'icon' => $n['icon'], 'label' => $label, 'url' => $n['url']];
};

$mobileTabs = \App\Core\Auth::hasRole('admin')
    ? [
        $mk('dashboard', 'Beranda'),
        $mk('periode', 'Periode'),
        $mk('pengguna', 'Pengguna'),
        $mk('laporan', 'Rekap'),
        ['type' => 'menu', 'key' => 'menu', 'icon' => 'fa-table-cells-large', 'label' => 'Menu'],
    ]
    : (\App\Core\Auth::hasRole('kamad')
        ? [
            $mk('dashboard', 'Beranda'),
            $mk('bukti', 'Bukti'),
            $mk('laporan', 'Rekap'),
            ['type' => 'notif', 'key' => 'notif', 'icon' => 'fa-bell', 'label' => 'Notif'],
            ['type' => 'menu', 'key' => 'menu', 'icon' => 'fa-table-cells-large', 'label' => 'Menu'],
        ]
        : [
            $mk('dashboard', 'Beranda'),
            $mk('penilaian', 'Nilai'),
            $mk('bukti', 'Bukti'),
            ['type' => 'menu', 'key' => 'menu', 'icon' => 'fa-table-cells-large', 'label' => 'Menu'],
            ['type' => 'akun', 'key' => 'akun', 'icon' => 'fa-circle-user', 'label' => 'Akun'],
        ]);
?>

<!-- ===== BOTTOM NAV (mobile only, 5 tab) ===== -->
<nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 border-t border-slate-200 safe-bottom">
    <div class="flex items-stretch">
        <?php foreach ($mobileTabs as $tab): ?>
        <?php
            $active = $tab['type'] === 'link' ? $isActive($tab['key']) : false;
            $cls = 'mobile-tab relative flex-1 flex flex-col items-center justify-center gap-1 py-2.5 '
                 . ($active ? 'text-brand-600' : 'text-slate-400');
        ?>
        <?php if ($tab['type'] === 'link'): ?>
            <a href="<?= base_url($tab['url']) ?>" class="<?= $cls ?>">
                <i class="fa-solid <?= $tab['icon'] ?> text-[17px]"></i>
                <span class="text-[10px] font-bold leading-none"><?= e($tab['label']) ?></span>
            </a>
        <?php elseif ($tab['type'] === 'menu'): ?>
            <a href="<?= base_url($dashboardUrl) ?>#menuGrid" class="<?= $cls ?>">
                <i class="fa-solid <?= $tab['icon'] ?> text-[17px]"></i>
                <span class="text-[10px] font-bold leading-none"><?= e($tab['label']) ?></span>
            </a>
        <?php elseif ($tab['type'] === 'notif'): ?>
            <button type="button" data-notif-toggle onclick="toggleNotif()" class="<?= $cls ?>">
                <span id="notifTabDot" class="hidden absolute top-1.5 right-[calc(50%-14px)] w-2 h-2 rounded-full bg-rose-500"></span>
                <i class="fa-solid <?= $tab['icon'] ?> text-[17px]"></i>
                <span class="text-[10px] font-bold leading-none"><?= e($tab['label']) ?></span>
            </button>
        <?php else: ?>
            <button type="button" onclick="bukaAkun()" class="<?= $cls ?>">
                <i class="fa-solid <?= $tab['icon'] ?> text-[17px]"></i>
                <span class="text-[10px] font-bold leading-none"><?= e($tab['label']) ?></span>
            </button>
        <?php endif; ?>
        <?php endforeach; ?>
    </div>
</nav>

<!-- Sheet Akun (mobile) -->
<div id="akunSheet" class="lg:hidden fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-slate-900/40" onclick="tutupAkun()"></div>
    <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl p-5 safe-bottom shadow-2xl">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-full flex items-center justify-center font-bold text-white text-base shrink-0"
                 style="background:linear-gradient(135deg,#1e6347,#17503a);">
                <?= strtoupper(substr(\App\Core\Auth::user()['nama_lengkap'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="min-w-0">
                <div class="text-sm font-bold text-slate-800 truncate"><?= e(\App\Core\Auth::user()['nama_lengkap'] ?? 'Pengguna') ?></div>
                <div class="text-[11px] text-brand-600 font-semibold capitalize"><?= e(implode(', ', \App\Core\Auth::roles())) ?></div>
            </div>
        </div>
        <a href="<?= base_url('logout') ?>" data-logout
           class="mt-4 flex items-center justify-center gap-2 w-full py-3 rounded-xl bg-rose-50 text-rose-600 font-bold text-sm">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Keluar</span>
        </a>
    </div>
</div>

<!-- ===== MAIN CONTENT ===== -->
<div class="lg:ml-64 pt-16 min-h-screen flex flex-col">
    <main class="glass-page flex-1 p-4 pb-24 lg:p-6 lg:pb-6 max-w-screen-xl">

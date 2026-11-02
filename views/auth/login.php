<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PKKM — Masuk</title>
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
                            500: '#6366f1',
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                        }
                    },
                    backgroundImage: {
                        'grid-white': "url(\"data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(255 255 255 / 0.05)'%3e%3cpath d='M0 .5H31.5V32'/%3e%3c/svg%3e\")",
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        .glass { backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }
        .input-field {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.12);
            color: #fff;
            transition: all .2s;
        }
        .input-field::placeholder { color: rgba(255,255,255,0.35); }
        .input-field:focus {
            outline: none;
            border-color: rgba(99,102,241,0.7);
            background: rgba(255,255,255,0.09);
            box-shadow: 0 0 0 3px rgba(99,102,241,0.2);
        }
        .btn-login {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #4338ca 100%);
            box-shadow: 0 4px 24px rgba(99,102,241,0.45);
            transition: all .2s;
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 32px rgba(99,102,241,0.55);
        }
        .btn-login:active { transform: translateY(0); }
        .floating-card {
            animation: float 6s ease-in-out infinite;
        }
        .floating-card-2 {
            animation: float 8s ease-in-out infinite;
            animation-delay: -3s;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        .stat-badge {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.12);
        }
    </style>
</head>
<body class="min-h-screen flex" style="background: #0f0e17;">

    <!-- LEFT PANEL — Branding & Visual -->
    <div class="hidden lg:flex lg:w-[58%] relative overflow-hidden flex-col justify-between p-12"
         style="background: linear-gradient(135deg, #0f0e17 0%, #1a1040 40%, #0d1b4b 70%, #0a2a6e 100%);">

        <!-- Grid overlay -->
        <div class="absolute inset-0 bg-grid-white opacity-40 pointer-events-none"></div>

        <!-- Decorative blobs -->
        <div class="absolute top-[-80px] left-[-80px] w-[400px] h-[400px] rounded-full opacity-20 pointer-events-none"
             style="background: radial-gradient(circle, #6366f1 0%, transparent 70%); filter: blur(60px);"></div>
        <div class="absolute bottom-[-100px] right-[-60px] w-[350px] h-[350px] rounded-full opacity-15 pointer-events-none"
             style="background: radial-gradient(circle, #06b6d4 0%, transparent 70%); filter: blur(70px);"></div>
        <div class="absolute top-[40%] right-[10%] w-[200px] h-[200px] rounded-full opacity-10 pointer-events-none"
             style="background: radial-gradient(circle, #a855f7 0%, transparent 70%); filter: blur(50px);"></div>

        <!-- Logo & Brand -->
        <div class="relative z-10">
            <div class="flex items-center space-x-3">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center font-black text-white text-xl shadow-lg"
                     style="background: linear-gradient(135deg, #6366f1, #4338ca);">P</div>
                <div>
                    <div class="text-white font-bold text-lg tracking-tight">PKKM</div>
                    <div class="text-xs font-medium" style="color: rgba(255,255,255,0.45);">Kementerian Agama RI</div>
                </div>
            </div>
        </div>

        <!-- Center Content -->
        <div class="relative z-10 space-y-8">
            <div class="space-y-4">
                <div class="inline-flex items-center space-x-2 px-3 py-1.5 rounded-full text-xs font-semibold"
                     style="background: rgba(99,102,241,0.15); border: 1px solid rgba(99,102,241,0.3); color: #a5b4fc;">
                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
                    <span>Sistem Penilaian Digital Terintegrasi</span>
                </div>

                <h1 class="text-5xl font-black text-white leading-tight tracking-tight">
                    Penilaian<br>
                    <span style="background: linear-gradient(90deg, #818cf8, #6366f1, #a78bfa); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Kinerja</span><br>
                    Kepala Madrasah
                </h1>

                <p class="text-base leading-relaxed max-w-sm" style="color: rgba(255,255,255,0.5);">
                    Platform digital resmi PKKM — kelola, nilai, dan pantau kinerja kepala madrasah secara transparan dan akuntabel.
                </p>
            </div>

            <!-- Floating Stat Cards -->
            <div class="space-y-3">
                <div class="floating-card flex items-center space-x-4 p-4 rounded-2xl stat-badge glass max-w-xs">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-indigo-300 text-base shrink-0"
                         style="background: rgba(99,102,241,0.2);">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                    <div>
                        <div class="text-xs font-medium" style="color: rgba(255,255,255,0.45);">Komponen Penilaian</div>
                        <div class="text-white font-bold text-sm">5 Komponen · 108 Butir Indikator</div>
                    </div>
                </div>

                <div class="floating-card-2 flex items-center space-x-4 p-4 rounded-2xl stat-badge glass max-w-xs ml-6">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-emerald-300 text-base shrink-0"
                         style="background: rgba(16,185,129,0.2);">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div>
                        <div class="text-xs font-medium" style="color: rgba(255,255,255,0.45);">Kelompok Penilai</div>
                        <div class="text-white font-bold text-sm">6 Kelompok · KS, KY, WASMAD, dll</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bottom Footer Brand -->
        <div class="relative z-10">
            <p class="text-xs" style="color: rgba(255,255,255,0.3);">
                © <?= date('Y') ?> Bidang Pendidikan Madrasah · Kanwil Kemenag Jawa Tengah
            </p>
        </div>
    </div>

    <!-- RIGHT PANEL — Login Form -->
    <div class="flex-1 flex items-center justify-center p-6 lg:p-12 relative"
         style="background: #13111f;">

        <!-- Mobile logo -->
        <div class="absolute top-6 left-6 lg:hidden flex items-center space-x-2">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-black text-white text-base"
                 style="background: linear-gradient(135deg, #6366f1, #4338ca);">P</div>
            <span class="text-white font-bold text-sm">PKKM</span>
        </div>

        <div class="w-full max-w-sm space-y-8">
            <!-- Heading -->
            <div class="space-y-2">
                <h2 class="text-3xl font-black text-white tracking-tight">Selamat Datang</h2>
                <p class="text-sm" style="color: rgba(255,255,255,0.45);">
                    Masuk ke akun Anda untuk melanjutkan
                </p>
            </div>

            <!-- Form -->
            <form id="loginForm" class="space-y-5" autocomplete="off">
                <?= csrf_field() ?>

                <!-- Username -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold tracking-wider uppercase" style="color: rgba(255,255,255,0.5);">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none" style="color: rgba(255,255,255,0.3);">
                            <i class="fa-solid fa-user text-sm"></i>
                        </span>
                        <input type="text" name="username" required
                            placeholder="Masukkan username Anda"
                            class="input-field w-full pl-11 pr-4 py-3 rounded-xl text-sm font-medium">
                    </div>
                </div>

                <!-- Password -->
                <div class="space-y-1.5">
                    <label class="block text-xs font-semibold tracking-wider uppercase" style="color: rgba(255,255,255,0.5);">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none" style="color: rgba(255,255,255,0.3);">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </span>
                        <input type="password" name="password" id="pwdField" required
                            placeholder="Masukkan password Anda"
                            class="input-field w-full pl-11 pr-11 py-3 rounded-xl text-sm font-medium">
                        <button type="button" onclick="togglePwd()" class="absolute inset-y-0 right-0 pr-4 flex items-center"
                                style="color: rgba(255,255,255,0.3);">
                            <i class="fa-solid fa-eye text-sm" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit -->
                <button type="submit" id="btnSubmit"
                    class="btn-login w-full py-3.5 rounded-xl text-white font-bold text-sm flex items-center justify-center space-x-2">
                    <span>Masuk ke Sistem</span>
                    <i class="fa-solid fa-arrow-right text-sm"></i>
                </button>
            </form>

            <!-- Role Guide -->
            <div class="rounded-2xl p-4 space-y-3" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07);">
                <p class="text-xs font-semibold uppercase tracking-wider" style="color: rgba(255,255,255,0.35);">Akun Demo</p>
                <div class="grid grid-cols-1 gap-2 text-xs">
                    <?php
                    $demos = [
                        ['icon' => 'fa-shield-halved', 'color' => '#818cf8', 'bg' => 'rgba(99,102,241,0.15)', 'role' => 'Administrator', 'user' => 'admin', 'pass' => 'admin123'],
                        ['icon' => 'fa-user-tie',      'color' => '#34d399', 'bg' => 'rgba(16,185,129,0.15)', 'role' => 'Kepala Madrasah', 'user' => 'kamad', 'pass' => 'kamad123'],
                        ['icon' => 'fa-user-check',   'color' => '#fbbf24', 'bg' => 'rgba(245,158,11,0.15)', 'role' => 'Penilai (misal KS)', 'user' => 'penilai_ks', 'pass' => 'penilai123'],
                    ];
                    foreach ($demos as $d):
                    ?>
                    <button type="button" onclick="fillDemo('<?= $d['user'] ?>', '<?= $d['pass'] ?>')"
                        class="flex items-center space-x-3 px-3 py-2 rounded-xl text-left transition hover:opacity-80"
                        style="background: <?= $d['bg'] ?>; border: 1px solid <?= $d['bg'] ?>;">
                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0" style="background: <?= $d['bg'] ?>;">
                            <i class="fa-solid <?= $d['icon'] ?> text-xs" style="color: <?= $d['color'] ?>;"></i>
                        </div>
                        <div>
                            <div class="font-semibold" style="color: rgba(255,255,255,0.85);"><?= $d['role'] ?></div>
                            <div style="color: rgba(255,255,255,0.4);"><?= $d['user'] ?> / <?= $d['pass'] ?></div>
                        </div>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    function togglePwd() {
        const f = document.getElementById('pwdField');
        const i = document.getElementById('eyeIcon');
        if (f.type === 'password') {
            f.type = 'text';
            i.className = 'fa-solid fa-eye-slash text-sm';
        } else {
            f.type = 'password';
            i.className = 'fa-solid fa-eye text-sm';
        }
    }
    function fillDemo(u, p) {
        document.querySelector('input[name="username"]').value = u;
        document.querySelector('input[name="password"]').value = p;
    }
    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-2"></i> Memverifikasi...';

        const formData = new FormData(this);
        try {
            const response = await fetch('<?= base_url('login') ?>', { method: 'POST', body: formData });
            const res = await response.json();
            if (res.success) {
                btn.innerHTML = '<i class="fa-solid fa-check mr-2"></i> Berhasil!';
                btn.style.background = 'linear-gradient(135deg,#059669,#047857)';
                setTimeout(() => { window.location.href = res.redirect; }, 800);
            } else {
                Swal.fire({
                    icon: 'error', title: 'Login Gagal',
                    text: res.message,
                    background: '#1e1b33',
                    color: '#e2e8f0',
                    confirmButtonColor: '#6366f1',
                    iconColor: '#f87171',
                });
                btn.disabled = false;
                btn.innerHTML = '<span>Masuk ke Sistem</span><i class="fa-solid fa-arrow-right ml-2 text-sm"></i>';
            }
        } catch(err) {
            Swal.fire({ icon: 'error', title: 'Kesalahan Jaringan', text: 'Tidak dapat terhubung ke server.', background: '#1e1b33', color: '#e2e8f0', confirmButtonColor: '#6366f1' });
            btn.disabled = false;
            btn.innerHTML = '<span>Masuk ke Sistem</span><i class="fa-solid fa-arrow-right ml-2 text-sm"></i>';
        }
    });
    </script>
</body>
</html>

<?php
$bgImage = get_setting('login_bg_image', 'img/gedung-kemenag.jpg');
$loginTitle = get_setting('login_title', 'Aplikasi Penilaian Kinerja Kepala Madrasah');
$loginSubtitle = get_setting('login_subtitle', 'Kementerian Agama Kabupaten Jepara');
$loginDesc = get_setting('login_desc', 'Sistem informasi untuk mendukung penilaian kinerja kepala madrasah secara transparan, akuntabel, dan terintegrasi pada lingkungan Kementerian Agama Kabupaten Jepara.');
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>PKKM — <?= e($loginTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" media="print" onload="this.media='all'">
    <link rel="preload" as="style" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" media="print" onload="this.media='all'">
    <link rel="stylesheet" href="<?= asset_url('css/app.css') ?>">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Fullscreen background gedung */
        .bg-gedung {
            background-image: url('<?= base_url($bgImage) ?>');
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
        }

        /* Islamic Geometric Girih Star Overlay on Right Side */
        .bg-islamic-girih {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='80' height='80' viewBox='0 0 80 80'%3E%3Cg fill='%231b5e3d' fill-opacity='0.08'%3E%3Cpath d='M40 0l6.18 12.52 13.82-1.3-3.66 13.4 10.66 8.94-11.4 7.96 4.64 13.1-13.38-3.74L40 60.5l-6.86-9.62-13.38 3.74 4.64-13.1-11.4-7.96 10.66-8.94-3.66-13.4 13.82 1.3z'/%3E%3Cpath d='M40 20l3.09 6.26 6.91-.65-1.83 6.7 5.33 4.47-5.7 3.98 2.32 6.55-6.69-1.87L40 50.25l-3.43-4.81-6.69 1.87 2.32-6.55-5.7-3.98 5.33-4.47-1.83-6.7 6.91.65z' fill='%231b5e3d' fill-opacity='0.06'/%3E%3Ccircle cx='40' cy='40' r='10' stroke='%231b5e3d' stroke-opacity='0.08' stroke-width='1.5' fill='none'/%3E%3C/g%3E%3C/svg%3E");
            background-repeat: repeat;
        }

        /* Input styling */
        .input-login {
            border: 1px solid #cbd5e1;
            transition: all 0.2s ease;
            background: #ffffff;
        }
        .input-login:focus {
            border-color: #1e6347;
            box-shadow: 0 0 0 3px rgba(30, 99, 71, 0.15);
            outline: none;
        }

        /* Button styling matching the image */
        .btn-kemenag {
            background-color: #1e6347;
            transition: all 0.2s ease;
        }
        .btn-kemenag:hover {
            background-color: #164f38;
            box-shadow: 0 6px 16px rgba(30, 99, 71, 0.35);
        }

        /* Glass / Floating Card shadow */
        .floating-card-shadow {
            box-shadow: 0 24px 60px -12px rgba(0, 0, 0, 0.32), 0 0 1px 1px rgba(255, 255, 255, 0.7) inset;
        }

        /* Subtle Islamic Ornament Pattern for Wave Banner */
        .islamic-wave-pattern {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='60' height='60' viewBox='0 0 60 60'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Cpath d='M30 0l4.63 9.39 10.37-.97-2.74 10.05 8 6.7-8.55 5.97 3.48 9.82-10.04-2.8L30 45.38l-5.15-7.22-10.04 2.8 3.48-9.82-8.55-5.97 8-6.7-2.74-10.05 10.37.97z'/%3E%3C/g%3E%3C/svg%3E");
            background-repeat: repeat;
        }
    </style>
</head>
<body class="h-full w-full bg-slate-900 select-none overflow-hidden relative">

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 1. FULLSCREEN BACKGROUND (GEDUNG + ISLAMIC OVERLAY)            -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="absolute inset-0 bg-gedung"></div>

    <!-- Soft White-to-Transparent Vignette on the right side for floating card -->
    <div class="absolute inset-0 pointer-events-none bg-gradient-to-r from-black/20 via-white/10 to-white/70"></div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 2. TOP-LEFT BADGE: Semanak, Kemenag Berdampak, Pusaka          -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="absolute top-5 left-5 sm:top-6 sm:left-8 z-20">
        <div class="bg-white/95 backdrop-blur-md rounded-2xl px-3.5 py-2 sm:px-4 sm:py-2.5 shadow-lg border border-slate-200/80 flex items-center space-x-3 sm:space-x-4">
            <!-- Semanak Jepara -->
            <div class="flex items-center space-x-2 border-r border-slate-200 pr-3">
                <img src="<?= base_url('img/semanak-ijo.png') ?>" alt="Semanak Jepara" class="h-5 sm:h-6 w-auto object-contain">
            </div>
            <!-- Kemenag Berdampak Tagline -->
            <div class="flex items-center space-x-1.5 border-r border-slate-200 pr-3">
                <div class="w-5 h-5 rounded-md bg-emerald-700 text-white flex items-center justify-center text-[9px] font-black">
                    <i class="fa-solid fa-shapes"></i>
                </div>
                <div class="leading-none">
                    <div class="text-[10px] font-extrabold text-emerald-800">Kemenag</div>
                    <div class="text-[8px] font-bold text-amber-600 tracking-tight">Berdampak</div>
                </div>
            </div>
            <!-- Pusaka SuperApps -->
            <div class="flex items-center space-x-1">
                <div class="w-5 h-5 rounded-full bg-cyan-600/10 text-cyan-700 flex items-center justify-center text-[10px]">
                    <i class="fa-solid fa-fire-flame-curved"></i>
                </div>
                <div class="leading-none hidden sm:block">
                    <div class="text-[10px] font-bold text-slate-700">pusaka</div>
                    <div class="text-[7px] text-slate-400">Kemenag SuperApps</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 3. BOTTOM-LEFT: Organic Fluid Wave Banner (Matching Mockup)     -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="absolute inset-x-0 bottom-0 z-20 pointer-events-none select-none">
        <svg class="w-full h-auto min-h-[220px] max-h-[340px] pointer-events-none block" 
             viewBox="0 0 1440 380" 
             preserveAspectRatio="none" 
             fill="none" 
             xmlns="http://www.w3.org/2000/svg">
            <defs>
                <!-- Green Dark Gradient -->
                <linearGradient id="waveGreen" x1="0%" y1="0%" x2="100%" y2="100%">
                    <stop offset="0%" stop-color="#18613f" />
                    <stop offset="45%" stop-color="#155536" />
                    <stop offset="100%" stop-color="#0f3d27" />
                </linearGradient>
                <!-- Golden Gradient Line -->
                <linearGradient id="goldRibbon" x1="0%" y1="0%" x2="100%" y2="0%">
                    <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.9" />
                    <stop offset="30%" stop-color="#fbbf24" stop-opacity="1" />
                    <stop offset="65%" stop-color="#fef08a" stop-opacity="0.95" />
                    <stop offset="100%" stop-color="#d97706" stop-opacity="0.8" />
                </linearGradient>
                <!-- Subtle Islamic Star Pattern fill for wave -->
                <pattern id="islamicPatternWave" width="50" height="50" patternUnits="userSpaceOnUse">
                    <path d="M25 0l3.86 7.82 8.64-.81-2.28 8.37 6.66 5.58-7.12 4.97 2.9 8.18-8.36-2.33L25 37.8l-4.29-6.02-8.36 2.33 2.9-8.18-7.12-4.97 6.66-5.58-2.28-8.37 8.64.81z" fill="#ffffff" fill-opacity="0.04" />
                </pattern>
            </defs>

            <!-- Shadow underneath wave -->
            <path d="M0,130 C320,60 620,170 940,240 C1140,285 1320,335 1440,360 L1440,380 L0,380 Z" fill="#000000" fill-opacity="0.25" />

            <!-- Main Green Fluid Wave Body -->
            <path d="M0,120 C320,50 620,160 940,230 C1140,275 1320,325 1440,350 L1440,380 L0,380 Z" fill="url(#waveGreen)" />
            <path d="M0,120 C320,50 620,160 940,230 C1140,275 1320,325 1440,350 L1440,380 L0,380 Z" fill="url(#islamicPatternWave)" />

            <!-- Golden Glowing Accent Line On Top Edge of the Curve -->
            <path d="M0,120 C320,50 620,160 940,230 C1140,275 1320,325 1440,350" 
                  stroke="url(#goldRibbon)" 
                  stroke-width="5" 
                  stroke-linecap="round" 
                  fill="none" />
        </svg>

        <!-- Typography Content Overlay Placed Beautifully inside Wave -->
        <div class="absolute bottom-6 left-6 sm:bottom-10 sm:left-10 lg:bottom-12 lg:left-14 max-w-xl pointer-events-auto space-y-2.5">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white leading-tight tracking-tight drop-shadow-md">
                <?= nl2br(e($loginTitle)) ?>
            </h1>
            <p class="text-xs sm:text-[13px] text-emerald-100/90 leading-relaxed font-normal max-w-lg drop-shadow-sm">
                <?= e($loginDesc) ?>
            </p>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════ -->
    <!-- 4. FLOATING LOGIN CARD WITH ISLAMIC PATTERN (RIGHT SIDE)       -->
    <!-- ═══════════════════════════════════════════════════════════════ -->
    <div class="absolute inset-0 flex items-center justify-center lg:justify-end lg:pr-12 xl:pr-16 z-30 pointer-events-none p-4">
        
        <!-- The Floating Card: Pure White with Islamic Pattern Texture -->
        <div class="w-full max-w-[410px] bg-white bg-islamic-girih rounded-[32px] sm:rounded-[36px] floating-card-shadow p-6 sm:p-8 border border-white/80 pointer-events-auto relative overflow-hidden backdrop-blur-sm">
            
            <!-- Logo Kemenag Ikhlas Beramal -->
            <div class="text-center space-y-2">
                <div class="flex justify-center">
                    <img src="<?= base_url('img/logo-kemenag.svg') ?>" 
                         alt="Logo Kementerian Agama" 
                         class="w-16 h-16 sm:w-20 sm:h-20 object-contain drop-shadow-sm">
                </div>

                <div>
                    <h2 class="text-base sm:text-lg font-bold text-slate-800 tracking-tight leading-snug">
                        <?= e($loginTitle) ?>
                    </h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">
                        <?= e($loginSubtitle) ?>
                    </p>
                </div>

                <!-- Golden Diamond Ornament Decor -->
                <div class="flex items-center justify-center pt-0.5">
                    <span class="w-2 h-2 rotate-45 bg-amber-500 rounded-[1px]"></span>
                </div>
            </div>

            <?php if (!empty($sessionExpired)): ?>
            <!-- Alert Session Expired / Idle 2 Jam -->
            <div class="mt-3.5 p-3 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-2.5 text-amber-900 text-xs">
                <i class="fa-solid fa-clock-rotate-left text-amber-600 mt-0.5 shrink-0"></i>
                <div class="leading-relaxed">
                    Sesi Anda Berakhir. Silakan login kembali untuk melanjutkan.
                </div>
            </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form id="loginForm" class="space-y-3.5 mt-4" autocomplete="off">
                <?= csrf_field() ?>

                <!-- Field NIP/NIK/Username -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-700 tracking-tight">
                        NIP/NIK
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-regular fa-user text-xs"></i>
                        </span>
                        <input type="text" 
                               name="username" 
                               required 
                               placeholder="Masukkan NIP/NIK Anda" 
                               class="input-login w-full pl-9 pr-3 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400">
                    </div>
                </div>

                <!-- Field Password -->
                <div class="space-y-1">
                    <label class="block text-[11px] font-bold text-slate-700 tracking-tight">
                        Password
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </span>
                        <input type="password" 
                               name="password" 
                               id="pwdField" 
                               required 
                               placeholder="Masukkan Password" 
                               class="input-login w-full pl-9 pr-9 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400">
                        <button type="button" 
                                onclick="togglePwd()" 
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 transition">
                            <i class="fa-regular fa-eye text-xs" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between text-[11px] pt-0.5">
                    <label class="inline-flex items-center space-x-1.5 text-slate-600 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-3.5 h-3.5 rounded border-slate-300 text-emerald-700 focus:ring-emerald-600">
                        <span>Ingat saya</span>
                    </label>
                    <a href="javascript:void(0)" 
                       onclick="Swal.fire('Lupa Password', 'Silakan hubungi administrator Seksi Pendidikan Madrasah Kemenag Kabupaten Jepara untuk reset password akun Anda.', 'info')"
                       class="text-slate-600 hover:text-emerald-700 font-medium transition">
                        Lupa password?
                    </a>
                </div>

                <!-- Login Button with Arrow -->
                <div class="pt-1.5">
                    <button type="submit" 
                            id="btnSubmit" 
                            class="btn-kemenag w-full py-2.5 sm:py-3 px-4 rounded-xl text-white font-bold text-xs sm:text-sm flex items-center justify-between transition shadow-md">
                        <span class="w-4"></span>
                        <span class="text-center font-bold tracking-wide">Login</span>
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </button>
                </div>
            </form>

            <!-- Info Box Bawah (Green Soft Badge) -->
            <div class="rounded-xl p-3 bg-emerald-50/80 border border-emerald-200/60 flex items-start space-x-2.5 mt-3.5">
                <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-solid fa-shield-check text-[10px]"></i>
                </div>
                <p class="text-[11px] text-emerald-900 leading-snug font-medium">
                    Gunakan NIP dan password sesuai ketentuan yang telah diberikan oleh administrator.
                </p>
            </div>

        </div>
    </div>

    <!-- Script Handle Login & Toggle Password -->
    <script>
    function togglePwd() {
        const f = document.getElementById('pwdField');
        const i = document.getElementById('eyeIcon');
        if (f.type === 'password') {
            f.type = 'text';
            i.className = 'fa-regular fa-eye-slash text-xs';
        } else {
            f.type = 'password';
            i.className = 'fa-regular fa-eye text-xs';
        }
    }

    document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const btn = document.getElementById('btnSubmit');
        const oldContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="w-full text-center"><i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Memverifikasi...</span>';

        const formData = new FormData(this);
        try {
            const response = await fetch('<?= base_url('login') ?>', { method: 'POST', body: formData });
            const res = await response.json();
            if (res.success) {
                btn.innerHTML = '<span class="w-full text-center"><i class="fa-solid fa-check mr-1.5"></i> Berhasil! Mengalihkan...</span>';
                btn.style.backgroundColor = '#15803d';
                setTimeout(() => { window.location.href = res.redirect; }, 600);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: res.message,
                    confirmButtonColor: '#1e6347',
                });
                btn.disabled = false;
                btn.innerHTML = oldContent;
            }
        } catch(err) {
            Swal.fire({
                icon: 'error',
                title: 'Kesalahan Jaringan',
                text: 'Tidak dapat terhubung ke server.',
                confirmButtonColor: '#1e6347'
            });
            btn.disabled = false;
            btn.innerHTML = oldContent;
        }
    });
    </script>
</body>
</html>

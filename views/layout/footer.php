    </main>
</div>

<!-- ===== FOOTER ===== -->
<footer class="lg:ml-64 bg-white border-t border-slate-100 py-3 px-6 text-center text-[11px] text-slate-400">
    © <?= date('Y') ?> Sistem Informasi PKKM · Bidang Pendidikan Madrasah · Kementerian Agama RI
</footer>

<script>
// Sheet akun mobile
window.bukaAkun = function () {
    const sheet = document.getElementById('akunSheet');
    if (sheet) sheet.classList.remove('hidden');
};
window.tutupAkun = function () {
    const sheet = document.getElementById('akunSheet');
    if (sheet) sheet.classList.add('hidden');
};

// Tombol kembali pada app bar mobile
window.kembaliHalaman = function () {
    const btn = document.getElementById('appBack');
    const fallback = btn ? btn.dataset.fallback : null;
    if (window.history.length > 1 && document.referrer) {
        window.history.back();
    } else if (fallback) {
        window.location.href = fallback;
    }
};

// Konfirmasi sebelum logout
document.addEventListener('click', function (e) {
    const link = e.target.closest('a[data-logout]');
    if (!link) return;
    e.preventDefault();

    const keluar = function () { window.location.href = link.href; };

    if (typeof Swal === 'undefined') {
        if (window.confirm('Keluar dari aplikasi?')) keluar();
        return;
    }

    Swal.fire({
        title: 'Keluar dari aplikasi?',
        text: 'Anda akan mengakhiri sesi PKKM ini.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Keluar',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b'
    }).then(function (hasil) {
        if (hasil.isConfirmed) keluar();
    });
});

// Notifikasi admin & kepala madrasah
(function () {
    const panel = document.getElementById('notifPanel');
    if (!panel) return;

    const badges = document.querySelectorAll('[data-notif-badge]');
    const list = document.getElementById('notifList');
    const url = '<?= base_url('notifikasi') ?>';

    function esc(value) {
        const d = document.createElement('div');
        d.textContent = value == null ? '' : String(value);
        return d.innerHTML;
    }

    function render(data) {
        const unread = data.unread || 0;
        badges.forEach(function (badge) {
            badge.textContent = unread > 99 ? '99+' : unread;
            badge.classList.toggle('hidden', unread === 0);
        });

        const tabDot = document.getElementById('notifTabDot');
        if (tabDot) tabDot.classList.toggle('hidden', unread === 0);

        if (!data.items || !data.items.length) {
            list.innerHTML = '<div class="px-4 py-6 text-center text-xs text-slate-400">Belum ada notifikasi.</div>';
            return;
        }

        list.innerHTML = data.items.map(function (it) {
            const href = (it.link || '#').replace(/"/g, '%22');
            const dot = it.is_read ? 'bg-slate-300' : 'bg-rose-500';
            return '<a href="' + href + '" class="block px-4 py-3 hover:bg-slate-50">' +
                '<div class="flex items-start gap-2">' +
                '<span class="mt-1 w-1.5 h-1.5 rounded-full ' + dot + ' shrink-0"></span>' +
                '<div class="min-w-0">' +
                '<div class="text-xs font-semibold text-slate-700">' + esc(it.judul) + '</div>' +
                '<div class="text-[11px] text-slate-500 leading-snug">' + esc(it.pesan) + '</div>' +
                '<div class="text-[10px] text-slate-400 mt-0.5">' + esc(it.waktu) + '</div>' +
                '</div></div></a>';
        }).join('');
    }

    function muat() {
        fetch(url, { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
            .then(function (res) { return res.ok ? res.json() : null; })
            .then(function (data) { if (data && data.success) render(data); })
            .catch(function () {});
    }

    window.toggleNotif = function () {
        const hidden = panel.classList.contains('hidden');
        panel.classList.toggle('hidden', !hidden);
        if (hidden) muat();
    };

    window.bacaSemuaNotif = function () {
        fetch(url + '/read', { method: 'POST', headers: { 'Accept': 'application/json' } })
            .then(function () { muat(); })
            .catch(function () {});
    };

    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-notif-toggle]')) return;
        if (!panel.contains(e.target)) panel.classList.add('hidden');
    });

    muat();
    window.setInterval(muat, 60000);
})();

// Auto redirect ke logout jika pengguna idle lebih dari 2 jam (7200 detik)
(function () {
    const IDLE_LIMIT_MS = 2 * 60 * 60 * 1000; // 2 jam = 7.200.000 ms
    const STORAGE_KEY = 'pkkm_last_active';
    const logoutUrl = '<?= base_url('logout?reason=timeout') ?>';

    function setAktif() {
        try { localStorage.setItem(STORAGE_KEY, String(Date.now())); } catch (e) {}
    }

    function cekIdle() {
        let last = 0;
        try { last = parseInt(localStorage.getItem(STORAGE_KEY) || '0', 10); } catch (e) {}
        if (!last) {
            setAktif();
            return;
        }
        if (Date.now() - last >= IDLE_LIMIT_MS) {
            try { localStorage.removeItem(STORAGE_KEY); } catch (e) {}
            window.location.href = logoutUrl;
        }
    }

    setAktif();
    let throttleTimer = null;
    function onAktivitas() {
        if (!throttleTimer) {
            setAktif();
            throttleTimer = setTimeout(function () { throttleTimer = null; }, 3000);
        }
    }
    ['click', 'keydown', 'scroll', 'touchstart'].forEach(function (ev) {
        window.addEventListener(ev, onAktivitas, { passive: true });
    });

    window.setInterval(cekIdle, 30000); // periksa tiap 30 detik
    document.addEventListener('visibilitychange', function () {
        if (!document.hidden) cekIdle();
    });
})();
</script>
</body>
</html>

    </main>
</div>

<!-- ===== FOOTER ===== -->
<footer class="lg:ml-64 bg-white border-t border-slate-100 py-3 px-6 text-center text-[11px] text-slate-400">
    © <?= date('Y') ?> Sistem Informasi PKKM · Bidang Pendidikan Madrasah · Kementerian Agama RI
</footer>

<script>
function toggleSidebar() {
    const sb = document.getElementById('sidebar');
    const ov = document.getElementById('sidebarOverlay');
    const hidden = sb.classList.contains('-translate-x-full');
    sb.classList.toggle('-translate-x-full', !hidden);
    ov.classList.toggle('hidden', !hidden);
}

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
    const wrap = document.getElementById('notifWrap');
    if (!wrap) return;

    const badge = document.getElementById('notifBadge');
    const panel = document.getElementById('notifPanel');
    const list = document.getElementById('notifList');
    const url = '<?= base_url('notifikasi') ?>';

    function esc(value) {
        const d = document.createElement('div');
        d.textContent = value == null ? '' : String(value);
        return d.innerHTML;
    }

    function render(data) {
        const unread = data.unread || 0;
        badge.textContent = unread > 99 ? '99+' : unread;
        badge.classList.toggle('hidden', unread === 0);

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
        if (!wrap.contains(e.target)) panel.classList.add('hidden');
    });

    muat();
    window.setInterval(muat, 60000);
})();
</script>
</body>
</html>

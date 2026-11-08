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

// Animasi keluar saat pindah halaman
(function () {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const leaveDelay = reduceMotion ? 190 : 230;
    let leaving = false;

    // Kembalikan state saat halaman dipulihkan dari bfcache
    window.addEventListener('pageshow', function (e) {
        leaving = false;
        document.body.classList.remove('page-leaving');
        if (e.persisted) {
            const main = document.querySelector('.glass-page');
            if (main) {
                main.style.animation = 'none';
                void main.offsetWidth;
                main.style.animation = '';
            }
        }
    });

    document.addEventListener('click', function (e) {
        if (leaving || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        const link = e.target.closest('a[href]');
        if (!link || link.closest('[data-no-transition]')) return;
        if (link.hasAttribute('download')) return;
        const target = link.getAttribute('target');
        if (target && target !== '_self') return;

        const href = link.getAttribute('href');
        if (!href || href.charAt(0) === '#' || /^(javascript|mailto|tel):/i.test(href)) return;

        let url;
        try { url = new URL(link.href, window.location.href); } catch (err) { return; }
        if (url.origin !== window.location.origin) return;
        if (url.pathname === window.location.pathname && url.search === window.location.search) return;

        e.preventDefault();
        leaving = true;
        document.body.classList.add('page-leaving');
        window.setTimeout(function () { window.location.href = link.href; }, leaveDelay);
    });
})();
</script>
</body>
</html>

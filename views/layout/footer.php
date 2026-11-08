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
</script>
</body>
</html>

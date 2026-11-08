<?php view('layout.header', ['title' => $title]); ?>

<div class="space-y-6">

<!-- ── Header ─────────────────────────────────────────────── -->
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
    <div>
        <h1 class="text-xl font-bold text-slate-800 tracking-tight">Audit Log Sistem</h1>
        <p class="text-sm text-slate-500 mt-0.5">Catatan riwayat seluruh aktivitas pengguna dan perubahan data dalam sistem</p>
    </div>
    <div class="flex items-center space-x-2">
        <span class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 text-slate-600">
            <i class="fa-solid fa-list-check mr-1.5 text-slate-400"></i>
            Total: <?= count($logs) ?> Aktivitas Terakhir
        </span>
    </div>
</div>

<!-- ── Filter & Search Bar ─────────────────────────────────── -->
<div class="card p-4">
    <form method="GET" class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex flex-wrap items-center gap-2.5 flex-1">
            <!-- Search -->
            <div class="relative flex-1 min-w-[200px] max-w-md">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                <input type="text" name="q" value="<?= e($searchQuery) ?>" placeholder="Cari nama pengguna, aksi, atau detail..."
                    class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-brand-500 outline-none">
            </div>

            <!-- Kategori -->
            <select name="kategori" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-700 focus:ring-2 focus:ring-brand-500 outline-none">
                <option value="">-- Semua Kategori --</option>
                <?php foreach ($kategoris as $kat): ?>
                <option value="<?= e($kat) ?>" <?= $selectedKategori === $kat ? 'selected' : '' ?>>
                    Kategori: <?= ucfirst(e($kat)) ?>
                </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold text-white shadow" style="background:linear-gradient(135deg,#1e6347,#17503a);">
                Filter
            </button>

            <?php if ($searchQuery !== '' || $selectedKategori !== ''): ?>
            <a href="<?= base_url('admin/audit-log') ?>" class="px-3 py-2 rounded-xl text-xs font-semibold text-slate-500 hover:text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                Reset
            </a>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- ── Table of Audit Logs ─────────────────────────────────── -->
<div class="card overflow-hidden">
<div class="overflow-x-auto">
<table class="table-stack w-full text-left text-xs">
    <thead class="bg-slate-50 border-b border-slate-100 text-slate-500 uppercase text-[10px] font-semibold tracking-wider">
        <tr>
            <th class="px-6 py-3.5 w-40">Waktu</th>
            <th class="px-6 py-3.5 w-52">Pengguna</th>
            <th class="px-6 py-3.5 w-36">Aksi</th>
            <th class="px-6 py-3.5 w-28">Kategori</th>
            <th class="px-6 py-3.5">Detail Aktivitas</th>
            <th class="px-6 py-3.5 text-right w-36">IP Address</th>
        </tr>
    </thead>
    <tbody class="divide-y divide-slate-100">
        <?php if (!empty($logs)): ?>
        <?php foreach ($logs as $l): ?>
        <?php
            // Action color badge
            $act = strtoupper($l['action']);
            if (str_contains($act, 'LOGIN') || str_contains($act, 'LOGOUT')) {
                $actBadge = 'bg-blue-50 text-blue-700 border-blue-200';
            } elseif (str_contains($act, 'TAMBAH') || str_contains($act, 'UPLOAD') || str_contains($act, 'SUBMIT')) {
                $actBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            } elseif (str_contains($act, 'UPDATE') || str_contains($act, 'UNLOCK')) {
                $actBadge = 'bg-amber-50 text-amber-700 border-amber-200';
            } elseif (str_contains($act, 'DELETE') || str_contains($act, 'HAPUS') || str_contains($act, 'RESET')) {
                $actBadge = 'bg-rose-50 text-rose-700 border-rose-200';
            } else {
                $actBadge = 'bg-slate-100 text-slate-700 border-slate-200';
            }
        ?>
        <tr class="hover:bg-slate-50/70 transition">
            <td class="px-6 py-4 text-slate-500 whitespace-nowrap" data-label="Waktu">
                <div class="font-semibold text-slate-700"><?= date('d/m/Y', strtotime($l['created_at'])) ?></div>
                <div class="text-[11px] text-slate-400"><?= date('H:i:s', strtotime($l['created_at'])) ?> WIB</div>
            </td>
            <td class="px-6 py-4" data-label="Pengguna">
                <div class="flex items-center space-x-2.5">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs text-white shrink-0" style="background:linear-gradient(135deg,#1e6347,#17503a);">
                        <?= strtoupper(substr($l['nama_lengkap'] ?: ($l['username'] ?: 'S'), 0, 1)) ?>
                    </div>
                    <div>
                        <div class="font-semibold text-slate-800"><?= e($l['nama_lengkap'] ?: ($l['username'] ?: 'Sistem')) ?></div>
                        <div class="text-[11px] text-slate-400">@<?= e($l['username'] ?: 'system') ?></div>
                    </div>
                </div>
            </td>
            <td class="px-6 py-4" data-label="Aksi">
                <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border <?= $actBadge ?>">
                    <?= e($l['action']) ?>
                </span>
            </td>
            <td class="px-6 py-4" data-label="Kategori">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                    <?= e($l['kategori']) ?>
                </span>
            </td>
            <td class="px-6 py-4 text-slate-700 leading-relaxed max-w-md" data-label="Detail Aktivitas">
                <?= e($l['detail'] ?: '—') ?>
            </td>
            <td class="px-6 py-4 text-right text-slate-500 font-mono text-[11px] whitespace-nowrap" data-label="IP Address">
                <?= e($l['ip_address'] ?: '-') ?>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php else: ?>
        <tr>
            <td colspan="6" class="px-6 py-12 text-center text-slate-400" data-label="">
                <i class="fa-solid fa-file-shield text-3xl mb-2 block"></i>
                Tidak ada data log aktivitas yang cocok dengan pencarian.
            </td>
        </tr>
        <?php endif; ?>
    </tbody>
</table>
</div>
</div>

</div>

<?php view('layout.footer'); ?>

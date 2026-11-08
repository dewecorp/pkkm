<?php
/**
 * Grid menu ikon khusus mobile (< 1024px).
 * $nav: daftar menu role aktif.
 */
$nav = $nav ?? [];
$uri = $_SERVER['REQUEST_URI'];
$isActive = fn($path) => strpos($uri, $path) !== false;
?>
<div class="lg:hidden scroll-mt-24" id="menuGrid">
    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2 px-1">Menu</div>
    <div class="grid grid-cols-4 gap-2.5">
        <?php foreach ($nav as $item): ?>
        <?php $active = $isActive($item['key']); ?>
        <a href="<?= base_url($item['url']) ?>"
           class="mobile-tab flex flex-col items-center justify-center gap-1.5 px-1.5 py-3 rounded-2xl border transition
                  <?= $active ? 'bg-brand-50 border-brand-200' : 'bg-white border-slate-200/80' ?>">
            <span class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-base shadow-sm"
                  style="background:linear-gradient(135deg,<?= $active ? '#2b7d55,#17503a' : '#4c9d75,#1e6347' ?>);">
                <i class="fa-solid <?= $item['icon'] ?>"></i>
            </span>
            <span class="text-[10px] font-semibold text-center leading-tight <?= $active ? 'text-brand-700' : 'text-slate-600' ?>">
                <?= e($item['label']) ?>
            </span>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<?php
/**
 * Kartu statistik ringkas (dipakai semua level dashboard).
 * $items: [label, value, icon, color, grad, badge?, small?]
 */
$items = $items ?? [];
?>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    <?php foreach ($items as $k): ?>
    <div class="group relative rounded-xl bg-white/85 border border-white/70 p-4 sm:p-5
                shadow-[0_8px_30px_-18px_rgba(11,42,30,0.35)] transition-all duration-200
                hover:-translate-y-0.5 hover:shadow-md hover:border-white">

        <!-- Label -->
        <div class="text-[10px] sm:text-xs font-medium text-slate-500 leading-tight pr-10 sm:pr-11 whitespace-nowrap">
            <?= e($k['label']) ?>
        </div>

        <!-- Icon -->
        <div class="absolute top-3 right-3 sm:top-4 sm:right-4 w-8 h-8 sm:w-9 sm:h-9 rounded-lg flex items-center justify-center text-white"
             style="background:<?= $k['grad'] ?>;box-shadow:0 4px 10px -4px <?= $k['color'] ?>99;">
            <i class="fa-solid <?= $k['icon'] ?> text-xs sm:text-sm"></i>
        </div>

        <!-- Value + badge -->
        <div class="mt-1.5 flex items-center gap-1.5 flex-wrap">
            <span class="<?= !empty($k['small']) ? 'text-[16px] sm:text-[18px]' : 'text-[22px] sm:text-[24px]' ?> font-extrabold text-slate-900 tracking-tight leading-none tabular-nums">
                <?= $k['value'] ?>
            </span>
            <?php if (!empty($k['badge'])): ?>
            <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold"
                  style="background:<?= $k['color'] ?>14;color:<?= $k['color'] ?>;">
                <?= e($k['badge']) ?>
            </span>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

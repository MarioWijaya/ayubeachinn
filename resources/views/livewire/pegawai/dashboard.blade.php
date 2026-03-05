<?php
  $statusKamarOptions = ['tersedia', 'terisi', 'perbaikan'];

  $statusKamarLabel = fn($s) => match ($s) {
    'tersedia' => 'Tersedia',
    'terisi' => 'Terisi',
    'perbaikan' => 'Perbaikan',
    default => $s,
  };

  $badgeKamarClass = fn($s) => match ($s) {
    'tersedia' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
    'terisi' => 'bg-[#FFB22C]/15 text-[#9A5B00] border-[#FFB22C]/40',
    'perbaikan' => 'bg-amber-50 text-amber-800 border-amber-200',
    default => 'bg-slate-50 text-slate-700 border-slate-200',
  };

  $displayTipe = fn(?string $tipe) => str_ireplace('standart', 'standard', (string) $tipe);

  $iconByType = function (?string $tipe): string {
    $key = strtolower(trim((string) $tipe));
    return match (true) {
      str_contains($key, 'deluxe') => 'crown',
      str_contains($key, 'suite') => 'gem',
      str_contains($key, 'family') => 'users',
      str_contains($key, 'superior') => 'bed-double',
      str_contains($key, 'standard') || str_contains($key, 'standart') => 'fan',
      default => 'bed',
    };
  };
  $svgIcon = function (string $name, string $class = 'h-4 w-4'): string {
    $attrs = 'xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" class="'.e($class).'"';

    return match ($name) {
      'crown' => '<svg '.$attrs.'><path d="m3 8 4 4 5-7 5 7 4-4v10H3z"/><path d="M3 18h18"/></svg>',
      'gem' => '<svg '.$attrs.'><path d="m6 3 6 8 6-8"/><path d="m3 9 9 12 9-12Z"/><path d="M12 21V11"/></svg>',
      'users' => '<svg '.$attrs.'><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6"/><path d="M23 11h-6"/></svg>',
      'fan' => '<svg '.$attrs.'><circle cx="12" cy="12" r="2"/><path d="M12 4c2 0 3 1.5 2.5 3.1L13 11"/><path d="M20 12c0 2-1.5 3-3.1 2.5L13 13"/><path d="M12 20c-2 0-3-1.5-2.5-3.1L11 13"/><path d="M4 12c0-2 1.5-3 3.1-2.5L11 11"/></svg>',
      'bed-double' => '<svg '.$attrs.'><path d="M2 10h20"/><path d="M2 18h20"/><path d="M4 10v8"/><path d="M20 10v8"/><rect x="6" y="12" width="5" height="4" rx="1"/><rect x="13" y="12" width="5" height="4" rx="1"/></svg>',
      'bed' => '<svg '.$attrs.'><path d="M2 11h20"/><path d="M2 18h20"/><path d="M4 11v7"/><path d="M20 11v7"/><rect x="5" y="13" width="14" height="3" rx="1"/></svg>',
      'search' => '<svg '.$attrs.'><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>',
      'rotate-ccw' => '<svg '.$attrs.'><path d="M3 2v6h6"/><path d="M3 8a9 9 0 1 0 2.6-6.4L3 4"/></svg>',
      'chevron-right' => '<svg '.$attrs.'><path d="m9 6 6 6-6 6"/></svg>',
      'chevron-down' => '<svg '.$attrs.'><path d="m6 9 6 6 6-6"/></svg>',
      'calendar-days' => '<svg '.$attrs.'><path d="M8 2v4"/><path d="M16 2v4"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/></svg>',
      default => '<svg '.$attrs.'><circle cx="12" cy="12" r="9"/></svg>',
    };
  };
  $fmtDash = fn($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d-m-Y') : '-';
  $fmtSlash = fn($date) => $date ? \Illuminate\Support\Carbon::parse($date)->format('d/m/Y') : '-';
  
?>

<div class="w-full space-y-6 pb-10">
  <x-page-header
    title="Dashboard"
    subtitle="Monitoring ketersediaan kamar."
  />


<div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tipeList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
    <?php
      $terisi   = (int) ($terisiMap[$t->tipe_kamar] ?? 0);
      $total    = (int) ($t->total ?? 0);
      $perbaikan = (int) ($perbaikanMap[$t->tipe_kamar] ?? 0);
      $tersedia = max(0, $total - $terisi - $perbaikan);
      $occPct   = $total > 0 ? (int) round(($terisi / $total) * 100) : 0;

      // status label & style (simple)
      // status label & style (simple)
$statusText = $tersedia === 0 ? 'Penuh' : ($tersedia <= 2 ? 'Hampir penuh' : 'Tersedia');
$statusCls  = $tersedia === 0
  ? 'bg-rose-50 text-rose-700 border-rose-200'
  : ($tersedia <= 2
      ? 'bg-[#FFB22C]/15 text-[#9A5B00] border-[#FFB22C]/40'
      : 'bg-emerald-50 text-emerald-700 border-emerald-200');

$barCls = $tersedia === 0
  ? 'bg-rose-500'
  : ($tersedia <= 2 ? 'bg-[#FFB22C]' : 'bg-emerald-500');
    ?>

    <button
      type="button"
      wire:click="setTipe(<?php echo \Illuminate\Support\Js::from($t->tipe_kamar)->toHtml() ?>)"
      class="group w-full text-left rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition
             hover:border-slate-300 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[#854836]/20"
    >
      <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
          <div class="flex items-center gap-2 text-xs font-semibold tracking-wide text-slate-500">
            <span class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-slate-100 text-slate-700">
              <?php echo $svgIcon($iconByType($t->tipe_kamar), 'h-4 w-4'); ?>
            </span>
            <span class="truncate"><?php echo e($displayTipe($t->tipe_kamar)); ?></span>
          </div>

          <div class="mt-3 flex items-baseline gap-2">
            <div class="text-3xl font-extrabold text-slate-900 leading-none">
              <?php echo e($tersedia); ?>

            </div>
            <div class="text-sm font-semibold text-slate-500">
              tersedia
            </div>
          </div>

          <div class="mt-1 text-xs text-slate-500 whitespace-nowrap">
            Terisi <span class="font-semibold text-slate-700"><?php echo e($terisi); ?></span>
            • Perbaikan <span class="font-semibold text-slate-700"><?php echo e($perbaikan); ?></span>
            dari <span class="font-semibold text-slate-700"><?php echo e($total); ?></span>
          </div>
        </div>

        <span class="shrink-0 inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold <?php echo e($statusCls); ?>">
          <?php echo e($statusText); ?>

        </span>
      </div>

      <div class="mt-4">
        <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500">
          <span>Okupansi</span>
          <span><?php echo e($occPct); ?>%</span>
        </div>
        <div class="mt-2 h-2 w-full rounded-full bg-slate-100 overflow-hidden">
          <div class="h-full <?php echo e($barCls); ?>" style="width: <?php echo e($occPct); ?>%"></div>
        </div>
      </div>

      <div class="mt-4 flex items-center justify-between text-sm font-semibold text-[#854836]">
        <span>Lihat kamar</span>
        <?php echo $svgIcon('chevron-right', 'h-4 w-4 opacity-70 group-hover:opacity-100'); ?>
      </div>
    </button>
  <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
</div>

  
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="p-4 sm:p-5">
      <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="relative min-w-0 w-full lg:max-w-xl">
          <span class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
            <?php echo $svgIcon('search', 'h-4 w-4'); ?>
          </span>
          <input
            type="text"
            placeholder="Cari nomor kamar / tipe..."
            class="h-11 min-w-0 max-w-full w-full rounded-xl border border-slate-300 bg-white py-2.5 pl-9 pr-3 text-sm leading-normal
                   focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
            wire:model.live.debounce.300ms="q"
          >
        </div>

        <div class="flex w-full gap-2 lg:w-auto">
          <button
            type="button"
            wire:click="resetFilters"
            class="w-full lg:w-auto inline-flex items-center justify-center gap-2 rounded-xl border border-slate-300
                   bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
          >
            <?php echo $svgIcon('rotate-ccw', 'h-4 w-4'); ?>
            Reset
          </button>
        </div>
      </div>

      <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
        
        <div class="min-w-0 rounded-xl border border-slate-200 bg-slate-50 p-3">
          <label class="block text-xs font-semibold text-slate-600">Tipe kamar</label>
          <div class="relative mt-2">
            <select
              class="h-11 min-w-0 max-w-full w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm leading-normal
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
              wire:model.live="tipe"
            >
              <option value="">Semua</option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $tipeList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <option value="<?php echo e($t->tipe_kamar); ?>">
                  <?php echo e($displayTipe($t->tipe_kamar)); ?>

                </option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <?php echo $svgIcon('chevron-down', 'h-4 w-4'); ?>
            </span>
          </div>
        </div>

        
        <div class="min-w-0 rounded-xl border border-slate-200 bg-slate-50 p-3">
          <label class="block text-xs font-semibold text-slate-600">Status kamar</label>
          <div class="relative mt-2">
            <select
              class="h-11 min-w-0 max-w-full w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm leading-normal
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
              wire:model.live="statusKamar"
            >
              <option value="">Semua</option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $statusKamarOptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                <option value="<?php echo e($s); ?>"><?php echo e($statusKamarLabel($s)); ?></option>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </select>
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <?php echo $svgIcon('chevron-down', 'h-4 w-4'); ?>
            </span>
          </div>
        </div>

        
        <div class="min-w-0 rounded-xl border border-slate-200 bg-slate-50 p-3">
          <label class="block text-xs font-semibold text-slate-600">Dari tanggal</label>
          <div class="relative mt-2">
            <input
              type="date"
              class="h-11 min-w-0 max-w-full w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm leading-normal [color-scheme:light]
                     [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:inset-0 [&::-webkit-calendar-picker-indicator]:h-full
                     [&::-webkit-calendar-picker-indicator]:w-full [&::-webkit-calendar-picker-indicator]:cursor-pointer [&::-webkit-calendar-picker-indicator]:opacity-0
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
              wire:model.live="checkFrom"
            >
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <?php echo $svgIcon('calendar-days', 'h-4 w-4'); ?>
            </span>
          </div>
        </div>

        
        <div class="min-w-0 rounded-xl border border-slate-200 bg-slate-50 p-3">
          <label class="block text-xs font-semibold text-slate-600">Sampai tanggal</label>
          <div class="relative mt-2">
            <input
              type="date"
              class="h-11 min-w-0 max-w-full w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm leading-normal [color-scheme:light]
                     [&::-webkit-calendar-picker-indicator]:absolute [&::-webkit-calendar-picker-indicator]:inset-0 [&::-webkit-calendar-picker-indicator]:h-full
                     [&::-webkit-calendar-picker-indicator]:w-full [&::-webkit-calendar-picker-indicator]:cursor-pointer [&::-webkit-calendar-picker-indicator]:opacity-0
                     focus:outline-none focus:ring-2 focus:ring-[#854836]/30"
              wire:model.live="checkTo"
              min="{{ $checkFrom }}"
            >
            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
              <?php echo $svgIcon('calendar-days', 'h-4 w-4'); ?>
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>

  
  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="flex items-center justify-between border-b border-slate-200 bg-slate-50 px-4 py-3">
      <div class="text-sm font-semibold text-slate-800">
        List Kamar (<?php echo e($kamarPage->total()); ?>)
      </div>
      <div class="text-xs text-slate-500">
        Tanggal cek:
        <span class="font-semibold text-slate-700"><?php echo e($fmtSlash($checkFrom)); ?></span>
        <span class="text-slate-400">s/d</span>
        <span class="font-semibold text-slate-700"><?php echo e($fmtSlash($checkTo)); ?></span>
      </div>
    </div>

    
    <div class="md:hidden divide-y divide-slate-200">
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $kamarPage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
        <?php $statusKamarValue = ((int) ($k->is_perbaikan ?? 0) === 1) ? 'perbaikan' : ((int) ($k->is_terisi ?? 0) === 1 ? 'terisi' : 'tersedia'); ?>

        <div class="p-4">
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="text-sm font-semibold text-slate-900">
                Kamar <?php echo e($k->nomor_kamar); ?>

              </div>
              <div class="mt-0.5 text-xs text-slate-500">
                <?php echo e($displayTipe($k->tipe_kamar)); ?>

              </div>
            </div>

            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold <?php echo e($badgeKamarClass($statusKamarValue)); ?>">
              <?php echo e($statusKamarLabel($statusKamarValue)); ?>

            </span>
          </div>

          <div class="mt-3 text-sm text-slate-700 space-y-1">
            <div><span class="text-slate-500">Tamu:</span> <?php echo e($statusKamarValue === 'perbaikan' ? '-' : ($k->nama_tamu ?? '-')); ?></div>
            <div>
              <span class="text-slate-500">Tanggal:</span>
              <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusKamarValue !== 'perbaikan' && $k->tanggal_check_in): ?>
                <?php echo e($fmtDash($k->tanggal_check_in)); ?> s/d <?php echo e($fmtDash($k->tanggal_check_out)); ?>

              <?php else: ?>
                -
              <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
          </div>
        </div>
      <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <div class="p-6 text-center text-slate-500">Tidak ada kamar ditemukan.</div>
      <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    
    <div class="hidden overflow-x-auto md:block">
      <table class="min-w-full text-sm">
        <thead class="bg-white border-b border-slate-200">
          <tr class="text-left text-slate-600">
            <th class="px-4 py-3 font-medium">No. Kamar</th>
            <th class="px-4 py-3 font-medium">Tipe</th>
            <th class="px-4 py-3 font-medium">Status</th>
            <th class="px-4 py-3 font-medium">Tamu</th>
            <th class="px-4 py-3 font-medium">Tanggal</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200">
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $kamarPage; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
            <?php $statusKamarValue = ((int) ($k->is_perbaikan ?? 0) === 1) ? 'perbaikan' : ((int) ($k->is_terisi ?? 0) === 1 ? 'terisi' : 'tersedia'); ?>

            <tr class="hover:bg-[#854836]/[0.04]">
              <td class="px-4 py-3 font-semibold text-slate-900"><?php echo e($k->nomor_kamar); ?></td>
              <td class="px-4 py-3 text-slate-700"><?php echo e($displayTipe($k->tipe_kamar)); ?></td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold <?php echo e($badgeKamarClass($statusKamarValue)); ?>">
                  <?php echo e($statusKamarLabel($statusKamarValue)); ?>

                </span>
              </td>
              <td class="px-4 py-3 text-slate-700"><?php echo e($statusKamarValue === 'perbaikan' ? '-' : ($k->nama_tamu ?? '-')); ?></td>
              <td class="px-4 py-3 text-slate-700">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($statusKamarValue !== 'perbaikan' && $k->tanggal_check_in): ?>
                  <?php echo e($fmtDash($k->tanggal_check_in)); ?> s/d <?php echo e($fmtDash($k->tanggal_check_out)); ?>

                <?php else: ?>
                  -
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
              </td>
            </tr>
          <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <tr>
              <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                Tidak ada kamar ditemukan.
              </td>
            </tr>
          <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </tbody>
      </table>
    </div>

    <div class="border-t border-slate-200 bg-white px-4 py-3">
      <?php echo e($kamarPage->links(data: ['scrollTo' => false])); ?>

    </div>
  </div>
</div>

<?php /**PATH /Users/mario/Documents/projectweb/ayubeachinn_project/resources/views/livewire/pegawai/dashboard.blade.php ENDPATH**/ ?>

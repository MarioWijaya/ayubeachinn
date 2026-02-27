@props([
    'title' => null,
    'subtitle' => null,
    'rightSlot' => null,
])

<div class="relative z-30 -mx-4 border-b border-slate-200 bg-white/80 px-2 shadow-sm backdrop-blur lg:sticky lg:top-0 lg:z-40 lg:-mx-8 lg:px-8">
  <div class="flex flex-col gap-2 py-2.5 lg:h-16 lg:flex-row lg:items-center lg:justify-between lg:py-0">
    <div wire:ignore class="inline-flex h-9 w-fit max-w-full items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 text-xs font-semibold text-slate-700 shadow-sm">
      <span
        id="realtimeClock"
        data-realtime-clock
        data-tz="Asia/Makassar"
        class="truncate tracking-wide"
      ></span>
    </div>
    <div class="flex w-full items-center justify-stretch lg:w-auto lg:justify-end">
      @if ($rightSlot)
        <div class="w-full lg:w-auto">
          {{ $rightSlot }}
        </div>
      @endif
    </div>
  </div>
</div>

@if ($title || $subtitle)
  <div class="mt-3 flex flex-col gap-2 sm:mt-4">
    @if ($title)
      <h1 class="text-2xl font-semibold text-slate-900">{{ $title }}</h1>
    @endif
    @if ($subtitle)
      <p class="text-sm text-slate-600">{{ $subtitle }}</p>
    @endif
  </div>
@endif

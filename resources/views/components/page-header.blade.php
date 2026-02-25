@props([
    'title' => null,
    'subtitle' => null,
    'rightSlot' => null,
])

<div class="sticky top-0 z-40 -mx-4 border-b border-slate-200 bg-white/80 px-2 shadow-sm backdrop-blur lg:-mx-8 lg:px-8">
  <div class="flex h-16 items-center justify-between gap-3">
    <div wire:ignore class="inline-flex h-9 items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-4 text-xs font-semibold text-slate-700 shadow-sm">
      <span
        id="realtimeClock"
        data-realtime-clock
        data-tz="Asia/Makassar"
        class="tracking-wide"
      ></span>
    </div>
    <div class="flex items-center justify-end">
      @if ($rightSlot)
        <div class="w-full sm:w-auto">
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

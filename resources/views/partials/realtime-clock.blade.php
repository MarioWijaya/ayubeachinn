<span
  wire:ignore
  x-data="{
    now: new Date(),
    tick(){ this.now = new Date() },
    fmt(d){
      const pad = (n) => String(n).padStart(2,'0');
      const days = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
      const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
      return `${days[d.getDay()]}, ${pad(d.getDate())} ${months[d.getMonth()]} ${d.getFullYear()} • ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`;
    }
  }"
  x-init="tick(); setInterval(() => tick(), 1000)"
  class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-semibold text-slate-600"
>
  <i data-lucide="clock" class="h-3.5 w-3.5 text-[#854836]"></i>
  <span x-text="fmt(now)"></span>
</span>

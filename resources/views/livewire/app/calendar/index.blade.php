<div class="w-full space-y-4 pb-10">

  <x-page-header
    title="Kalender Ketersediaan"
    subtitle="Klik Tanggal untuk melihat detail daftar booking hari itu."
  />

  {{-- KALENDER --}}
  <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <div
      wire:ignore
      id="calendar"
      data-days='@json($daysJson ?? [])'
      data-total="{{ $totalRooms ?? 0 }}"
    ></div>
  </div>

  {{-- MODAL DETAIL HARI --}}
  <div id="dayDetailOverlay"
       class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/40 p-4">
    <div class="modal-card w-full max-w-4xl rounded-2xl bg-white shadow-xl border border-slate-200 overflow-hidden flex flex-col">
      <div class="modal-header flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
        <div>
          <div id="ddTitle" class="text-lg font-bold text-slate-900">Detail</div>
          <div id="ddSub" class="text-xs text-slate-500">—</div>
        </div>
        <button type="button" class="rounded-lg p-2 hover:bg-slate-100"
                onclick="window.closeDayDetail()">
          ✕
        </button>
      </div>

      <div class="modal-body p-6 space-y-5 flex-1 overflow-y-auto">
        <div class="stats-grid grid grid-cols-1 md:grid-cols-3 gap-3">
          <div class="stat-card rounded-2xl border border-slate-200 p-4">
            <div class="text-xs text-slate-500">Total Kamar</div>
            <div id="ddTotal" class="text-2xl font-extrabold text-slate-900">0</div>
          </div>
          <div class="stat-card rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
            <div class="text-xs text-emerald-700">Kosong</div>
            <div id="ddEmpty" class="text-2xl font-extrabold text-emerald-800">0</div>
          </div>
          <div class="stat-card rounded-2xl border border-rose-200 bg-rose-50 p-4">
            <div class="text-xs text-rose-700">Terisi</div>
            <div id="ddOcc" class="text-2xl font-extrabold text-rose-800">0</div>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
          <div class="section rounded-2xl border border-slate-200 overflow-hidden">
          <div class="section-head px-4 py-3 bg-slate-50 border-b border-slate-200 font-semibold flex items-center justify-between">
            <span class="section-title">Check-in</span>
            <span id="ddCheckinsCount" class="section-count text-xs font-semibold text-slate-500">(0)</span>
          </div>
            <div id="ddCheckins" class="items p-4 space-y-2 max-h-[360px] overflow-auto"></div>
          </div>
          <div class="section rounded-2xl border border-slate-200 overflow-hidden">
          <div class="section-head px-4 py-3 bg-slate-50 border-b border-slate-200 font-semibold flex items-center justify-between">
            <span class="section-title">Check-out</span>
            <span id="ddCheckoutsCount" class="section-count text-xs font-semibold text-slate-500">(0)</span>
          </div>
            <div id="ddCheckouts" class="items p-4 space-y-2 max-h-[360px] overflow-auto"></div>
          </div>
        </div>

      </div>
      <div class="modal-footer flex items-center justify-end border-t border-slate-200 bg-white px-6 py-4">
        <button type="button"
                class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-semibold hover:bg-slate-50"
                onclick="window.closeDayDetail()">
          Tutup
        </button>
      </div>
    </div>
  </div>

</div>

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.20/index.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fullcalendar/daygrid@6.1.20/index.css">

<style>
  .fc{
    --fc-border-color: #e2e8f0;
    --fc-page-bg-color: transparent;
    --fc-today-bg-color: rgba(133, 72, 54, 0.08);
    font-family: Poppins, ui-sans-serif, system-ui;
    overflow: hidden;
  }

  .fc .fc-toolbar-title{
    font-size: 18px;
    font-weight: 800;
    color: #0f172a;
  }

  .fc .fc-button{
    border-radius: 12px !important;
    border: 1px solid #e2e8f0 !important;
    background: #fff !important;
    color: #0f172a !important;
    font-weight: 700 !important;
    padding: 8px 12px !important;
    box-shadow: 0 1px 2px rgba(0,0,0,.05);
  }

  .fc .fc-button:hover{
    background: #f8fafc !important;
  }

  .fc .fc-button-primary:not(:disabled).fc-button-active,
  .fc .fc-button-primary:not(:disabled):active{
    background: #854836 !important;
    border-color: #854836 !important;
    color: #fff !important;
  }

  .fc .fc-col-header-cell-cushion{
    font-weight: 700;
    color: #334155;
    padding: 10px 0;
  }

  .fc .fc-daygrid-day-number{
    font-weight: 700;
    color: #334155;
    padding: 8px 8px 0;
  }

  .fc .fc-daygrid-day-frame{
    min-height: 110px;
  }

  .fc .fc-daygrid-day-events{
    overflow: hidden;
    padding: 2px 4px 6px;
  }

  /* wrapper kecil dalam cell */
  .fc .day-card{
    margin: 4px 4px 6px;
    padding: 8px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    background: linear-gradient(180deg, #fff 0%, #fafafa 100%);
    box-shadow: 0 1px 2px rgba(0,0,0,.04);
    max-width: 100%;
    box-sizing: border-box;
    display: grid;
    gap: 6px;
    cursor: pointer;
  }

  .fc .day-card__top{
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
  }

  .fc .day-card__title{
    font-size: 11px;
    font-weight: 800;
    color: #64748b;
    letter-spacing: .3px;
    text-transform: uppercase;
  }

  .fc .day-card__value{
    font-size: 14px;
    font-weight: 900;
    color: #0f172a;
  }

  .fc .day-card__chips{
    display: grid;
    gap: 4px;
  }

  .fc .day-card--mobile{
    position: relative;
    min-height: 76px;
    padding: 0;
    margin: 0;
    border: none;
    background: transparent;
    box-shadow: none;
  }

  .fc .day-card__mobile-badge{
    display: none;
    align-items: center;
    justify-content: center;
    width: max-content;
    min-width: 22px;
    height: 22px;
    padding: 0 6px;
    border-radius: 999px;
    border: 1px solid #854836;
    background: #ffb22c33;
    color: #111827;
    font-size: 10px;
    font-weight: 900;
    line-height: 1;
    position: absolute;
    bottom: 6px;
    left: 6px;
    max-width: calc(100% - 12px);
  }

  .fc .day-card__mobile-badge.is-zero{
    opacity: 0.45;
  }

  .fc .chip{
    display: inline-flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 6px;
    border-radius: 8px;
    font-size: 10px;
    font-weight: 900;
    line-height: 1;
    border: 1px solid transparent;
    white-space: nowrap;
    gap: 6px;
  }

  .fc .chip__label{
    flex: 1 1 auto;
    color: inherit;
  }

  .fc .chip__label--short{
    display: none;
  }

  .fc .chip__value{
    font-size: 11px;
  }

  .fc .chip--empty{
    background: #e6f9ef;
    border-color: #b8e7cf;
    color: #166534;
  }

  .fc .chip--occ{
    background: #fdecec;
    border-color: #f6c2c2;
    color: #991b1b;
  }

  .fc .btn-detail{
    width: 100%;
    border: 1px solid #854836;
    border-radius: 8px;
    padding: 5px 8px;
    font-size: 10px;
    font-weight: 900;
    color: #854836;
    background: #fff7ed;
    text-align: center;
  }

  .fc .day-card:hover{
    border-color: #854836;
    box-shadow: 0 2px 8px rgba(133, 72, 54, 0.12);
  }

  .fc .day-card:active{
    transform: scale(.99);
  }

  /* Responsive */
  @media (max-width: 820px){
    .fc .fc-toolbar-title{ font-size: 16px; }
    .fc .fc-button{ padding: 6px 10px !important; }
    .fc .fc-daygrid-day-frame{ min-height: 100px; }
    .fc .fc-daygrid-day-number{ font-size: 12px; padding: 6px 6px 0; }
    .fc .day-card{ margin: 3px 3px 5px; padding: 6px; border-radius: 10px; }
    .fc .day-card__title{ font-size: 10px; }
    .fc .day-card__value{ font-size: 12px; }
    .fc .chip{ font-size: 9px; padding: 4px 5px; }
  }

  @media (max-width: 640px){
    .fc .fc-toolbar{
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }
    .fc .fc-toolbar-chunk{
      width: 100%;
      display: flex;
      justify-content: center;
      gap: 6px;
      flex-wrap: wrap;
    }
    .fc .fc-toolbar-title{
      width: 100%;
      text-align: center;
      font-size: 15px;
    }
    .fc .fc-button{
      padding: 6px 8px !important;
      font-size: 12px !important;
    }
    .fc .fc-daygrid-day-frame{ min-height: 90px; }
    .fc .day-card{
      margin: 2px 2px 4px;
      padding: 6px;
      border-radius: 10px;
      position: relative;
    }
    .fc .day-card__title{
      font-size: clamp(9px, 2.2vw, 10px);
    }
    .fc .day-card__value{
      font-size: clamp(11px, 2.6vw, 12px);
    }
    .fc .chip{
      font-size: clamp(8px, 2.2vw, 9px);
      padding: 4px 5px;
      border-radius: 7px;
    }
    .fc .btn-detail{
      font-size: 9px;
      padding: 4px 6px;
    }
    .fc .chip__label--full{
      display: none;
    }
    .fc .chip__label--short{
      display: inline;
      letter-spacing: .2px;
    }
    .fc .day-card__chips{
      grid-template-columns: 1fr;
      gap: 3px;
    }

    .fc .day-card{
      padding: 6px;
      place-items: center;
    }

    .fc .day-card__mobile-badge{
      display: inline-flex;
      font-size: clamp(10px, 2.6vw, 12px);
      height: clamp(18px, 4.4vw, 22px);
      padding: clamp(2px, 0.8vw, 6px) clamp(6px, 1.4vw, 10px);
      letter-spacing: .1px;
    }
  }

  @media (max-width: 430px){
    .fc .fc-button{
      padding: 4px 6px !important;
      font-size: 11px !important;
    }
    .fc .fc-toolbar{ gap: 6px; }
    .fc .fc-daygrid-day-number{ font-size: 11px; padding: 4px 4px 0; }
    .fc .fc-daygrid-day-frame{ min-height: 86px; }
  }

  @media (max-width: 360px){
    .fc .fc-toolbar-title{ font-size: 14px; }
    .fc .fc-button{
      padding: 3px 5px !important;
      font-size: 10px !important;
    }
    .fc .fc-daygrid-day-number{ font-size: 10px; padding: 4px 4px 0; }
    .fc .fc-daygrid-day-frame{ min-height: 80px; }
    .fc .day-card{ padding: 5px; }
  }

  @media (max-width: 480px){
    .fc .fc-daygrid-day-frame,
    .fc .fc-daygrid-day-events,
    .fc .fc-daygrid-day-bg{
      overflow: visible;
    }

    .fc .fc-daygrid-day-frame{
      position: relative;
      min-height: 76px;
    }

    .fc .day-card--desktop{
      display: none;
    }

    .fc .day-card--mobile{
      place-items: unset;
    }

    .fc .day-card__mobile-badge{
      display: inline-flex;
      position: absolute;
      left: 6px;
      bottom: 6px;
      z-index: 10;
      font-size: 11px;
      line-height: 1.1;
      padding: 4px 8px;
      height: auto;
      max-width: calc(100% - 12px);
    }
  }

  @media (max-width: 480px){
    .modal-card{
      max-height: 90vh;
      overflow: hidden;
      border-radius: 16px;
    }

    .modal-header{
      position: sticky;
      top: 0;
      z-index: 20;
      background: #fff;
    }

    .modal-body{
      padding: 16px;
      gap: 12px;
    }

    .stats-grid{
      grid-template-columns: repeat(3, minmax(0, 1fr));
      gap: 8px;
    }

    .stat-card{
      padding: 10px;
      border-radius: 14px;
    }

    .section-head{
      padding: 8px 10px;
    }

    .section-title{
      font-size: 13px;
    }

    .items{
      padding: 10px;
      gap: 8px;
      font-size: 12px;
    }

    .item{
      padding: 10px;
      border-radius: 14px;
      font-size: 12px;
    }

    .item .meta{
      font-size: 11px;
    }

    .item-empty{
      font-size: 12px;
      color: #64748b;
    }

    .modal-footer{
      padding: 12px 16px;
    }
  }
</style>
@endpush

@push('scripts')
<script>
  // data dari server
  window.__calendarDays = @json($daysJson ?? []);
  window.__totalRooms = @json($totalRooms ?? 0);

  window.openDayDetail = (dateStr) => {
    const data = window.__calendarDays?.[dateStr];
    if (!data) return;

    const overlay = document.getElementById('dayDetailOverlay');
    const formatDashDate = (value) => {
      if (!value || typeof value !== 'string') return value || '-';
      const parts = value.split('-');
      if (parts.length !== 3) return value;
      return `${parts[2]}-${parts[1]}-${parts[0]}`;
    };
    document.getElementById('ddTitle').textContent = 'Detail Ketersediaan Kamar';
    document.getElementById('ddSub').textContent = formatDashDate(dateStr);

    document.getElementById('ddTotal').textContent = data.total_rooms ?? 0;
    document.getElementById('ddEmpty').textContent = data.empty_count ?? 0;
    document.getElementById('ddOcc').textContent   = data.occupied_count ?? 0;

    const renderItem = (b) => `
      <div class="item rounded-xl border border-slate-200 p-3">
        <div class="font-semibold text-slate-900">${b.nama_tamu} <span class="text-slate-500 font-semibold">(Kmr ${b.nomor_kamar})</span></div>
        <div class="meta text-xs text-slate-600">${b.tanggal_check_in} → ${b.tanggal_check_out}</div>
        <div class="meta text-xs font-semibold text-slate-700 mt-1">Status: ${b.status_booking ?? '-'}</div>
      </div>
    `;

    const checkins = data.checkins || [];
    const checkouts = data.checkouts || [];
    document.getElementById('ddCheckinsCount').textContent = `(${checkins.length})`;
    document.getElementById('ddCheckoutsCount').textContent = `(${checkouts.length})`;

    const ci = checkins.map(renderItem).join('') || `<div class="item-empty">Tidak ada check-in</div>`;
    const co = checkouts.map(renderItem).join('') || `<div class="item-empty">Tidak ada check-out</div>`;

    document.getElementById('ddCheckins').innerHTML = ci;
    document.getElementById('ddCheckouts').innerHTML = co;

    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
  };

  window.closeDayDetail = () => {
    const overlay = document.getElementById('dayDetailOverlay');
    overlay.classList.add('hidden');
    overlay.classList.remove('flex');
  };

  document.addEventListener('click', (e) => {
    const overlay = document.getElementById('dayDetailOverlay');
    if (!overlay || overlay.classList.contains('hidden')) return;
    if (e.target === overlay) window.closeDayDetail();
  });

</script>
@endpush

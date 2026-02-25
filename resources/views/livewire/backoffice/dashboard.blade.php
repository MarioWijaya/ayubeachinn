<div
  class="w-full space-y-6 pb-10"
  data-dashboard-root
  data-endpoint="{{ route('dashboard.charts') }}"
  data-default-start="{{ $from }}"
  data-default-end="{{ $to }}"
>
  <x-page-header />

  <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div>
      <h1 class="text-2xl font-semibold text-slate-900">Dashboard</h1>
      <p class="text-sm text-slate-600">Ringkasan performa hotel berdasarkan periode yang dipilih.</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-3 shadow-sm">
      <div class="grid grid-cols-1 gap-2 md:grid-cols-2 lg:flex lg:flex-wrap lg:items-center">
        <div class="relative min-w-0 w-full md:col-span-1 lg:w-auto">
          <select
            data-preset-select
            class="h-11 min-w-0 max-w-full w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-sm font-semibold leading-normal text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 md:text-xs"
          >
            <option value="all">Semua</option>
            <option value="today">Hari ini</option>
            <option value="7">7 hari</option>
            <option value="14">14 hari</option>
            <option value="30">30 hari</option>
            <option value="month">Bulan ini</option>
            <option value="custom" hidden>Kustom</option>
          </select>
          <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
            <i data-lucide="chevron-down" class="h-4 w-4"></i>
          </span>
        </div>

        <div class="min-w-0 grid grid-cols-1 gap-2 md:col-span-1 md:grid-cols-[1fr_auto_1fr] lg:flex lg:items-center">
          <input
            type="date"
            data-filter-start
            class="h-11 min-w-0 max-w-full w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold leading-normal text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 md:text-xs"
            aria-label="Dari tanggal"
          >
          <span class="hidden text-center text-xs font-semibold text-slate-500 md:block">s/d</span>
          <input
            type="date"
            data-filter-end
            class="h-11 min-w-0 max-w-full w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm font-semibold leading-normal text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30 md:text-xs"
            aria-label="Sampai tanggal"
          >
        </div>

        <button
          type="button"
          data-filter-reset
          class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 md:w-auto md:text-xs"
        >
          <i data-lucide="rotate-ccw" class="h-3.5 w-3.5"></i>
          Reset
        </button>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Kamar</div>
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
          <i data-lucide="bed-double" class="h-4 w-4"></i>
        </span>
      </div>
      <div class="mt-3 text-3xl font-extrabold text-slate-900" data-stat="total-rooms">0</div>
      <div class="mt-1 text-xs text-slate-500">Kamar aktif (tidak perbaikan)</div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Kamar Terisi</div>
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
          <i data-lucide="check-circle" class="h-4 w-4"></i>
        </span>
      </div>
      <div class="mt-3 text-3xl font-extrabold text-slate-900" data-stat="occupied-today">0</div>
      <div class="mt-1 text-xs">
        <span data-occupancy-badge class="font-semibold text-slate-700">
          Okupansi <span data-stat="occupancy-rate">0%</span>
        </span>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Total Booking</div>
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-[#FFB22C]/20 text-[#9A5B00]">
          <i data-lucide="clipboard-list" class="h-4 w-4"></i>
        </span>
      </div>
      <div class="mt-3 text-3xl font-extrabold text-slate-900" data-stat="bookings-count">0</div>
      <div class="mt-1 text-xs text-slate-500">Dalam range filter</div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="text-xs font-semibold uppercase tracking-wide text-slate-400">Pendapatan</div>
        <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-slate-100 text-[#854836]">
          <i data-lucide="wallet" class="h-4 w-4"></i>
        </span>
      </div>
      <div class="mt-3 text-2xl font-extrabold text-slate-900" data-stat="revenue-total">Rp 0</div>
      <div class="mt-1 text-xs text-slate-500">Status selesai</div>
    </div>
  </div>

  <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm lg:col-span-2">
      <div class="flex items-center justify-between">
        <div>
          <div class="text-sm font-semibold text-slate-800">Okupansi</div>
          <div class="text-xs text-slate-500" data-occupancy-caption>Range filter</div>
        </div>
      </div>
      <div class="mt-4 h-56 sm:h-64 lg:h-80">
        <canvas id="occupancyChart" class="h-full w-full" wire:ignore></canvas>
      </div>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <div class="flex items-center justify-between">
        <div class="text-sm font-semibold text-slate-800">Distribusi Status</div>
        <div class="text-xs text-slate-500">Range filter</div>
      </div>
        <div class="mt-4">
          <div class="h-56 sm:h-60">
            <canvas id="statusChart" class="h-full w-full" wire:ignore></canvas>
          </div>
          <div class="mt-4 flex flex-wrap items-center justify-center gap-3 text-sm" data-status-legend></div>
        </div>
      </div>
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-center justify-between">
      <div>
        <div class="text-sm font-semibold text-slate-800">Pendapatan</div>
        <div class="text-xs text-slate-500">Range filter</div>
      </div>
      <div class="text-xs text-slate-500">Trend pemasukan</div>
    </div>
    <div class="mt-4 h-52 sm:h-64 lg:h-72">
      <canvas id="revenueChart" class="h-full w-full" wire:ignore></canvas>
    </div>
  </div>

  <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="flex flex-col gap-3 border-b border-slate-200 px-4 py-4 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <div class="text-sm font-semibold text-slate-800">Booking Terbaru</div>
        <div class="text-xs text-slate-500">Ringkasan booking dalam range.</div>
      </div>
      <div class="grid w-full grid-cols-1 gap-2 sm:w-auto sm:grid-cols-[minmax(0,1fr)_180px]">
        <input
          type="text"
          placeholder="Cari tamu..."
          class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30"
          data-booking-search
        >
        <div class="relative">
          <select
            class="w-full appearance-none rounded-xl border border-slate-300 bg-white px-3 py-2.5 pr-10 text-xs font-semibold leading-normal text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#FFB22C]/30"
            data-booking-filter
          >
            <option value="">Semua status</option>
            <option value="menunggu">Menunggu</option>
            <option value="check_in">Check-in</option>
            <option value="check_out">Check-out</option>
            <option value="selesai">Selesai</option>
            <option value="batal">Batal</option>
          </select>
          <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
            <i data-lucide="chevron-down" class="h-4 w-4"></i>
          </span>
        </div>
      </div>
    </div>

    <div class="hidden overflow-x-auto md:block">
      <table class="min-w-full text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold text-slate-600">
          <tr>
            <th class="px-4 py-3">Nama Tamu</th>
            <th class="px-4 py-3">Kamar</th>
            <th class="px-4 py-3">Check-in</th>
            <th class="px-4 py-3">Check-out</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3 text-right">Total</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 text-slate-700" data-booking-rows></tbody>
      </table>
    </div>

    <div class="space-y-3 px-4 py-4 md:hidden" data-booking-cards></div>
  </div>
</div>

@push('scripts')
<script>
  (() => {
    if (window.__dashboardInitBound) return;
    window.__dashboardInitBound = true;

    const handler = () => window.scheduleDashboardInit?.();
    document.addEventListener('DOMContentLoaded', handler);
    document.addEventListener('livewire:navigated', handler);
  })();

  window.scheduleDashboardInit = function () {
    let tries = 0;

    const run = () => {
      tries += 1;
      const root = document.querySelector('[data-dashboard-root]');
      if (!root) return;
      if (root.offsetWidth === 0 && tries < 8) {
        setTimeout(run, 80);
        return;
      }

      window.initDashboardCharts?.();
    };

    requestAnimationFrame(run);
  };

  window.initDashboardCharts = async function () {
    const root = document.querySelector('[data-dashboard-root]');
    if (!root) {
      if (window.__dashboardCharts) {
        Object.values(window.__dashboardCharts).forEach((chart) => chart?.destroy());
        window.__dashboardCharts = {};
      }
      return;
    }

    const endpoint = root.dataset.endpoint;
    const defaultStart = root.dataset.defaultStart;
    const defaultEnd = root.dataset.defaultEnd;
    const cacheKey = 'dashboard:cache';
    const startInput = root.querySelector('[data-filter-start]');
    const endInput = root.querySelector('[data-filter-end]');
    const presetSelect = root.querySelector('[data-preset-select]');
    const resetButton = root.querySelector('[data-filter-reset]');
    const rowsContainer = root.querySelector('[data-booking-rows]');
    const cardsContainer = root.querySelector('[data-booking-cards]');
    const legendContainer = root.querySelector('[data-status-legend]');
    const bookingSearch = root.querySelector('[data-booking-search]');
    const bookingFilter = root.querySelector('[data-booking-filter]');
    let bookingsCache = [];

    if (!window.__dashboardCharts) {
      window.__dashboardCharts = {};
    }

    const formatCurrency = new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      maximumFractionDigits: 0,
    });

    const getBrand = () => {
      const raw = getComputedStyle(document.documentElement).getPropertyValue('--brand').trim() || '133 72 54';
      const [r, g, b] = raw.split(' ').map((value) => Number.parseInt(value, 10));
      return {
        solid: `rgb(${r}, ${g}, ${b})`,
        soft: `rgba(${r}, ${g}, ${b}, 0.18)`,
      };
    };

    const formatDate = (date) => {
      const year = date.getFullYear();
      const month = String(date.getMonth() + 1).padStart(2, '0');
      const day = String(date.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
    };

    const formatDateHuman = (value) => {
      if (!value) return '-';
      const date = new Date(`${value}T00:00:00`);
      if (Number.isNaN(date.getTime())) return value;
      return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }).format(date);
    };

    const granularityText = (granularity) => {
      if (granularity === 'week') return 'Agregasi mingguan';
      if (granularity === 'month') return 'Agregasi bulanan';
      if (granularity === 'year') return 'Agregasi tahunan';
      return 'Agregasi harian';
    };

    const applyOccupancyCaption = (range, granularity) => {
      const captionEl = root.querySelector('[data-occupancy-caption]');
      if (!captionEl || !range) return;

      const start = formatDateHuman(range.start_date);
      const end = formatDateHuman(range.end_date);
      captionEl.textContent = `${granularityText(granularity)} • ${start} - ${end}`;
    };

    const resolveRange = (preset) => {
      if (preset === 'all') {
        return [null, null];
      }

      const today = new Date();
      today.setHours(0, 0, 0, 0);
      const end = new Date(today);
      let start = new Date(today);

      if (preset === 'today') {
        // default hari ini (start = end)
      } else if (preset === '7') {
        start.setDate(start.getDate() - 6);
      } else if (preset === '14') {
        start.setDate(start.getDate() - 13);
      } else if (preset === '30') {
        start.setDate(start.getDate() - 29);
      } else if (preset === 'month') {
        start = new Date(today.getFullYear(), today.getMonth(), 1);
      } else {
        return [null, null];
      }

      return [formatDate(start), formatDate(end)];
    };

    const updateUrl = (start, end, preset) => {
      const url = new URL(window.location.href);
      if (start && end) {
        url.searchParams.set('start_date', start);
        url.searchParams.set('end_date', end);
      } else {
        url.searchParams.delete('start_date');
        url.searchParams.delete('end_date');
      }
      if (preset) {
        url.searchParams.set('preset', preset);
      } else {
        url.searchParams.delete('preset');
      }
      window.history.replaceState({}, '', url.toString());
    };

    const destroyCharts = () => {
      Object.values(window.__dashboardCharts).forEach((chart) => chart?.destroy());
      window.__dashboardCharts = {};
    };

    const applyStats = (stats) => {
      root.querySelector('[data-stat="total-rooms"]').textContent = stats.total_rooms;
      root.querySelector('[data-stat="occupied-today"]').textContent = stats.occupied_today;
      root.querySelector('[data-stat="occupancy-rate"]').textContent = `${stats.occupancy_rate}%`;
      root.querySelector('[data-stat="bookings-count"]').textContent = stats.bookings_count;
      root.querySelector('[data-stat="revenue-total"]').textContent = formatCurrency.format(stats.revenue_total || 0);

      const badge = root.querySelector('[data-occupancy-badge]');
      if (badge) {
        const rate = Number(stats.occupancy_rate || 0);
        const base = 'font-semibold';

        if (rate < 40) {
          badge.className = `${base} text-rose-600`;
        } else if (rate < 70) {
          badge.className = `${base} text-[#9A5B00]`;
        } else {
          badge.className = `${base} text-emerald-600`;
        }
      }
    };

    const statusStyles = {
      menunggu: 'bg-[#FFB22C]/15 text-[#9A5B00] border-[#FFB22C]/40',
      check_in: 'bg-emerald-50 text-emerald-700 border-emerald-200',
      check_out: 'bg-sky-50 text-sky-700 border-sky-200',
      selesai: 'bg-slate-100 text-slate-700 border-slate-200',
      batal: 'bg-rose-50 text-rose-700 border-rose-200',
    };

    const statusLabels = {
      menunggu: 'menunggu',
      check_in: 'check in',
      check_out: 'check-out',
      selesai: 'selesai',
      batal: 'batal',
    };

    const formatStatus = (status) => statusLabels[status] ?? status.replace('_', ' ');

    const renderBookings = (bookings) => {
      rowsContainer.innerHTML = '';
      cardsContainer.innerHTML = '';

      if (!bookings.length) {
        const emptyRow = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 6;
        cell.className = 'px-3 py-6 text-center text-sm text-slate-500';
        cell.textContent = 'Belum ada booking di periode ini.';
        emptyRow.appendChild(cell);
        rowsContainer.appendChild(emptyRow);
        return;
      }

      bookings.forEach((booking) => {
        const row = document.createElement('tr');
        row.className = 'text-slate-700';
        row.innerHTML = `
          <td class="px-3 py-3 font-semibold text-slate-900">${booking.nama_tamu}</td>
          <td class="px-3 py-3">${booking.kamar}</td>
          <td class="px-3 py-3">${booking.check_in}</td>
          <td class="px-3 py-3">${booking.check_out}</td>
          <td class="px-3 py-3">
            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold ${statusStyles[booking.status] || 'bg-slate-50 text-slate-700 border-slate-200'}">
              ${formatStatus(booking.status)}
            </span>
          </td>
          <td class="px-3 py-3 text-right font-semibold text-slate-900">${formatCurrency.format(booking.total)}</td>
        `;
        rowsContainer.appendChild(row);

        const card = document.createElement('div');
        card.className = 'rounded-2xl border border-slate-200 bg-white p-4 shadow-sm';
        card.innerHTML = `
          <div class="flex items-start justify-between gap-3">
            <div>
              <div class="text-sm font-semibold text-slate-900">${booking.nama_tamu}</div>
              <div class="text-xs text-slate-500">${booking.kamar}</div>
            </div>
            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold ${statusStyles[booking.status] || 'bg-slate-50 text-slate-700 border-slate-200'}">
              ${formatStatus(booking.status)}
            </span>
          </div>
          <div class="mt-3 text-xs text-slate-500">${booking.check_in} • ${booking.check_out}</div>
          <div class="mt-2 text-sm font-semibold text-slate-900">${formatCurrency.format(booking.total)}</div>
        `;
        cardsContainer.appendChild(card);
      });
    };

    const applyBookingFilters = () => {
      const keyword = (bookingSearch?.value || '').toLowerCase().trim();
      const status = bookingFilter?.value || '';

      const filtered = bookingsCache.filter((booking) => {
        const matchStatus = status ? booking.status === status : true;
        const matchKeyword = keyword
          ? booking.nama_tamu.toLowerCase().includes(keyword) ||
            booking.kamar.toLowerCase().includes(keyword)
          : true;
        return matchStatus && matchKeyword;
      });

      renderBookings(filtered);
    };

    const statusPalette = {
      menunggu: 'rgba(255, 178, 44, 0.7)',
      check_in: getBrand().solid,
      check_out: '#0EA5E9',
      selesai: '#0E9F6E',
      batal: '#F93827',
    };

    const renderLegend = (labels, totals) => {
      legendContainer.innerHTML = '';
      const grandTotal = totals.reduce((sum, value) => sum + Number(value || 0), 0);
      labels.forEach((label, index) => {
        const color = statusPalette[label] || 'rgba(148, 163, 184, 0.8)';
        const value = Number(totals[index] || 0);
        const ratio = grandTotal > 0 ? value / grandTotal : 0;
        const tinySlice = value > 0 && ratio < 0.02;

        const item = document.createElement('div');
        item.className = `inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1 text-xs font-semibold text-slate-700 ${tinySlice ? 'ring-1 ring-slate-200' : ''}`;
        item.innerHTML = `
          <div class="flex items-center gap-2">
            <span class="inline-flex h-2.5 w-2.5 rounded-full" style="background-color:${color}"></span>
            <span>${formatStatus(label)}</span>
          </div>
          <span class="font-bold text-slate-900">${value}</span>
        `;
        legendContainer.appendChild(item);
      });
    };

    const applyCharts = (charts, animateOverride = null) => {
      destroyCharts();
      const brand = getBrand();
      const animateNow = false;

      const occupancyCtx = document.getElementById('occupancyChart');
      if (occupancyCtx && window.Chart) {
        window.__dashboardCharts.occupancy = new Chart(occupancyCtx, {
          type: 'bar',
          data: {
            labels: charts.occupancy.labels,
            datasets: [
              {
                label: 'Terisi',
                data: charts.occupancy.occupied,
                backgroundColor: 'rgba(14, 159, 110, 0.85)',
                borderColor: '#0E9F6E',
                borderWidth: 0,
                borderRadius: 0,
                borderSkipped: false,
              },
              {
                label: 'Kosong',
                data: charts.occupancy.empty,
                backgroundColor: 'rgba(148, 163, 184, 0.3)',
                borderColor: 'rgba(148, 163, 184, 0.5)',
                borderWidth: 0,
                borderRadius: { topLeft: 12, topRight: 12, bottomLeft: 0, bottomRight: 0 },
                borderSkipped: false,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
              legend: {
                position: 'bottom',
                labels: {
                  usePointStyle: true,
                  pointStyle: 'roundedRect',
                  boxWidth: 10,
                  color: '#64748b',
                  font: { size: 11, weight: '600' },
                },
              },
            },
            responsiveAnimationDuration: 0,
            scales: {
              x: {
                stacked: true,
                grid: { display: false },
                ticks: {
                  color: '#94a3b8',
                  font: { size: 11 },
                  autoSkip: true,
                  maxTicksLimit: 8,
                  maxRotation: 0,
                },
              },
              y: {
                stacked: true,
                beginAtZero: true,
                grid: {
                  color: 'rgba(148, 163, 184, 0.2)',
                  borderDash: [4, 4],
                },
                ticks: { color: '#94a3b8', font: { size: 11 } },
              },
            },
            datasets: {
              bar: {
                barThickness: 18,
              },
            },
          },
        });
      }

      const statusCtx = document.getElementById('statusChart');
      if (statusCtx && window.Chart) {
        const totalStatus = charts.status_distribution.totals.reduce((acc, val) => acc + val, 0);
        const centerTextPlugin = {
          id: 'statusCenterText',
          beforeDraw(chart) {
            const { ctx, chartArea } = chart;
            if (!chartArea) return;
            const x = (chartArea.left + chartArea.right) / 2;
            const y = (chartArea.top + chartArea.bottom) / 2;

            ctx.save();
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillStyle = '#0f172a';
            ctx.font = '600 20px Poppins, sans-serif';
            ctx.fillText(String(totalStatus), x, y - 8);
            ctx.fillStyle = '#64748b';
            ctx.font = '500 12px Poppins, sans-serif';
            ctx.fillText('Total Booking', x, y + 12);
            ctx.restore();
          },
        };

        window.__dashboardCharts.status = new Chart(statusCtx, {
          type: 'doughnut',
          data: {
            labels: charts.status_distribution.labels,
            datasets: [
              {
                data: charts.status_distribution.totals,
                backgroundColor: [
                  statusPalette.menunggu,
                  statusPalette.check_in,
                  statusPalette.check_out,
                  statusPalette.selesai,
                  statusPalette.batal,
                ],
                borderWidth: 2,
                borderColor: '#ffffff',
                spacing: 1,
                borderRadius: 6,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
              legend: { display: false },
            },
            cutout: '68%',
          },
          plugins: [centerTextPlugin],
        });
      }

      renderLegend(charts.status_distribution.labels, charts.status_distribution.totals);

      const revenueCtx = document.getElementById('revenueChart');
      if (revenueCtx && window.Chart) {
        window.__dashboardCharts.revenue = new Chart(revenueCtx, {
          type: 'line',
          data: {
            labels: charts.revenue_daily.labels,
            datasets: [
              {
                label: 'Pendapatan',
                data: charts.revenue_daily.totals,
                borderColor: brand.solid,
                backgroundColor: brand.soft,
                fill: true,
                tension: 0.35,
                pointRadius: 3,
                pointBackgroundColor: brand.solid,
              },
            ],
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: (context) => formatCurrency.format(context.raw || 0),
                },
              },
            },
            responsiveAnimationDuration: 0,
            scales: {
              y: { beginAtZero: true },
            },
          },
        });
      }

    };

    const fetchData = async (startDate, endDate, preset = null, animateOnFetch = null) => {
      if (!endpoint) return;
      const queryKey = [startDate || '-', endDate || '-', preset || '-'].join('|');
      if (window.__dashboardLastQuery === queryKey && window.__dashboardFetchInFlight) {
        return;
      }

      window.__dashboardLastQuery = queryKey;
      const url = new URL(endpoint, window.location.origin);
      if (startDate && endDate) {
        url.searchParams.set('start_date', startDate);
        url.searchParams.set('end_date', endDate);
      }

      const cached = sessionStorage.getItem(cacheKey);
      if (cached) {
        try {
          const parsed = JSON.parse(cached);
          applyStats(parsed.stats);
          applyCharts(parsed.charts, false);
          applyOccupancyCaption(parsed.range, parsed.charts?.occupancy?.granularity || 'day');
          bookingsCache = parsed.bookings || [];
          applyBookingFilters();
        } catch {
          sessionStorage.removeItem(cacheKey);
        }
      }

      if (window.__dashboardFetchController) {
        window.__dashboardFetchController.abort();
      }

      window.__dashboardFetchController = new AbortController();
      window.__dashboardFetchInFlight = true;

      const response = await fetch(url.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        signal: window.__dashboardFetchController.signal,
      }).catch(() => null);

      window.__dashboardFetchInFlight = false;
      if (!response || !response.ok) return;
      const payload = await response.json();
      sessionStorage.setItem(cacheKey, JSON.stringify(payload));
      if (!startDate || !endDate) {
        startInput.value = payload.range.start_date;
        endInput.value = payload.range.end_date;
        presetSelect.value = preset ?? '';
        updateUrl(payload.range.start_date, payload.range.end_date, preset);
      }
      applyStats(payload.stats);
      applyCharts(payload.charts, false);
      applyOccupancyCaption(payload.range, payload.charts?.occupancy?.granularity || 'day');
      bookingsCache = payload.bookings || [];
      applyBookingFilters();
    };

    if (!root.dataset.bound) {
      root.dataset.bound = 'true';

      presetSelect.addEventListener('change', () => {
        const preset = presetSelect.value;
        const [startDate, endDate] = resolveRange(preset);

        if (!startDate || !endDate) {
          startInput.value = '';
          endInput.value = '';
          updateUrl(null, null, preset);
          fetchData(null, null, preset, false);
          return;
        }

        startInput.value = startDate;
        endInput.value = endDate;
        updateUrl(startDate, endDate, preset);
        fetchData(startDate, endDate, preset, false);
      });

      const handleCustomChange = () => {
        if (!startInput.value || !endInput.value) return;
        presetSelect.value = 'custom';
        updateUrl(startInput.value, endInput.value, 'custom');
        fetchData(startInput.value, endInput.value, 'custom', false);
      };

      startInput.addEventListener('change', handleCustomChange);
      endInput.addEventListener('change', handleCustomChange);

      bookingSearch?.addEventListener('input', applyBookingFilters);
      bookingFilter?.addEventListener('change', applyBookingFilters);

      resetButton.addEventListener('click', () => {
        presetSelect.value = 'all';
        startInput.value = '';
        endInput.value = '';
        updateUrl(null, null, 'all');
        fetchData(null, null, 'all', false);
      });
    }

    const url = new URL(window.location.href);
    const urlStart = url.searchParams.get('start_date') || defaultStart || '';
    const urlEnd = url.searchParams.get('end_date') || defaultEnd || '';
    const urlPreset = url.searchParams.get('preset') || '';

    if (urlStart && urlEnd) {
      startInput.value = urlStart;
      endInput.value = urlEnd;
      presetSelect.value = urlPreset || 'custom';
      updateUrl(urlStart, urlEnd, urlPreset || 'custom');
      fetchData(urlStart, urlEnd, urlPreset || 'custom');
    } else {
      startInput.value = '';
      endInput.value = '';
      presetSelect.value = urlPreset || 'all';
      updateUrl(null, null, urlPreset || 'all');
      fetchData(null, null, urlPreset || 'all');
    }
  };
</script>
@endpush

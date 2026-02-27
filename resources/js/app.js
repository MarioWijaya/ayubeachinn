import flatpickr from "flatpickr";
import "flatpickr/dist/flatpickr.min.css";

const loadFullCalendar = () => {
  if (window.__fullcalendarLoader) return window.__fullcalendarLoader;

  window.__fullcalendarLoader = Promise.all([
    import("@fullcalendar/core"),
    import("@fullcalendar/daygrid"),
    import("@fullcalendar/interaction"),
    import("@fullcalendar/core/locales/id"),
  ]).then(([core, dayGrid, interaction, locale]) => ({
    Calendar: core.Calendar,
    dayGridPlugin: dayGrid.default,
    interactionPlugin: interaction.default,
    idLocale: locale.default,
  }));

  return window.__fullcalendarLoader;
};

window.flatpickr = flatpickr;

// =================== LUCIDE ===================
const lucideCdnSources = [
  "https://unpkg.com/lucide@0.563.0/dist/umd/lucide.min.js",
  "https://cdn.jsdelivr.net/npm/lucide@0.563.0/dist/umd/lucide.min.js",
];

let lucideLoadPromise = null;

const ensureLucideLoaded = () => {
  if (window.lucide?.createIcons) {
    return Promise.resolve(true);
  }

  if (lucideLoadPromise) {
    return lucideLoadPromise;
  }

  lucideLoadPromise = new Promise((resolve) => {
    const existingScript = document.querySelector(
      'script[data-lucide-loader="fallback"]',
    );

    if (existingScript) {
      const checkExisting = () => resolve(Boolean(window.lucide?.createIcons));
      existingScript.addEventListener("load", checkExisting, { once: true });
      existingScript.addEventListener("error", () => resolve(false), {
        once: true,
      });
      return;
    }

    const tryLoad = (index) => {
      if (index >= lucideCdnSources.length) {
        resolve(false);
        return;
      }

      const script = document.createElement("script");
      script.src = lucideCdnSources[index];
      script.async = true;
      script.defer = true;
      script.dataset.lucideLoader = "fallback";

      script.onload = () => resolve(Boolean(window.lucide?.createIcons));
      script.onerror = () => {
        script.remove();
        tryLoad(index + 1);
      };

      document.head.appendChild(script);
    };

    tryLoad(0);
  }).finally(() => {
    lucideLoadPromise = null;
  });

  return lucideLoadPromise;
};

const refreshIcons = () => {
  const lucide = window.lucide ?? window.lucide?.default;
  if (!lucide) {
    return false;
  }

  try {
    if (typeof lucide.createIcons === "function") {
      lucide.createIcons();
      return true;
    }
  } catch {
    // noop
  }

  try {
    if (typeof lucide.replace === "function") {
      lucide.replace();
      return true;
    }
  } catch {
    // noop
  }

  return false;
};

const scheduleLucideRefresh = () => {
  const runRefresh = () => {
    refreshIcons();
    requestAnimationFrame(refreshIcons);
    setTimeout(refreshIcons, 50);
    setTimeout(refreshIcons, 250);
  };

  if (refreshIcons()) {
    runRefresh();
    return;
  }

  ensureLucideLoaded().then((loaded) => {
    if (!loaded) {
      return;
    }

    runRefresh();
  });
};

// =================== ACTIVE SIDEBAR ===================
const updateCurrentLinks = () => {
  const currentPath = window.location.pathname.replace(/\/+$/, "") || "/";

  document.querySelectorAll('a[wire\\:navigate]').forEach((link) => {
    const href = link.getAttribute("href");
    const activePrefixes = (link.dataset.activePrefixes || "")
      .split("|")
      .map((item) => item.trim())
      .filter(Boolean);

    if (activePrefixes.length > 0) {
      const isActive = activePrefixes.some(
        (prefix) => currentPath === prefix || currentPath.startsWith(prefix + "/"),
      );
      if (isActive) link.setAttribute("data-current", "true");
      else link.removeAttribute("data-current");
      return;
    }

    if (!href) return;

    const linkPath =
      new URL(href, window.location.origin).pathname.replace(/\/+$/, "") || "/";

    const isActive =
      currentPath === linkPath ||
      (linkPath !== "/" && currentPath.startsWith(linkPath + "/"));

    if (isActive) link.setAttribute("data-current", "true");
    else link.removeAttribute("data-current");
  });
};

// =================== OBSERVER (optional) ===================
const initIconObserver = () => {
  if (window.__iconObserverInitialized) return;
  window.__iconObserverInitialized = true;

  let scheduled = false;
  const observer = new MutationObserver(() => {
    if (scheduled) return;
    scheduled = true;

    requestAnimationFrame(() => {
      scheduled = false;
      scheduleLucideRefresh();
    });
  });

  observer.observe(document.body, { childList: true, subtree: true });
};

// ==========================================================
// FULLCALENDAR: SUMMARY PER HARI + DETAIL MODAL
// ==========================================================

/**
 * Cari instance Livewire dari elemen calendar
 */
const getLivewireInstanceFromCalendarEl = (calendarEl) => {
  const root = calendarEl.closest("[wire\\:id]");
  const id = root?.getAttribute("wire:id");
  if (!id) return null;

  if (window.Livewire?.find) return window.Livewire.find(id);
  return null;
};

const isoDate = (date) => {
  const local = new Date(date.getTime() - date.getTimezoneOffset() * 60000);
  return local.toISOString().slice(0, 10);
};

const ensureDaysObject = () => {
  if (!window.__calendarDays) window.__calendarDays = {};
  if (typeof window.__totalRooms !== "number") window.__totalRooms = 0;
};

const isMobileCalendar = () =>
  window.matchMedia && window.matchMedia("(max-width: 480px)").matches;

const hydrateCalendarDataFromEl = (el) => {
  if (!el) return;

  try {
    const days = el.dataset?.days ? JSON.parse(el.dataset.days) : null;
    if (days) window.__calendarDays = days;
  } catch {
    window.__calendarDays = window.__calendarDays || {};
  }

  const total = Number(el.dataset?.total ?? window.__totalRooms ?? 0);
  window.__totalRooms = Number.isNaN(total) ? 0 : total;
};

/**
 * Render ringkasan ke setiap cell kalender
 */
const renderDaySummary = (arg) => {
  ensureDaysObject();

  const dateStr = arg.dateStr ?? isoDate(arg.date);
  const data = window.__calendarDays?.[dateStr];

  const eventsEl = arg.el.querySelector(".fc-daygrid-day-events");
  if (!eventsEl) return;

  eventsEl.innerHTML = "";
  if (!data) return;

  const total = Number(data.total_rooms ?? window.__totalRooms ?? 0);
  const kosong = data.empty_count ?? 0;
  const terisi = data.occupied_count ?? 0;
  const mobile = isMobileCalendar();

  const wrap = document.createElement("div");
  const zeroClass = terisi === 0 ? " is-zero" : "";

  if (mobile) {
    wrap.className = "day-card day-card--mobile";
    wrap.innerHTML = `
      <div class="day-card__mobile-badge${zeroClass}">${terisi}</div>
    `;
  } else {
    wrap.className = "day-card day-card--desktop";
    wrap.innerHTML = `
      <div class="day-card__top">
        <div class="day-card__title">Total</div>
        <div class="day-card__value">${total}</div>
      </div>

      <div class="day-card__chips">
        <span class="chip chip--empty">
          <span class="chip__label chip__label--full">Kosong</span>
          <span class="chip__label chip__label--short">Ksg</span>
          <span class="chip__value">${kosong}</span>
        </span>

        <span class="chip chip--occ">
          <span class="chip__label chip__label--full">Terisi</span>
          <span class="chip__label chip__label--short">Isi</span>
          <span class="chip__value">${terisi}</span>
        </span>
      </div>
    `;
  }

  wrap.addEventListener("click", () => {
    window.openDayDetail?.(dateStr);
  });

  eventsEl.appendChild(wrap);
};

const rerenderDaySummaries = () => {
  const calendarEl = document.getElementById("calendar");
  if (!calendarEl) return;

  calendarEl
    .querySelectorAll(".fc-daygrid-day[data-date]")
    .forEach((cell) => {
      const dateStr = cell.getAttribute("data-date");
      if (!dateStr) return;
      renderDaySummary({ el: cell, dateStr });
    });
};

/**
 * Init kalender availability
 */
window.initAvailabilityCalendar = async () => {
  const el = document.getElementById("calendar");
  if (!el) return;

  // kalau element berubah akibat navigate, destroy instance lama
  if (window.__fcAvailability && window.__fcAvailabilityEl !== el) {
    window.__fcAvailability.destroy();
    window.__fcAvailability = null;
  }

  hydrateCalendarDataFromEl(el);

  // init hanya sekali per element
  if (window.__fcAvailability) return;

  ensureDaysObject();

  const lw = getLivewireInstanceFromCalendarEl(el);

  const { Calendar, dayGridPlugin, interactionPlugin, idLocale } =
    await loadFullCalendar();

  window.__fcAvailability = new Calendar(el, {
    plugins: [dayGridPlugin, interactionPlugin],
    initialView: "dayGridMonth",
    height: "auto",
    locale: idLocale,
    firstDay: 1,
    buttonText: {
      today: "Hari ini",
      month: "Bulan",
      week: "Minggu",
      day: "Hari",
      list: "Daftar",
    },

    events: [],

    datesSet(info) {
      const start = info.startStr.slice(0, 10);
      const end = info.endStr.slice(0, 10);

      if (lw?.call) lw.call("loadRange", start, end);
      rerenderDaySummaries();
    },

    dayCellDidMount(arg) {
      renderDaySummary(arg);
    },

    dateClick(info) {
      window.openDayDetail?.(info.dateStr);
    },
  });

  window.__fcAvailability.render();
  window.__fcAvailabilityEl = el;
};

const bootAvailabilityCalendar = () => {
  if (!document.getElementById("calendar")) return;
  window.initAvailabilityCalendar?.();
};

window.updateAvailabilityCalendarDays = (days = {}, totalRooms = 0) => {
  window.__calendarDays = days || {};
  window.__totalRooms = Number(totalRooms || 0);

  if (window.__fcAvailability) {
    window.__fcAvailability.render();
    rerenderDaySummaries();
  }
};

const initCalendarResizeListener = () => {
  if (window.__calendarResizeInit) return;
  window.__calendarResizeInit = true;

  let timer = null;
  window.addEventListener("resize", () => {
    if (!window.__fcAvailability) return;
    if (timer) window.clearTimeout(timer);
    timer = window.setTimeout(() => {
      window.__fcAvailability.render();
      rerenderDaySummaries();
    }, 200);
  });
};

// =================== LIVEWIRE LISTENER ===================
const initLivewireListeners = () => {
  if (window.__calendarDaysListenerInitialized) return;
  window.__calendarDaysListenerInitialized = true;

  if (window.Livewire?.on) {
    window.Livewire.on("calendar-days-updated", ({ days, totalRooms }) => {
      window.updateAvailabilityCalendarDays(days, totalRooms);
    });
  }
};

// =================== SCROLL FIX (pagination/filter) ===================
const initScrollLock = () => {
  if (window.__scrollLockInit) return;
  window.__scrollLockInit = true;

  // CASE A: request Livewire biasa (filter, paginate yang wire:click, dll)
  if (window.Livewire?.hook) {
    window.Livewire.hook("message.sent", () => {
      window.__lwScrollY = window.scrollY;
    });

    window.Livewire.hook("message.processed", () => {
      if (typeof window.__lwScrollY === "number") {
        window.scrollTo({ top: window.__lwScrollY, behavior: "instant" });
      }
      scheduleLucideRefresh();
    });
  }

  // CASE B: pagination link (Laravel links) / navigate
  document.addEventListener("click", (e) => {
    const a = e.target.closest(".pagination a");
    if (!a) return;
    window.__lwScrollY = window.scrollY;
  });

  document.addEventListener("livewire:navigated", () => {
    if (typeof window.__lwScrollY === "number") {
      window.scrollTo({ top: window.__lwScrollY, behavior: "instant" });
    }
  });
};

// =================== REALTIME CLOCK ===================
window.initRealtimeClock = () => {
  if (window.__rtClockStarted) return;
  window.__rtClockStarted = true;

  const update = () => {
    const el =
      document.querySelector("[data-realtime-clock]") ||
      document.getElementById("realtimeClock");
    if (!el) return;

    const tz = el.dataset.tz || "Asia/Makassar";
    const timeFmt = new Intl.DateTimeFormat("id-ID", {
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      hour12: false,
      timeZone: tz,
    });

    const dateFmt = new Intl.DateTimeFormat("id-ID", {
      weekday: "long",
      day: "2-digit",
      month: "short",
      year: "numeric",
      timeZone: tz,
    });

    const time = timeFmt.format(new Date()).replaceAll(":", ".");
    const date = dateFmt.format(new Date()).replace(".", "");
    el.textContent = `${time} • ${date}`;
  };

  update();
  window.__clockInterval = setInterval(update, 1000);
};

// =================== BOOT UI ===================
const bootUi = () => {
  scheduleLucideRefresh();
  initIconObserver();
  updateCurrentLinks();
  setTimeout(updateCurrentLinks, 50);
  initLivewireListeners();
  bootAvailabilityCalendar();
  initCalendarResizeListener();
  initScrollLock();
  window.initRealtimeClock?.();
};

// =================== EVENTS ===================
document.addEventListener("DOMContentLoaded", () => {
  scheduleLucideRefresh();
  updateCurrentLinks();
  setTimeout(updateCurrentLinks, 50);
  window.initRealtimeClock?.();
});

document.addEventListener("livewire:initialized", () => {
  bootUi();
});

document.addEventListener("livewire:navigated", () => {
  bootUi();
});

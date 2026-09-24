import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import { Calendar } from 'fullcalendar';
import themePlugin from 'fullcalendar/themes/pulse';
import dayGridPlugin from 'fullcalendar/daygrid';
import listPlugin from 'fullcalendar/list';

import 'fullcalendar/skeleton.css';
import 'fullcalendar/themes/pulse/theme.css';
import 'fullcalendar/themes/pulse/palettes/blue.css';

import '@fontsource/inter/400.css';
import '@fontsource/inter/600.css';
import '@fontsource/inter/700.css';

window.Alpine = Alpine;
window.ApexCharts = ApexCharts;
window.FullCalendar = { Calendar };

Alpine.data('sidebar', () => ({
    open: new Set(),

    init() {
        this.$el.querySelectorAll('[data-open]').forEach((el) => {
            if (el.dataset.open === 'true') {
                this.open.add(el.dataset.menu);
            }
        });
    },

    toggle(key) {
        if (this.open.has(key)) {
            this.open.delete(key);
        } else {
            this.open.add(key);
        }
    },

    isOpen(key) {
        return this.open.has(key);
    },
}));

Alpine.data('tabs', (initial = '') => ({
    active: initial,

    isActive(key) {
        return this.active === key;
    },

    activate(key) {
        this.active = key;
    },
}));

Alpine.data('chart', (sourceId) => ({
    init() {
        const raw = document.getElementById(sourceId);
        if (!raw) {
            return;
        }
        // Js::from() emits a JS expression ("JSON.parse('...')"), not raw JSON.
        const options = new Function(`return (${raw.textContent})`)();
        new ApexCharts(this.$refs.container, options).render();
    },
}));

Alpine.data('fullCalendar', (sourceId, extraEventsId) => ({
    init() {
        const raw = document.getElementById(sourceId);
        if (!raw) {
            return;
        }
        const read = (id) => {
            const el = document.getElementById(id);
            return el ? new Function(`return (${el.textContent})`)() : [];
        };
        const events = read(sourceId);
        let extraEvents = [];
        if (extraEventsId) {
            extraEvents = read(extraEventsId);
        }
        const calendar = new Calendar(this.$refs.container, {
            plugins: [themePlugin, dayGridPlugin, listPlugin],
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth',
            },
            height: 'auto',
            events: [...events, ...extraEvents],
            eventDisplay: 'block',

            // Stable class hooks so app.css can style the calendar with the app's design tokens
            viewClass: 'fc-view-app',
            toolbarClass: 'fc-toolbar-app',
            toolbarSectionClass: 'fc-toolbar-section-app',
            toolbarTitleClass: 'fc-title-app',
            buttonClass: 'fc-btn-app',
            buttonGroupClass: 'fc-btngroup-app',
            tableHeaderClass: 'fc-tableheader-app',
            tableBodyClass: 'fc-tablebody-app',
            dayHeaderClass: 'fc-dayheader-app',
            dayCellClass: 'fc-daycell-app',
            rowEventClass: 'fc-event-app',
            listDayClass: 'fc-listday-app',
            listDayHeaderClass: 'fc-listdayheader-app',
            listItemEventClass: 'fc-listitem-app',
            noEventsClass: 'fc-noevents-app',
        });
        calendar.render();
    },
}));

Alpine.data('wbpSearch', (searchUrl) => ({
    query: '',
    results: [],
    selected: null,
    open: false,
    loading: false,
    error: '',
    timer: null,

    search() {
        clearTimeout(this.timer);

        if (this.selected) {
            return;
        }

        this.timer = setTimeout(async () => {
            const term = this.query.trim();

            if (term.length < 2) {
                this.results = [];
                this.open = false;
                return;
            }

            this.loading = true;
            this.error = '';

            try {
                const res = await fetch(`${searchUrl}?q=${encodeURIComponent(term)}`, {
                    headers: { Accept: 'application/json' },
                });
                const data = await res.json();
                this.results = data.results || [];
                this.open = true;
            } catch (e) {
                this.error = 'Gagal memuat data WBP.';
                this.results = [];
                this.open = false;
            } finally {
                this.loading = false;
            }
        }, 300);
    },

    select(item) {
        this.selected = item;
        this.query = item.nama;
        this.results = [];
        this.open = false;
    },

    clear() {
        this.selected = null;
        this.query = '';
        this.results = [];
        this.open = false;
    },
}));

const blokWbpTable = (payload) => ({
    wbps: payload.wbps,
    blokList: payload.blokList,
    setStatusUrl: payload.setStatusUrl,
    categoryId: payload.categoryId,
    tanggal: payload.tanggal,

    blokQuery: '',
    blokResults: [],
    blokOpen: false,
    selectedBlok: payload.selectedBlok || '',
    wbpQuery: payload.wbpQuery || '',

    filtered: [],
    selesai: 0,
    proses: 0,
    total: 0,
    persen: 0,
    distribusi: [],

    init() {
        this.blokResults = [...this.blokList];
        this.recount();
    },

    recount() {
        const term = this.wbpQuery.trim().toLowerCase();

        this.filtered = this.wbps.filter((w) => {
            if (this.selectedBlok && w.blok_kamar !== this.selectedBlok) {
                return false;
            }
            if (!term) {
                return true;
            }

            return w.nama.toLowerCase().includes(term)
                || w.no_register.toLowerCase().includes(term);
        });

        this.selesai = this.filtered.filter((w) => w.fulfilled).length;
        this.total = this.filtered.length;
        this.proses = this.total - this.selesai;
        this.persen = this.total > 0
            ? Math.round((this.selesai / this.total) * 100)
            : 0;

        const counts = {};

        this.filtered.forEach((w) => {
            if (w.status) {
                counts[w.status] = (counts[w.status] || 0) + 1;
            }
        });

        this.distribusi = Object.entries(counts).sort((a, b) => b[1] - a[1]);
    },

    filterBlok() {
        const term = this.blokQuery.trim().toLowerCase();

        this.blokResults = term
            ? this.blokList.filter((b) => b.toLowerCase().includes(term))
            : [...this.blokList];
    },

    selectBlok(blok) {
        this.selectedBlok = blok;
        this.blokQuery = '';
        this.blokOpen = false;
        this.recount();
        this.syncUrlState();
    },

    clearBlok() {
        this.selectedBlok = '';
        this.blokQuery = '';
        this.blokResults = [...this.blokList];
        this.recount();
        this.syncUrlState();
    },

    onWbpQuery() {
        this.recount();
        this.syncUrlState();
    },

    // Sinkronkan filter blok/WBP ke URL dan input tersembunyi header agar
    // filter tetap bertahan saat petugas mengganti tanggal/tab.
    syncUrlState() {
        const params = new URLSearchParams(location.search);

        const setParam = (key, value) => {
            if (value) {
                params.set(key, value);
            } else {
                params.delete(key);
            }
        };

        setParam('blok', this.selectedBlok);
        setParam('q', this.wbpQuery.trim());

        history.replaceState(null, '', `${location.pathname}?${params.toString()}`);

        const blokInput = document.querySelector('input[name="blok"]');
        const qInput = document.querySelector('input[name="q"]');

        if (blokInput) {
            blokInput.value = this.selectedBlok;
        }
        if (qInput) {
            qInput.value = this.wbpQuery.trim();
        }

        document.querySelectorAll('[data-tab-link]').forEach((a) => {
            const href = new URL(a.href);
            setParam('blok', this.selectedBlok);
            setParam('q', this.wbpQuery.trim());
            href.search = params.toString();
            a.href = href.toString();
        });
    },
});

const blokWbpForm = (searchUrl, statusOptions, blokList) => ({
    blokQuery: '',
    blokResults: [...blokList],
    blokOpen: false,
    selectedBlok: '',

    wbpQuery: '',
    wbpResults: [],
    wbpOpen: false,
    selectedWbp: null,
    loading: false,
    error: '',
    timer: null,

    statusOptions,
    status: statusOptions[0] || 'Proses',
    blokError: false,
    wbpError: false,

    filterBlok() {
        const term = this.blokQuery.trim().toLowerCase();

        this.blokResults = term
            ? blokList.filter((b) => b.toLowerCase().includes(term))
            : [...blokList];
    },

    selectBlok(blok) {
        this.selectedBlok = blok;
        this.blokQuery = '';
        this.blokOpen = false;
        this.blokError = false;
        this.wbpQuery = '';
        this.wbpResults = [];
        this.wbpOpen = false;
        this.selectedWbp = null;
        this.wbpError = false;
    },

    clearBlok() {
        this.selectedBlok = '';
        this.blokQuery = '';
        this.blokResults = [...blokList];
        this.wbpQuery = '';
        this.wbpResults = [];
        this.selectedWbp = null;
        this.wbpError = false;
    },

    searchWbp() {
        clearTimeout(this.timer);

        if (!this.selectedBlok) {
            this.wbpResults = [];
            this.wbpOpen = false;

            return;
        }

        if (this.selectedWbp) {
            return;
        }

        this.timer = setTimeout(async () => {
            const term = this.wbpQuery.trim();

            if (term.length < 2) {
                this.wbpResults = [];
                this.wbpOpen = false;

                return;
            }

            this.loading = true;
            this.error = '';

            try {
                const res = await fetch(`${searchUrl}?q=${encodeURIComponent(term)}&blok=${encodeURIComponent(this.selectedBlok)}`, {
                    headers: { Accept: 'application/json' },
                });
                const data = await res.json();
                this.wbpResults = data.results || [];
                this.wbpOpen = true;
            } catch (e) {
                this.error = 'Gagal memuat data WBP.';
                this.wbpResults = [];
                this.wbpOpen = false;
            } finally {
                this.loading = false;
            }
        }, 300);
    },

    selectWbp(item) {
        this.selectedWbp = item;
        this.wbpQuery = item.nama;
        this.wbpResults = [];
        this.wbpOpen = false;
        this.wbpError = false;
    },

    clearWbp() {
        this.selectedWbp = null;
        this.wbpQuery = '';
        this.wbpResults = [];
        this.wbpOpen = false;
    },

    submit() {
        this.blokError = !this.selectedBlok;
        this.wbpError = !this.selectedWbp;

        if (this.blokError || this.wbpError) {
            return;
        }

        this.$refs.form.submit();
    },
});

Alpine.data('laundryTable', blokWbpTable);
Alpine.data('laundryForm', blokWbpForm);
Alpine.data('perawatanTable', blokWbpTable);
Alpine.data('perawatanForm', blokWbpForm);
Alpine.data('kesehatanTable', blokWbpTable);
Alpine.data('kesehatanForm', blokWbpForm);

Alpine.data('editSesi', (statusOptions) => ({
    show: false,
    action: '',
    statusOptions,
    form: {
        status: 'Selesai',
        tanggal: '',
        waktu_mulai: '',
        keterangan: '',
    },

    open({ action, status, tanggal, waktu_mulai, keterangan }) {
        this.action = action;
        this.form.status = status;
        this.form.tanggal = tanggal;
        this.form.waktu_mulai = waktu_mulai || '';
        this.form.keterangan = keterangan || '';
        this.show = true;
    },

    close() {
        this.show = false;
    },
}));

Alpine.data('confirmModal', () => ({
    show: false,
    action: '',

    open(action) {
        this.action = action;
        this.show = true;
    },

    close() {
        this.show = false;
    },

    submit() {
        this.$refs.form.submit();
    },
}));

Alpine.data('clock', () => ({
    time: '',

    init() {
        this.update();
        setInterval(() => this.update(), 1000);
    },

    update() {
        this.time = new Date().toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
        });
    },
}));

Alpine.store('mobileNav', {
    open: false,

    toggle() {
        this.open = !this.open;
        document.body.classList.toggle('overflow-hidden', this.open);
    },

    close() {
        this.open = false;
        document.body.classList.remove('overflow-hidden');
    },
});

window.matchMedia('(min-width: 1024px)').addEventListener('change', (e) => {
    if (e.matches) {
        Alpine.store('mobileNav').close();
    }
});

Alpine.start();

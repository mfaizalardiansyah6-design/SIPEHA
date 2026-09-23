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

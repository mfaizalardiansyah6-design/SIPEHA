import Alpine from 'alpinejs';
import ApexCharts from 'apexcharts';
import { Calendar } from 'fullcalendar';

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
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,listMonth',
            },
            height: 'auto',
            events: [...events, ...extraEvents],
            eventDisplay: 'block',
        });
        calendar.render();
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

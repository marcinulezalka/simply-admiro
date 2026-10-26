/**
 * Data i kalendarz w stopce layoutu Workspace (x-admiro::app.calendar): [data-ss-clock] odświeża co minutę dzień
 * tygodnia ([data-ss-clock-weekday]), datę ([data-ss-clock-date]: dzień, miesiąc, rok) w języku strony (lang dokumentu,
 * Intl.DateTimeFormat) i numer tygodnia ISO ([data-ss-clock-week], liczba w miejscu pierwszej liczby tekstu), więc po
 * północy pokazuje nowy dzień; w panelu [data-ss-calendar] klik w dzień [data-ss-calendar-day] zaznacza go
 * (aria-pressed) i pokazuje w podglądzie jego datę (data-label) i opis (data-note: dzień wolny albo brak wydarzeń),
 * a przycisk [data-ss-calendar-close] zamyka panel (przycisk w stopce jest wtedy pod panelem).
 * Bez skryptu zostaje data z chwili wczytania strony oraz podgląd dnia dzisiejszego. Osadzany inline
 * z nonce CSP (x-admiro::scripts).
 */
(() => {
    const locale = document.documentElement.lang || undefined;
    const weekdayFormat = new Intl.DateTimeFormat(locale, { weekday: 'short' });
    const dateFormat = new Intl.DateTimeFormat(locale, { day: 'numeric', month: 'short', year: 'numeric' });

    const isoWeek = (date) => {
        const day = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
        const weekday = day.getUTCDay() || 7;
        day.setUTCDate(day.getUTCDate() + 4 - weekday);
        const yearStart = new Date(Date.UTC(day.getUTCFullYear(), 0, 1));

        return Math.ceil(((day - yearStart) / 86400000 + 1) / 7);
    };

    const tick = (clock) => {
        const now = new Date();
        const weekday = clock.querySelector('[data-ss-clock-weekday]');
        const date = clock.querySelector('[data-ss-clock-date]');
        const week = clock.querySelector('[data-ss-clock-week]');

        if (weekday) {
            weekday.textContent = weekdayFormat.format(now).replace(/\.$/, '');
        }

        if (date) {
            date.textContent = dateFormat.format(now);
        }

        if (week) {
            week.textContent = week.textContent.replace(/\d+/, String(isoWeek(now)));
        }
    };

    const initCalendar = (calendar) => {
        const label = calendar.querySelector('[data-ss-calendar-label]');
        const note = calendar.querySelector('[data-ss-calendar-note]');
        const days = [...calendar.querySelectorAll('[data-ss-calendar-day]')];
        const close = calendar.querySelector('[data-ss-calendar-close]');

        if (close) {
            close.hidden = false;
            close.addEventListener('click', () => {
                const panel = calendar.closest('details');

                if (panel) {
                    panel.open = false;
                    panel.querySelector('summary')?.focus();
                }
            });
        }

        days.forEach((day) => day.addEventListener('click', () => {
            days.forEach((other) => other.setAttribute('aria-pressed', String(other === day)));

            if (label) {
                const text = day.dataset.label ?? '';
                label.textContent = text.charAt(0).toUpperCase() + text.slice(1);
            }

            if (note) {
                note.textContent = day.dataset.note ?? '';
            }
        }));
    };

    const start = () => {
        document.querySelectorAll('[data-ss-clock]').forEach((clock) => {
            tick(clock);
            window.setInterval(() => tick(clock), 60000);
        });
        document.querySelectorAll('[data-ss-calendar]').forEach(initCalendar);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

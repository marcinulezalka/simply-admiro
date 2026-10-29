/**
 * Data i kalendarz w stopce layoutu Workspace (x-admiro::app.calendar): [data-ss-clock] na początku każdej minuty
 * odświeża zegar w czasie przeglądarki — godzinę [data-ss-clock-time] („10:42”), datę [data-ss-clock-day]
 * („27.10.2026”, dzień i miesiąc dwucyfrowo) i atrybut datetime [data-ss-clock-date]; w panelu [data-ss-calendar] klik w dzień
 * [data-ss-calendar-day] zaznacza go (aria-pressed) i pokazuje w podglądzie jego datę (data-label) i opis (data-note: dzień wolny albo brak wydarzeń),
 * a przycisk [data-ss-calendar-close] zamyka panel (przycisk w stopce jest wtedy pod panelem).
 * Bez skryptu zostaje data z chwili wczytania strony oraz podgląd dnia dzisiejszego. Osadzany inline
 * z nonce CSP (x-admiro::scripts).
 */
(() => {
    const pad = (number) => String(number).padStart(2, '0');

    const tick = (clock) => {
        const now = new Date();
        const date = clock.querySelector('[data-ss-clock-date]');
        const time = clock.querySelector('[data-ss-clock-time]');
        const day = clock.querySelector('[data-ss-clock-day]');

        if (time) {
            time.textContent = `${pad(now.getHours())}:${pad(now.getMinutes())}`;
        }

        if (day) {
            day.textContent = `${pad(now.getDate())}.${pad(now.getMonth() + 1)}.${now.getFullYear()}`;
        }

        if (date) {
            date.setAttribute('datetime', `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`);
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
            window.setTimeout(() => {
                tick(clock);
                window.setInterval(() => tick(clock), 60000);
            }, 60000 - (Date.now() % 60000));
        });
        document.querySelectorAll('[data-ss-calendar]').forEach(initCalendar);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

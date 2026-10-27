/**
 * Wyszukiwanie na żywo (form[data-ss-live-search="<id wyników>"], np. języki w kreatorze onboardingu): pisanie w polu
 * (z opóźnieniem) pobiera stronę formularza z nowym zapytaniem i podmienia w niej tylko element wyników o podanym id,
 * a adres w pasku przeglądarki dostaje to samo zapytanie (history.replaceState), więc odświeżenie i powrót po zapisie
 * przełącznika pokazują te same wyniki. Wysłanie formularza (Enter) też nie przeładowuje strony. Bez skryptu formularz
 * działa zwykłym GET. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const DELAY = 250;

    const init = (form) => {
        const results = document.getElementById(form.dataset.ssLiveSearch ?? '');

        if (! results) {
            return;
        }

        let timer = 0;
        let controller = null;

        const refresh = async () => {
            const url = new URL(form.action, window.location.href);
            new FormData(form).forEach((value, key) => {
                if (typeof value === 'string' && value.trim() !== '') {
                    url.searchParams.append(key, value.trim());
                }
            });

            controller?.abort();
            controller = new AbortController();

            try {
                const response = await fetch(url, { headers: { Accept: 'text/html' }, signal: controller.signal });

                if (! response.ok) {
                    return;
                }

                const page = new DOMParser().parseFromString(await response.text(), 'text/html');
                const fresh = page.getElementById(results.id);

                if (fresh) {
                    results.innerHTML = fresh.innerHTML;
                    window.history.replaceState(null, '', url);
                }
            } catch (error) {
                if (error.name !== 'AbortError') {
                    form.submit();
                }
            }
        };

        form.addEventListener('input', () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(refresh, DELAY);
        });
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            window.clearTimeout(timer);
            refresh();
        });
    };

    const start = () => document.querySelectorAll('form[data-ss-live-search]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

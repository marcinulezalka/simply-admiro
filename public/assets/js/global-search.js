/**
 * Podpowiedzi globalnej wyszukiwarki (x-admiro::app.layout, form[data-ss-search-suggest]): po wpisaniu co najmniej
 * dwóch znaków (z opóźnieniem) pobiera fragment HTML z adresu podpowiedzi z polami formularza (tekst i zaznaczone
 * zakresy `[data-ss-search-scope]`) i wstawia go do listy [data-ss-search-suggestions] pod polem. Zmiana zakresu
 * pobiera podpowiedzi ponownie. Strzałki w dół i w górę przechodzą między opcjami (aria-activedescendant), Enter
 * otwiera zaznaczoną opcję, a bez zaznaczenia wysyła formularz (strona wszystkich wyników); Esc i klik obok
 * zamykają listę. Starsze odpowiedzi nie nadpisują nowszych. Bez skryptu zostaje zwykły formularz GET. Osadzany
 * inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const MIN_LENGTH = 2;
    const DELAY = 200;

    const init = (form) => {
        const input = form.querySelector('[data-ss-search]');
        const list = form.querySelector('[data-ss-search-suggestions]');
        const url = form.dataset.ssSearchSuggest;

        if (!(input instanceof HTMLInputElement) || list === null || !url) {
            return;
        }

        let timer = null;
        let request = 0;
        let active = -1;

        const options = () => [...list.querySelectorAll('[data-ss-search-option]')];

        const close = () => {
            list.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            active = -1;
        };

        const highlight = (index) => {
            const items = options();
            active = items.length === 0 ? -1 : (index + items.length) % items.length;
            items.forEach((item, position) => item.setAttribute('aria-selected', String(position === active)));

            if (active >= 0) {
                input.setAttribute('aria-activedescendant', items[active].id);
                items[active].scrollIntoView({ block: 'nearest' });
            } else {
                input.removeAttribute('aria-activedescendant');
            }
        };

        const load = async () => {
            if (input.value.trim().length < MIN_LENGTH) {
                close();

                return;
            }

            const current = ++request;
            const query = new URLSearchParams(new FormData(form));

            try {
                const response = await fetch(`${url}?${query}`, { headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });

                if (!response.ok || current !== request) {
                    return;
                }

                list.innerHTML = await response.text();
                list.hidden = false;
                input.setAttribute('aria-expanded', 'true');
                active = -1;
                input.removeAttribute('aria-activedescendant');
            } catch {
                close();
            }
        };

        const schedule = () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(load, DELAY);
        };

        input.addEventListener('input', schedule);
        input.addEventListener('focus', () => {
            if (list.innerHTML.trim() !== '' && input.value.trim().length >= MIN_LENGTH) {
                list.hidden = false;
                input.setAttribute('aria-expanded', 'true');
            }
        });
        form.querySelectorAll('[data-ss-search-scope]').forEach((scope) => scope.addEventListener('change', schedule));

        input.addEventListener('keydown', (event) => {
            if (list.hidden) {
                return;
            }

            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                highlight(active + (event.key === 'ArrowDown' ? 1 : -1));
            } else if (event.key === 'Enter' && active >= 0) {
                event.preventDefault();
                options()[active]?.click();
            } else if (event.key === 'Escape') {
                event.preventDefault();
                close();
            }
        });

        document.addEventListener('click', (event) => {
            if (!form.contains(event.target)) {
                close();
            }
        });
    };

    const start = () => document.querySelectorAll('form[data-ss-search-suggest]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

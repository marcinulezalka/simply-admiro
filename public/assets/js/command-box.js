/**
 * Pole komend na dole menu błyskawicy ([data-ss-command], admiro::search.commands): wybór komendy z listy pod „/”
 * podmienia prefiks przy ikonie (np. „todo:”) i podpowiedź pola, zamyka listę i ustawia kursor w polu; otwarcie menu
 * błyskawicy też ustawia kursor w polu komend. Bez skryptu zaznaczona zostaje domyślna komenda. Osadzany inline
 * z nonce CSP (x-admiro::scripts).
 */
(() => {
    document.addEventListener('change', (event) => {
        const option = event.target;

        if (!(option instanceof HTMLInputElement) || !option.matches('[data-ss-command-option]')) {
            return;
        }

        const box = option.closest('[data-ss-command]');
        const current = box?.querySelector('[data-ss-command-current]');
        const input = box?.querySelector('[data-ss-command-input]');
        const picker = option.closest('details');

        if (current) {
            current.textContent = option.dataset.prefix ?? '';
        }

        if (input instanceof HTMLInputElement) {
            input.placeholder = option.dataset.placeholder ?? '';
            input.focus();
        }

        if (picker instanceof HTMLDetailsElement) {
            picker.open = false;
        }
    });

    document.addEventListener('toggle', (event) => {
        const menu = event.target;

        if (menu instanceof HTMLDetailsElement && menu.open && menu.matches('.ss-search__find')) {
            menu.querySelector('[data-ss-command-input]')?.focus();
        }
    }, true);
})();

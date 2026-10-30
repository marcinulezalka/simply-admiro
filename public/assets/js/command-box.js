/**
 * Pole komend na dole menu błyskawicy ([data-ss-command], admiro::search.commands): wybór komendy z listy pod „/”
 * podmienia prefiks przy ikonie (np. „todo:”) i podpowiedź pola, zamyka listę i ustawia kursor w polu; otwarcie menu
 * błyskawicy też ustawia kursor w polu komend. Wysłanie formularza komend (#ss-command-form) idzie w tle (fetch
 * z `Accept: application/json`): na czas zapisu pole ma aria-busy — strzałka Entera zmienia się w loader, a pole
 * i przycisk są zablokowane —, po zapisie pole jest puste z kursorem na kolejne zadanie, lista TODO w panelu
 * powiadomień (.ss-tasks) zostaje podmieniona na odświeżoną, a komunikat trafia do [data-ss-command-status]. Błąd
 * zapisu albo walidacji wysyła formularz zwykłą drogą (przeładowanie z komunikatem). Bez skryptu zaznaczona zostaje
 * domyślna komenda, a formularz wysyła się zwykłą drogą. Osadzany inline z nonce CSP (x-admiro::scripts).
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

    document.addEventListener('submit', async (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement) || form.id !== 'ss-command-form' || form.dataset.ssCommandFallback === '1') {
            return;
        }

        const input = document.querySelector('[data-ss-command-input]');
        const box = input?.closest('[data-ss-command]');
        const send = box?.querySelector('[data-ss-command-send]');
        const status = box?.querySelector('[data-ss-command-status]');

        if (!(input instanceof HTMLInputElement) || !box) {
            return;
        }

        event.preventDefault();

        if (box.getAttribute('aria-busy') === 'true' || input.value.trim() === '') {
            return;
        }

        const body = new FormData(form);
        const busy = (state) => {
            box.setAttribute('aria-busy', String(state));
            input.readOnly = state;

            if (send instanceof HTMLButtonElement) {
                send.disabled = state;
            }
        };

        busy(true);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body,
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) {
                throw new Error(String(response.status));
            }

            const result = await response.json();
            const tasks = document.querySelector('.ss-tasks');

            if (tasks && typeof result.tasks === 'string') {
                const template = document.createElement('template');
                template.innerHTML = result.tasks.trim();
                const fresh = template.content.firstElementChild;

                if (fresh) {
                    fresh.querySelectorAll('[data-ss-autosubmit-fallback]').forEach((button) => {
                        button.hidden = true;
                    });
                    tasks.replaceWith(fresh);
                }
            }

            if (status) {
                status.textContent = typeof result.message === 'string' ? result.message : '';
            }

            input.value = '';
            busy(false);
            input.focus();
        } catch {
            busy(false);
            form.dataset.ssCommandFallback = '1';
            form.requestSubmit();
        }
    });

    document.addEventListener('toggle', (event) => {
        const menu = event.target;

        if (menu instanceof HTMLDetailsElement && menu.open && menu.matches('.ss-search__find')) {
            menu.querySelector('[data-ss-command-input]')?.focus();
        }
    }, true);
})();

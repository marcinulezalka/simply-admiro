/**
 * Formularze zapisywane automatycznie (form[data-ss-autosave], np. dane organizacji): zmiana pola wysyła formularz w tle
 * (fetch z Accept: application/json, bez przeładowania strony; kolejne zmiany w krótkim odstępie — jeden zapis), a stan
 * pokazuje napis [data-ss-autosave-status] (teksty z data-ss-autosave-saving / -saved / -error, aria-live). Błędy
 * walidacji (422, `errors`) trafiają pod pola (aria-invalid, .ss-field__error), a następny udany zapis je czyści.
 * Przycisk zapasowy [data-ss-autosave-fallback] chowa się; bez skryptu zostaje i wysyła formularz ręcznie.
 * Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const DELAY = 400;

    const status = (form, state) => {
        const element = form.querySelector('[data-ss-autosave-status]');

        if (!element) {
            return;
        }

        element.dataset.state = state;
        element.textContent = element.dataset['ssAutosave' + state.charAt(0).toUpperCase() + state.slice(1)] ?? '';
    };

    const clearErrors = (form) => {
        form.querySelectorAll('[data-ss-autosave-error]').forEach((error) => error.remove());
        form.querySelectorAll('[aria-invalid="true"]').forEach((field) => field.removeAttribute('aria-invalid'));
    };

    const showErrors = (form, errors) => {
        Object.entries(errors).forEach(([name, messages]) => {
            const field = form.elements.namedItem(name);

            if (!(field instanceof HTMLElement)) {
                return;
            }

            field.setAttribute('aria-invalid', 'true');
            const container = field.closest('.ss-field') ?? field.parentElement;
            const error = document.createElement('p');
            error.className = 'ss-field__error';
            error.setAttribute('role', 'alert');
            error.dataset.ssAutosaveError = '';
            error.textContent = Array.isArray(messages) ? String(messages[0] ?? '') : String(messages);
            container?.append(error);
        });
    };

    const save = async (form) => {
        status(form, 'saving');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = await response.json().catch(() => ({}));

            clearErrors(form);

            if (response.status === 422 && result.errors && typeof result.errors === 'object') {
                showErrors(form, result.errors);
                status(form, 'error');

                return;
            }

            status(form, response.ok ? 'saved' : 'error');
        } catch {
            status(form, 'error');
        }
    };

    const start = () => {
        document.querySelectorAll('form[data-ss-autosave] [data-ss-autosave-fallback]').forEach((button) => {
            button.hidden = true;
        });

        const timers = new WeakMap();

        document.addEventListener('change', (event) => {
            const form = event.target instanceof Element ? event.target.closest('form[data-ss-autosave]') : null;

            if (!form) {
                return;
            }

            window.clearTimeout(timers.get(form));
            timers.set(form, window.setTimeout(() => save(form), DELAY));
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

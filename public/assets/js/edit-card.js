/**
 * Karty formularza w dwóch stanach (x-admiro::folder-card editable, [data-ss-edit-card]): po załadowaniu karta przechodzi
 * w podgląd (data-state="view": dane w formie prezentacyjnej .ss-folder-card__preview, pola .ss-folder-card__form
 * schowane, fieldset[data-ss-edit-fields] wyłączone, widać tylko „Edytuj”), chyba że serwer otworzył ją
 * w edycji (data-ss-edit-open, np. po błędzie walidacji). „Edytuj” zapamiętuje wartości pól karty i włącza je (data-state=
 * "edit": „Zapisz” i „Anuluj”), „Anuluj” przywraca zapamiętane wartości (zdarzenie change tylko dla pól, które się zmieniły — np. autozapisowy przełącznik w karcie się nie wysyła) i wraca do podglądu. Przed wysłaniem formularza
 * pola wszystkich kart są włączane, bo pola wyłączone nie trafiają do zapisu — zapis obejmuje cały rekord. Bez skryptu
 * karta zostaje zwykłym formularzem z „Zapisz”. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const saved = new WeakMap();

    const fieldsets = (card) => card.querySelectorAll('fieldset[data-ss-edit-fields]');
    const controls = (card) => card.querySelectorAll('input, select, textarea');

    const remember = (card) => {
        saved.set(card, Array.from(controls(card), (control) => [control, control.type === 'checkbox' || control.type === 'radio' ? control.checked : control.value]));
    };

    const restore = (card) => {
        (saved.get(card) ?? []).forEach(([control, value]) => {
            const current = typeof value === 'boolean' ? control.checked : control.value;

            if (current === value) {
                return;
            }

            if (typeof value === 'boolean') {
                control.checked = value;
            } else {
                control.value = value;
            }

            control.dispatchEvent(new Event('change', { bubbles: true }));
        });
    };

    const setState = (card, state) => {
        card.dataset.state = state;
        fieldsets(card).forEach((fieldset) => {
            fieldset.disabled = state === 'view';
        });
        card.querySelectorAll('[data-ss-edit-start]').forEach((button) => {
            button.hidden = state !== 'view';
        });
        card.querySelectorAll('[data-ss-edit-save], [data-ss-edit-cancel]').forEach((button) => {
            button.hidden = state === 'view';
        });
    };

    const start = () => {
        document.querySelectorAll('[data-ss-edit-card]').forEach((card) => {
            if (card.hasAttribute('data-ss-edit-open')) {
                remember(card);
                setState(card, 'edit');
            } else {
                setState(card, 'view');
            }
        });

        document.addEventListener('click', (event) => {
            const button = event.target instanceof Element ? event.target.closest('[data-ss-edit-start], [data-ss-edit-cancel]') : null;
            const card = button?.closest('[data-ss-edit-card]');

            if (!button || !card) {
                return;
            }

            event.preventDefault();

            if (button.hasAttribute('data-ss-edit-start')) {
                remember(card);
                setState(card, 'edit');
                card.querySelector('fieldset[data-ss-edit-fields] input:not([type="hidden"]), fieldset[data-ss-edit-fields] select')?.focus();

                return;
            }

            restore(card);
            setState(card, 'view');
        });

        document.addEventListener('submit', (event) => {
            const form = event.target instanceof HTMLFormElement ? event.target : null;

            form?.querySelectorAll('[data-ss-edit-card] fieldset[data-ss-edit-fields]').forEach((fieldset) => {
                fieldset.disabled = false;
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

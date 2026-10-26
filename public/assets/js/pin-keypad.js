/**
 * Wirtualna klawiatura numeryczna pól PIN (x-admiro::pin-field): przycisk [data-ss-keypad-open] w polu otwiera
 * modalnie okno [data-ss-keypad] (natywny <dialog>), którego przyciski dopisują cyfry do pola wskazanego przez
 * data-ss-keypad-for (do jego maxlength), usuwają ostatnią cyfrę albo czyszczą pole. Kropki w
 * [data-ss-keypad-display] pokazują liczbę wpisanych cyfr (czytniki ekranu dostają ją tekstem z
 * data-ss-keypad-count: „:count” → liczba). W oknie działa też fizyczna klawiatura (cyfry, Backspace, Delete,
 * Enter = „Zatwierdź PIN”, Escape zamyka natywnie).
 *
 * „Zatwierdź PIN” [data-ss-keypad-done] przy PIN-ie krótszym niż minlength pokazuje komunikat
 * [data-ss-keypad-error] (data-ss-keypad-too-short) i zostawia okno otwarte; przy poprawnym zamyka okno i przechodzi
 * dalej: do następnego pustego albo niepoprawnego pola PIN formularza (otwiera jego klawiaturę), a gdy go nie ma —
 * wysyła formularz (requestSubmit) albo, gdy jest niepoprawny, wskazuje pierwsze niepoprawne pole. Enter w samym polu
 * działa tak samo, bez okna. Wartość wpisana skryptem nie podlega natywnemu minlength, więc pole dostaje
 * setCustomValidity z tym samym komunikatem — formularz z za krótkim PIN-em się nie wyśle. Samo zamknięcie wraca
 * fokusem do pola. Bez skryptu przycisk zostaje ukryty. Okno z [data-ss-keypad-show] otwiera się od razu po
 * załadowaniu (podgląd w design systemie). Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const init = (dialog) => {
        const input = document.getElementById(dialog.dataset.ssKeypadFor ?? '');
        const opener = input?.parentElement?.querySelector('[data-ss-keypad-open]');
        const display = dialog.querySelector('[data-ss-keypad-display]');

        if (!(input instanceof HTMLInputElement) || opener === null || display === null || typeof dialog.showModal !== 'function') {
            return;
        }

        const limit = () => (input.maxLength > 0 ? input.maxLength : Infinity);
        const error = dialog.querySelector('[data-ss-keypad-error]');
        const tooShortMessage = error?.dataset.ssKeypadTooShort ?? '';
        const isTooShort = () => input.value.length < Math.max(input.minLength, 1);

        const showError = (visible) => {
            if (error !== null) {
                error.textContent = visible ? tooShortMessage : '';
                error.hidden = !visible;
            }
        };

        const validate = () => {
            input.setCustomValidity(input.value !== '' && isTooShort() ? tooShortMessage : '');
        };

        const render = () => {
            const count = input.value.length;
            display.replaceChildren(...Array.from({ length: count }, () => {
                const dot = document.createElement('span');
                dot.className = 'ss-keypad__dot';
                dot.setAttribute('aria-hidden', 'true');

                return dot;
            }));
            const label = document.createElement('span');
            label.className = 'ss-sr-only';
            label.textContent = (display.dataset.ssKeypadCount ?? ':count').replace(':count', String(count));
            display.append(label);
        };

        const update = (value) => {
            input.value = value.slice(0, limit());
            input.dispatchEvent(new Event('input', { bubbles: true }));
            showError(false);
            render();
        };

        const press = (digit) => {
            if (input.value.length < limit()) {
                update(input.value + digit);
            }
        };

        dialog.querySelectorAll('[data-ss-keypad-digit]').forEach((key) => {
            key.addEventListener('click', () => press(key.dataset.ssKeypadDigit ?? ''));
        });
        dialog.querySelector('[data-ss-keypad-backspace]')?.addEventListener('click', () => update(input.value.slice(0, -1)));
        dialog.querySelector('[data-ss-keypad-clear]')?.addEventListener('click', () => update(''));
        input.addEventListener('input', validate);
        let afterClose = null;

        /* Następny krok po poprawnym PIN-ie: kolejne puste albo niepoprawne pole PIN, inaczej wysłanie formularza. */
        const proceed = (fromKeypad) => {
            const form = input.form;

            if (form === null) {
                input.focus();

                return;
            }

            const fields = Array.from(form.querySelectorAll('input[data-ss-pin-field]'));
            const next = fields.slice(fields.indexOf(input) + 1).find((field) => field.value === '' || !field.checkValidity());

            if (next instanceof HTMLInputElement) {
                const nextOpener = next.parentElement?.querySelector('[data-ss-keypad-open]');

                if (fromKeypad && nextOpener instanceof HTMLElement && !nextOpener.hidden) {
                    nextOpener.click();
                } else {
                    next.focus();
                }
            } else if (form.checkValidity()) {
                form.requestSubmit();
            } else {
                const invalid = form.querySelector(':invalid');

                if (invalid instanceof HTMLElement) {
                    invalid.focus();
                }

                form.reportValidity();
            }
        };

        const done = () => {
            if (isTooShort()) {
                showError(true);

                return;
            }

            afterClose = () => proceed(true);
            dialog.close();
        };

        input.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') {
                return;
            }

            event.preventDefault();
            validate();

            if (isTooShort()) {
                input.reportValidity();
            } else {
                proceed(false);
            }
        });

        dialog.querySelector('[data-ss-keypad-close]')?.addEventListener('click', () => dialog.close());
        dialog.querySelector('[data-ss-keypad-done]')?.addEventListener('click', done);

        dialog.addEventListener('keydown', (event) => {
            if (/^[0-9]$/.test(event.key)) {
                event.preventDefault();
                press(event.key);
            } else if (event.key === 'Backspace' || event.key === 'Delete') {
                event.preventDefault();
                update(event.key === 'Delete' ? '' : input.value.slice(0, -1));
            } else if (event.key === 'Enter') {
                event.preventDefault();
                done();
            }
        });
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
        dialog.addEventListener('close', () => {
            const next = afterClose ?? (() => input.focus());
            afterClose = null;
            showError(false);
            next();
        });

        opener.addEventListener('click', () => {
            render();
            dialog.showModal();
        });
        opener.hidden = false;

        if (dialog.hasAttribute('data-ss-keypad-show')) {
            render();
            dialog.showModal();
        }
    };

    const start = () => document.querySelectorAll('[data-ss-keypad]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

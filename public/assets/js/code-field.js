/**
 * Pole kodu z osobną komórką na każdą cyfrę (x-admiro::code-field): w [data-ss-code] skrypt pokazuje komórki
 * [data-ss-code-cell], ukrywa zwykłe pole [data-ss-code-input] (zmienia je na type="hidden", więc dalej wysyła kod)
 * i po każdej zmianie składa w nim kod z komórek. Cyfra przechodzi do następnej komórki, Backspace w pustej komórce
 * cofa do poprzedniej, strzałki przechodzą między komórkami, a wklejenie albo autouzupełnianie kilku cyfr rozkłada
 * je od bieżącej komórki. Etykieta wskazuje pierwszą komórkę, autofocus zwykłego pola przechodzi na nią. Bez skryptu
 * zostaje zwykłe pole. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const init = (field) => {
        const input = field.querySelector('[data-ss-code-input]');
        const fallback = field.querySelector('[data-ss-code-fallback]');
        const group = field.querySelector('[data-ss-code-cells]');
        const label = field.querySelector('[data-ss-code-label]');
        const cells = Array.from(field.querySelectorAll('[data-ss-code-cell]'));

        if (!(input instanceof HTMLInputElement) || fallback === null || group === null || cells.length === 0) {
            return;
        }

        const sync = () => {
            input.value = cells.map((cell) => cell.value).join('');
        };

        const fill = (start, digits) => {
            let index = start;

            for (const digit of digits) {
                if (index >= cells.length) {
                    break;
                }

                cells[index].value = digit;
                index += 1;
            }

            sync();
            cells[Math.min(index, cells.length - 1)].focus();
        };

        cells.forEach((cell, index) => {
            cell.required = true;

            cell.addEventListener('input', () => {
                const digits = cell.value.replace(/\D/g, '');
                cell.value = '';
                fill(index, digits);

                if (digits === '') {
                    cell.focus();
                }
            });

            cell.addEventListener('keydown', (event) => {
                if (event.key === 'Backspace' && cell.value === '' && index > 0) {
                    event.preventDefault();
                    cells[index - 1].value = '';
                    cells[index - 1].focus();
                    sync();
                } else if (event.key === 'ArrowLeft' && index > 0) {
                    event.preventDefault();
                    cells[index - 1].focus();
                } else if (event.key === 'ArrowRight' && index < cells.length - 1) {
                    event.preventDefault();
                    cells[index + 1].focus();
                }
            });

            cell.addEventListener('paste', (event) => {
                event.preventDefault();
                fill(index, (event.clipboardData?.getData('text') ?? '').replace(/\D/g, ''));
            });

            cell.addEventListener('focus', () => cell.select());
        });

        const autofocus = input.autofocus;
        const initial = input.value.replace(/\D/g, '');
        cells.forEach((cell, index) => {
            cell.value = initial[index] ?? '';
        });

        input.type = 'hidden';
        fallback.hidden = true;
        group.hidden = false;
        label?.setAttribute('for', cells[0].id);
        sync();

        if (autofocus) {
            cells[Math.min(initial.length, cells.length - 1)].focus();
        }
    };

    const start = () => document.querySelectorAll('[data-ss-code]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

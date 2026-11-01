/**
 * Edytor atrybutów rekordu (x-admiro::attributes-editor, [data-ss-attributes]): „Dodaj atrybut” dopisuje wiersz
 * z <template data-ss-attributes-template> i ustawia fokus na jego nazwie, „Usuń” usuwa wiersz, a uchwyt
 * [data-ss-attributes-handle] przeciąga wiersz w nowe miejsce listy (HTML drag & drop; strzałki w górę/dół na uchwycie
 * przesuwają go z klawiatury). Po każdej zmianie nazwy pól wierszy dostają kolejne numery (`<pole>[i][…]`), więc
 * kolejność listy jest kolejnością zapisu. Pusty wiersz dla wersji bez skryptu jest usuwany, a przyciski i uchwyty
 * odsłaniane. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const renumber = (editor) => {
        const field = editor.dataset.ssAttributesField ?? '';
        const pattern = new RegExp('^' + field.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\[[^\\]]+\\]');

        editor.querySelectorAll('[data-ss-attributes-list] > [data-ss-attributes-row]').forEach((row, index) => {
            row.querySelectorAll('input, select, textarea').forEach((control) => {
                control.name = control.name.replace(pattern, `${field}[${index}]`);
                control.id = control.id.replace(/-(\d+|__INDEX__)-/, `-${index}-`);
                control.closest('.ss-field')?.querySelector('label')?.setAttribute('for', control.id);
            });
        });
    };

    const reveal = (root) => {
        root.querySelectorAll('[data-ss-attributes-handle], [data-ss-attributes-remove]').forEach((element) => {
            element.hidden = false;
        });
        root.querySelectorAll('[data-ss-attributes-row]').forEach((row) => {
            row.draggable = false;
        });
    };

    const start = () => {
        document.querySelectorAll('[data-ss-attributes]').forEach((editor) => {
            editor.querySelectorAll('[data-ss-attributes-blank]').forEach((row) => row.remove());
            editor.querySelectorAll('[data-ss-attributes-add]').forEach((button) => {
                button.hidden = false;
            });
            reveal(editor);
            renumber(editor);
        });

        let dragged = null;

        document.addEventListener('click', (event) => {
            const target = event.target instanceof Element ? event.target : null;
            const editor = target?.closest('[data-ss-attributes]');

            if (!target || !editor) {
                return;
            }

            if (target.closest('[data-ss-attributes-add]')) {
                const template = editor.querySelector('template[data-ss-attributes-template]');
                const list = editor.querySelector('[data-ss-attributes-list]');
                const row = template?.content.firstElementChild?.cloneNode(true);

                if (row instanceof HTMLElement && list) {
                    list.append(row);
                    reveal(row.parentElement ?? list);
                    renumber(editor);
                    row.querySelector('input[name$="[name]"]')?.focus();
                }

                return;
            }

            const remove = target.closest('[data-ss-attributes-remove]');

            if (remove) {
                remove.closest('[data-ss-attributes-row]')?.remove();
                renumber(editor);
            }
        });

        document.addEventListener('pointerdown', (event) => {
            const handle = event.target instanceof Element ? event.target.closest('[data-ss-attributes-handle]') : null;
            const row = handle?.closest('[data-ss-attributes-row]');

            if (row instanceof HTMLElement) {
                row.draggable = true;
            }
        });

        document.addEventListener('dragstart', (event) => {
            const row = event.target instanceof HTMLElement && event.target.matches('[data-ss-attributes-row]') ? event.target : null;

            if (row) {
                dragged = row;
                row.classList.add('ss-attributes__row--dragging');
                event.dataTransfer?.setData('text/plain', '');
            }
        });

        document.addEventListener('dragover', (event) => {
            const over = event.target instanceof Element ? event.target.closest('[data-ss-attributes-row]') : null;

            if (!dragged || !over || over === dragged || over.parentElement !== dragged.parentElement) {
                return;
            }

            event.preventDefault();
            const box = over.getBoundingClientRect();
            over.parentElement.insertBefore(dragged, event.clientY < box.top + box.height / 2 ? over : over.nextSibling);
        });

        document.addEventListener('dragend', () => {
            if (!dragged) {
                return;
            }

            const editor = dragged.closest('[data-ss-attributes]');
            dragged.classList.remove('ss-attributes__row--dragging');
            dragged.draggable = false;
            dragged = null;

            if (editor) {
                renumber(editor);
            }
        });

        document.addEventListener('keydown', (event) => {
            const handle = event.target instanceof Element ? event.target.closest('[data-ss-attributes-handle]') : null;
            const row = handle?.closest('[data-ss-attributes-row]');

            if (!row || (event.key !== 'ArrowUp' && event.key !== 'ArrowDown')) {
                return;
            }

            event.preventDefault();
            const sibling = event.key === 'ArrowUp' ? row.previousElementSibling : row.nextElementSibling;

            if (sibling) {
                row.parentElement?.insertBefore(row, event.key === 'ArrowUp' ? sibling : sibling.nextSibling);
                renumber(row.closest('[data-ss-attributes]'));
                handle.focus();
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

/**
 * Drzewo z przeciąganiem ([data-ss-tree]): element [data-ss-tree-item="<token>"] z draggable="true" upuszczony na inny
 * element drzewa trafia pod niego, a upuszczony na strefę [data-ss-tree-root] — na najwyższy poziom. Skrypt ustawia
 * wartość listy wyboru w formularzu przeniesienia w wierszu elementu ([data-ss-tree-row] form[data-ss-tree-move] select) i wysyła formularz, więc
 * serwer sprawdza zmianę jak przy wyborze z listy (bez skryptu i z klawiatury działa sama lista). Nie wolno upuścić
 * elementu na niego samego ani na jego gałąź; element docelowy bez formularza przeniesienia (np. departament) nie
 * przyjmuje zrzutu, jeśli ma [data-ss-tree-locked]. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const OVER = 'is-drop-target';
    const DRAGGING = 'is-dragging';

    const init = (tree) => {
        let dragged = null;

        const itemOf = (target) => (target instanceof Element ? target.closest('[data-ss-tree-item], [data-ss-tree-root]') : null);

        const accepts = (target) => {
            if (dragged === null || target === null || !tree.contains(target)) {
                return false;
            }

            if (target.hasAttribute('data-ss-tree-root')) {
                return true;
            }

            return !target.hasAttribute('data-ss-tree-locked') && target !== dragged && !dragged.contains(target);
        };

        const clear = () => tree.querySelectorAll(`.${OVER}`).forEach((element) => element.classList.remove(OVER));

        tree.addEventListener('dragstart', (event) => {
            const item = event.target instanceof Element ? event.target.closest('[data-ss-tree-item][draggable="true"]') : null;

            if (item === null) {
                return;
            }

            dragged = item;
            item.classList.add(DRAGGING);
            event.dataTransfer?.setData('text/plain', item.dataset.ssTreeItem ?? '');

            if (event.dataTransfer) {
                event.dataTransfer.effectAllowed = 'move';
            }
        });

        tree.addEventListener('dragover', (event) => {
            const target = itemOf(event.target);

            if (!accepts(target)) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            clear();
            target.classList.add(OVER);
        });

        tree.addEventListener('dragleave', (event) => {
            const target = itemOf(event.target);

            if (target !== null && !target.contains(event.relatedTarget instanceof Node ? event.relatedTarget : null)) {
                target.classList.remove(OVER);
            }
        });

        tree.addEventListener('drop', (event) => {
            const target = itemOf(event.target);

            if (!accepts(target)) {
                return;
            }

            event.preventDefault();
            const select = dragged?.querySelector(':scope > [data-ss-tree-row] form[data-ss-tree-move] select');
            const parent = target.hasAttribute('data-ss-tree-root') ? '' : (target.dataset.ssTreeItem ?? '');

            clear();

            if (select instanceof HTMLSelectElement && select.value !== parent && [...select.options].some((option) => option.value === parent)) {
                select.value = parent;
                select.form?.requestSubmit();
            }
        });

        tree.addEventListener('dragend', () => {
            dragged?.classList.remove(DRAGGING);
            dragged = null;
            clear();
        });
    };

    const start = () => document.querySelectorAll('[data-ss-tree]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

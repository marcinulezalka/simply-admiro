/**
 * Drzewo z przeciąganiem ([data-ss-tree]): element [data-ss-tree-item="<token>"] z draggable="true" — z drzewa albo
 * spoza niego (np. wyłączona organizacja z wyników wyszukiwania) — upuszczony na element drzewa trafia pod niego,
 * a upuszczony na strefę [data-ss-tree-root] — na najwyższy poziom. Skrypt ustawia pole `parent` w formularzu
 * przeniesienia przeciąganego elementu (form[data-ss-tree-move]: w wierszu drzewa [data-ss-tree-row] lista wyboru,
 * w wyniku wyszukiwania pole ukryte, np. z `activate`) i wysyła formularz, więc serwer sprawdza zmianę jak przy wyborze
 * z listy (bez skryptu i z klawiatury działa sama lista albo przełącznik). Nie wolno upuścić elementu na niego samego
 * ani na jego gałąź; element z [data-ss-tree-locked] (np. departament) nie przyjmuje zrzutu. Osadzany inline z nonce
 * CSP (x-admiro::scripts).
 */
(() => {
    const OVER = 'is-drop-target';
    const DRAGGING = 'is-dragging';
    let dragged = null;

    const targetOf = (element) => (element instanceof Element ? element.closest('[data-ss-tree] [data-ss-tree-item], [data-ss-tree-root]') : null);

    const accepts = (target) => {
        if (dragged === null || target === null) {
            return false;
        }

        if (target.hasAttribute('data-ss-tree-root')) {
            return true;
        }

        return !target.hasAttribute('data-ss-tree-locked') && target !== dragged && !dragged.contains(target);
    };

    const clear = () => document.querySelectorAll(`.${OVER}`).forEach((element) => element.classList.remove(OVER));

    const parentField = (item) => {
        const form = item.querySelector(':scope > [data-ss-tree-row] form[data-ss-tree-move]') ?? item.querySelector('form[data-ss-tree-move]');

        return form?.querySelector('[name="parent"]') ?? null;
    };

    document.addEventListener('dragstart', (event) => {
        const item = event.target instanceof Element ? event.target.closest('[data-ss-tree-item][draggable="true"]') : null;

        if (item === null || parentField(item) === null) {
            return;
        }

        dragged = item;
        item.classList.add(DRAGGING);
        event.dataTransfer?.setData('text/plain', item.dataset.ssTreeItem ?? '');

        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
        }
    });

    document.addEventListener('dragover', (event) => {
        const target = targetOf(event.target);

        if (!accepts(target)) {
            return;
        }

        event.preventDefault();
        clear();
        target.classList.add(OVER);
    });

    document.addEventListener('dragleave', (event) => {
        const target = targetOf(event.target);

        if (target !== null && !target.contains(event.relatedTarget instanceof Node ? event.relatedTarget : null)) {
            target.classList.remove(OVER);
        }
    });

    document.addEventListener('drop', (event) => {
        const target = targetOf(event.target);

        if (!accepts(target)) {
            return;
        }

        event.preventDefault();
        clear();
        const field = parentField(dragged);
        const parent = target.hasAttribute('data-ss-tree-root') ? '' : (target.dataset.ssTreeItem ?? '');

        if (field instanceof HTMLSelectElement) {
            if (field.value === parent || ![...field.options].some((option) => option.value === parent)) {
                return;
            }

            field.value = parent;
        } else if (field instanceof HTMLInputElement) {
            field.value = parent;
        } else {
            return;
        }

        field.form?.requestSubmit();
    });

    document.addEventListener('dragend', () => {
        dragged?.classList.remove(DRAGGING);
        dragged = null;
        clear();
    });
})();

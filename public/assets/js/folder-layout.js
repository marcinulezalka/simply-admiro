/**
 * Przełącznik układu listy w polu komend teczki (x-admiro::folder-search z layouts:
 * button[data-ss-folder-layout="<id elementu wyników>"]): klik przełącza wyniki między listą a siatką — ustawia
 * data-ss-layout="list|grid" na elemencie wyników (style .ss-record-rows[data-ss-layout="grid"]), aria-pressed="true"
 * przy siatce i podpowiedź z układem, na który przełączy następny klik. Wybór zostaje w przeglądarce (localStorage,
 * klucz z id wyników), więc po przeładowaniu i wyszukiwaniu na żywo (js/live-search.js podmienia tylko zawartość
 * wyników) układ się nie zmienia. Bez skryptu przycisk zostaje ukryty, a wyniki są listą. Osadzany inline z nonce CSP
 * (x-admiro::scripts).
 */
(() => {
    const GRID = 'grid';
    const LIST = 'list';
    const key = (id) => `ss-folder-layout:${id}`;

    const remembered = (id) => {
        try {
            return window.localStorage.getItem(key(id)) === GRID ? GRID : LIST;
        } catch {
            return LIST;
        }
    };

    const remember = (id, layout) => {
        try {
            window.localStorage.setItem(key(id), layout);
        } catch {
            // Bez dostępu do localStorage układ obowiązuje do przeładowania strony.
        }
    };

    const apply = (button, layout) => {
        const results = document.getElementById(button.dataset.ssFolderLayout ?? '');

        results?.setAttribute('data-ss-layout', layout);
        button.setAttribute('aria-pressed', String(layout === GRID));
        button.title = (layout === GRID ? button.dataset.titleList : button.dataset.titleGrid) ?? button.title;
    };

    const start = () => {
        document.querySelectorAll('button[data-ss-folder-layout]').forEach((button) => {
            button.hidden = false;
            apply(button, remembered(button.dataset.ssFolderLayout ?? ''));
        });

        document.addEventListener('click', (event) => {
            const button = event.target instanceof Element ? event.target.closest('button[data-ss-folder-layout]') : null;

            if (!(button instanceof HTMLButtonElement)) {
                return;
            }

            const layout = button.getAttribute('aria-pressed') === 'true' ? LIST : GRID;

            apply(button, layout);
            remember(button.dataset.ssFolderLayout ?? '', layout);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

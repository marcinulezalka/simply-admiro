/**
 * Dropdown układu listy w polu komend teczki (x-admiro::folder-search z layouts:
 * details[data-ss-folder-layout="<id elementu wyników>"]): ikona przycisku pokazuje bieżący układ
 * (data-ss-layout-current na summary), a przycisk w menu (button[data-ss-layout-option]) wybiera układ z data-layouts
 * (lista, siatka, z tree także drzewo) — ustawia data-ss-layout="list|grid|tree" na elemencie wyników (style
 * .ss-record-rows[data-ss-layout]), aria-pressed="true" przy wybranym i zamyka menu. Wybór zostaje w przeglądarce
 * (localStorage, klucz z id wyników), więc po przeładowaniu i wyszukiwaniu na żywo (js/live-search.js podmienia tylko
 * zawartość wyników) układ się nie zmienia; zapamiętany układ, którego teczka nie ma, wraca do listy. Bez skryptu
 * dropdown zostaje ukryty, a wyniki są listą. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const LIST = 'list';
    const key = (id) => `ss-folder-layout:${id}`;
    const layouts = (menu) => (menu.dataset.layouts ?? 'list grid').split(' ').filter((layout) => layout !== '');

    const remembered = (menu) => {
        try {
            const stored = window.localStorage.getItem(key(menu.dataset.ssFolderLayout ?? ''));

            return stored !== null && layouts(menu).includes(stored) ? stored : LIST;
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

    const apply = (menu, layout) => {
        document.getElementById(menu.dataset.ssFolderLayout ?? '')?.setAttribute('data-ss-layout', layout);
        menu.querySelector('summary')?.setAttribute('data-ss-layout-current', layout);
        menu.querySelectorAll('button[data-ss-layout-option]').forEach((option) => {
            option.setAttribute('aria-pressed', String(option.dataset.ssLayoutOption === layout));
        });
    };

    const start = () => {
        document.querySelectorAll('details[data-ss-folder-layout]').forEach((menu) => {
            menu.hidden = false;
            apply(menu, remembered(menu));
        });

        document.addEventListener('click', (event) => {
            const option = event.target instanceof Element ? event.target.closest('button[data-ss-layout-option]') : null;
            const menu = option?.closest('details[data-ss-folder-layout]');

            if (!(option instanceof HTMLButtonElement) || !(menu instanceof HTMLDetailsElement)) {
                return;
            }

            const layout = option.dataset.ssLayoutOption ?? LIST;

            apply(menu, layout);
            remember(menu.dataset.ssFolderLayout ?? '', layout);
            menu.open = false;
            menu.querySelector('summary')?.focus();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

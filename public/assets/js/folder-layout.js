/**
 * Przełącznik układu listy w polu komend teczki (x-admiro::folder-search z layouts:
 * button[data-ss-folder-layout="<id elementu wyników>"]): klik przełącza wyniki na następny układ z data-layouts
 * (lista → siatka, z tree także → drzewo, potem znów lista) — ustawia data-ss-layout="list|grid|tree" na elemencie
 * wyników (style .ss-record-rows[data-ss-layout="grid|tree"]), na przycisku data-ss-layout-next z układem następnego
 * kliku (ikona tego układu), aria-pressed="true" poza listą i podpowiedź z data-title-<układ>. Wybór zostaje
 * w przeglądarce (localStorage, klucz z id wyników), więc po przeładowaniu i wyszukiwaniu na żywo (js/live-search.js
 * podmienia tylko zawartość wyników) układ się nie zmienia; zapamiętany układ, którego teczka nie ma, wraca do listy.
 * Bez skryptu przycisk zostaje ukryty, a wyniki są listą. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const LIST = 'list';
    const key = (id) => `ss-folder-layout:${id}`;
    const layouts = (button) => (button.dataset.layouts ?? 'list grid').split(' ').filter((layout) => layout !== '');

    const remembered = (button) => {
        try {
            const stored = window.localStorage.getItem(key(button.dataset.ssFolderLayout ?? ''));

            return stored !== null && layouts(button).includes(stored) ? stored : LIST;
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

    const following = (button, layout) => {
        const available = layouts(button);

        return available[(available.indexOf(layout) + 1) % available.length] ?? LIST;
    };

    const apply = (button, layout) => {
        const results = document.getElementById(button.dataset.ssFolderLayout ?? '');
        const next = following(button, layout);

        results?.setAttribute('data-ss-layout', layout);
        button.dataset.ssLayoutCurrent = layout;
        button.dataset.ssLayoutNext = next;
        button.setAttribute('aria-pressed', String(layout !== LIST));
        button.title = button.getAttribute(`data-title-${next}`) ?? button.title;
    };

    const start = () => {
        document.querySelectorAll('button[data-ss-folder-layout]').forEach((button) => {
            button.hidden = false;
            apply(button, remembered(button));
        });

        document.addEventListener('click', (event) => {
            const button = event.target instanceof Element ? event.target.closest('button[data-ss-folder-layout]') : null;

            if (!(button instanceof HTMLButtonElement)) {
                return;
            }

            const layout = following(button, button.dataset.ssLayoutCurrent ?? LIST);

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

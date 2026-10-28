/**
 * Zakładki z suwakiem ([data-ss-tabs], np. kategorie panelu powiadomień): lista [data-ss-tabs-list] (ukryta bez
 * skryptu, wtedy wszystkie panele są widoczne jeden pod drugim) z przyciskami [data-ss-tab] (role="tab",
 * aria-controls) przełącza panele [data-ss-tab-panel]: wybrany ma aria-selected="true", pozostałe panele dostają
 * hidden. Liczbę zakładek i numer wybranej skrypt podaje w --ss-tab-count i --ss-tab-index listy, a CSS przesuwa po
 * nich wskaźnik (.ss-tabs--slider). Strzałki w lewo i w prawo, Home i End zmieniają zakładkę, a kotwica panelu w adresie
 * albo kliknięty odnośnik `#<id panelu>` (np. „Import” w menu) wybiera jego zakładkę. Osadzany inline
 * z nonce CSP (x-admiro::scripts).
 */
(() => {
    const init = (root) => {
        const list = root.querySelector('[data-ss-tabs-list]');
        const tabs = list ? [...list.querySelectorAll('[data-ss-tab]')] : [];

        if (tabs.length === 0) {
            return;
        }

        const select = (index, focus = false) => {
            tabs.forEach((tab, position) => {
                const selected = position === index;
                const panel = document.getElementById(tab.getAttribute('aria-controls') ?? '');

                tab.setAttribute('aria-selected', String(selected));
                tab.tabIndex = selected ? 0 : -1;

                if (panel) {
                    panel.hidden = ! selected;
                }
            });
            list.style.setProperty('--ss-tab-index', String(index));

            if (focus) {
                tabs[index].focus();
            }
        };

        const indexOfPanel = (id) => tabs.findIndex((tab) => id !== '' && tab.getAttribute('aria-controls') === id);
        const fromHash = () => indexOfPanel(decodeURIComponent(window.location.hash.slice(1)));

        list.hidden = false;
        list.style.setProperty('--ss-tab-count', String(tabs.length));
        const initial = fromHash();
        select(initial >= 0 ? initial : Math.max(0, tabs.findIndex((tab) => tab.getAttribute('aria-selected') === 'true')));

        window.addEventListener('hashchange', () => {
            const index = fromHash();

            if (index >= 0) {
                select(index);
            }
        });
        document.addEventListener('click', (event) => {
            const link = event.target instanceof Element ? event.target.closest('a[href^="#"]') : null;
            const index = link ? indexOfPanel(decodeURIComponent(link.hash.slice(1))) : -1;

            if (index >= 0) {
                select(index);
            }
        });

        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => select(index));
            tab.addEventListener('keydown', (event) => {
                const target = {
                    ArrowRight: (index + 1) % tabs.length,
                    ArrowLeft: (index - 1 + tabs.length) % tabs.length,
                    Home: 0,
                    End: tabs.length - 1,
                }[event.key];

                if (target !== undefined) {
                    event.preventDefault();
                    select(target, true);
                }
            });
        });
    };

    const start = () => document.querySelectorAll('[data-ss-tabs]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

/**
 * Sidebar wiki i podpowiedzi layoutu stref z kolumnami (x-admiro::app.sidebar-wiki, [data-ss-sidebar-wiki]): przycisk
 * pinezki [data-ss-sidebar-wiki-pin] przypina panel jako stałą prawą kolumnę (stan w localStorage, aria-pressed; tylko
 * na szerokim ekranie), a nieprzypięty panel jest ukryty za wąską zakładką przy prawej krawędzi
 * [data-ss-sidebar-wiki-open], która wysuwa go nad treść; ×, Esc i klik obok chowają go z powrotem. Bez skryptu
 * panel stoi przypięty, bez przycisków. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const STORAGE_KEY = 'ss-sidebar-wiki-pinned';
    const desktop = window.matchMedia('(min-width: 64rem)');

    const readPinned = () => {
        try {
            return window.localStorage.getItem(STORAGE_KEY) === '1';
        } catch {
            return false;
        }
    };

    const writePinned = (pinned) => {
        try {
            window.localStorage.setItem(STORAGE_KEY, pinned ? '1' : '0');
        } catch {
            // Bez dostępu do localStorage stan obowiązuje tylko do przeładowania.
        }
    };

    const init = (sidebar) => {
        const panel = sidebar.querySelector('[data-ss-sidebar-wiki-panel]');
        const opener = sidebar.querySelector('[data-ss-sidebar-wiki-open]');
        const pin = sidebar.querySelector('[data-ss-sidebar-wiki-pin]');
        const close = sidebar.querySelector('[data-ss-sidebar-wiki-close]');
        let pinned = readPinned();
        let open = false;

        const sync = () => {
            const docked = pinned && desktop.matches;
            sidebar.classList.toggle('ss-sidebar-wiki--pinned', docked);
            sidebar.classList.toggle('ss-sidebar-wiki--open', !docked && open);
            pin?.setAttribute('aria-pressed', String(pinned));
            opener?.setAttribute('aria-expanded', String(!docked && open));

            if (panel) {
                panel.inert = !docked && !open;
            }
        };

        const show = (visible) => {
            open = visible;
            sync();
            (visible ? close : opener)?.focus();
        };

        sidebar.classList.add('ss-sidebar-wiki--js');
        [opener, pin, close].forEach((button) => {
            if (button) {
                button.hidden = false;
            }
        });

        opener?.addEventListener('click', () => show(!open));
        close?.addEventListener('click', () => show(false));
        pin?.addEventListener('click', () => {
            pinned = !pinned;
            open = false;
            writePinned(pinned);
            sync();
        });
        sidebar.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && open) {
                show(false);
            }
        });
        document.addEventListener('click', (event) => {
            if (open && event.target instanceof Node && !sidebar.contains(event.target)) {
                open = false;
                sync();
            }
        });
        desktop.addEventListener('change', sync);
        sync();
    };

    const start = () => document.querySelectorAll('[data-ss-sidebar-wiki]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

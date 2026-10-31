/**
 * Sidebar wiki i podpowiedzi layoutu stref z kolumnami (x-admiro::app.sidebar-wiki, [data-ss-sidebar-wiki]): otwiera go
 * kafelek „Wiki” w narzędziach panelu kalendarza ([data-ss-sidebar-wiki-open], pokazywany tylko na stronie z sidebarem;
 * klik zamyka panel kalendarza) — panel wysuwa się z prawej nad treść, a ×, Esc i klik obok chowają go z powrotem.
 * Pinezka [data-ss-sidebar-wiki-pin] przypina panel jako stałą prawą kolumnę (stan w localStorage, aria-pressed; tylko
 * na szerokim ekranie), wtedy kafelek tylko przenosi fokus do treści panelu. Bez skryptu panel stoi przypięty, bez
 * przycisków. Osadzany inline z nonce CSP (x-admiro::scripts).
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
        const body = sidebar.querySelector('.ss-sidebar-wiki__body');
        const pin = sidebar.querySelector('[data-ss-sidebar-wiki-pin]');
        const close = sidebar.querySelector('[data-ss-sidebar-wiki-close]');
        const openers = [...document.querySelectorAll('[data-ss-sidebar-wiki-open]')];
        let pinned = readPinned();
        let open = false;

        const docked = () => pinned && desktop.matches;

        const sync = () => {
            sidebar.classList.toggle('ss-sidebar-wiki--pinned', docked());
            sidebar.classList.toggle('ss-sidebar-wiki--open', !docked() && open);
            pin?.setAttribute('aria-pressed', String(pinned));
            openers.forEach((opener) => opener.setAttribute('aria-expanded', String(docked() || open)));

            if (panel) {
                panel.inert = !docked() && !open;
            }
        };

        const show = (visible) => {
            open = visible;
            sync();

            if (visible) {
                close?.focus();
            }
        };

        sidebar.classList.add('ss-sidebar-wiki--js');
        [pin, close, ...openers].forEach((button) => {
            if (button) {
                button.hidden = false;
            }
        });

        openers.forEach((opener) => opener.addEventListener('click', () => {
            const dropdown = opener.closest('details');

            if (dropdown instanceof HTMLDetailsElement) {
                dropdown.open = false;
            }

            if (docked()) {
                body?.focus();
            } else {
                show(!open);
            }
        }));
        close?.addEventListener('click', () => show(false));
        pin?.addEventListener('click', () => {
            pinned = !pinned;
            open = !pinned;
            writePinned(pinned);
            sync();
        });
        sidebar.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && open) {
                show(false);
            }
        });
        document.addEventListener('click', (event) => {
            const target = event.target;

            if (open && target instanceof Element && !sidebar.contains(target) && !target.closest('[data-ss-sidebar-wiki-open]')) {
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

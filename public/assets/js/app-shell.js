/**
 * Layout aplikacji (x-mainboard::app.layout): zwijanie menu bocznego na szerokim ekranie (stan w localStorage),
 * wysuwane menu na telefonie (tło i Esc zamykają), skrót „/” do globalnej wyszukiwarki i menu rozwijane top baru
 * (<details data-ss-dropdown>: powiadomienia, konto) — otwarcie jednego zamyka inne, klik obok i Esc zamykają. Bez skryptu menu
 * zostaje rozwinięte, a na telefonie stoi nad treścią. Osadzany inline z nonce CSP (x-mainboard::scripts).
 */
(() => {
    const STORAGE_KEY = 'ss-sidebar-collapsed';
    const desktop = window.matchMedia('(min-width: 64rem)');

    const readCollapsed = () => {
        try {
            return window.localStorage.getItem(STORAGE_KEY) === '1';
        } catch {
            return false;
        }
    };

    const writeCollapsed = (collapsed) => {
        try {
            window.localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0');
        } catch {
            // Bez dostępu do localStorage stan obowiązuje tylko do przeładowania.
        }
    };

    const isTyping = (target) => target instanceof Element && target.closest('input, textarea, select, [contenteditable="true"]') !== null;

    const init = (shell) => {
        const toggles = [...shell.querySelectorAll('[data-ss-sidebar-toggle]')];
        const backdrop = shell.querySelector('[data-ss-sidebar-backdrop]');
        const search = shell.querySelector('[data-ss-search]');

        const sync = () => {
            const expanded = desktop.matches ? !shell.classList.contains('ss-shell--collapsed') : shell.classList.contains('ss-shell--open');

            toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', String(expanded)));

            if (backdrop) {
                backdrop.hidden = desktop.matches || !expanded;
            }
        };

        const closeDrawer = () => {
            shell.classList.remove('ss-shell--open');
            sync();
        };

        shell.classList.add('ss-shell--js');
        shell.classList.toggle('ss-shell--collapsed', readCollapsed());

        toggles.forEach((toggle) => toggle.addEventListener('click', () => {
            if (desktop.matches) {
                writeCollapsed(shell.classList.toggle('ss-shell--collapsed'));
            } else {
                shell.classList.toggle('ss-shell--open');
            }

            sync();
        }));

        backdrop?.addEventListener('click', closeDrawer);

        const dropdowns = [...shell.querySelectorAll('[data-ss-dropdown]')];

        dropdowns.forEach((dropdown) => dropdown.addEventListener('toggle', () => {
            if (dropdown.open) {
                dropdowns.filter((other) => other !== dropdown).forEach((other) => { other.open = false; });
            }
        }));

        document.addEventListener('click', (event) => {
            dropdowns.forEach((dropdown) => {
                if (dropdown.open && event.target instanceof Node && !dropdown.contains(event.target)) {
                    dropdown.open = false;
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            const open = dropdowns.find((dropdown) => dropdown.open);

            if (event.key === 'Escape' && open) {
                open.open = false;
                open.querySelector('summary')?.focus();
            }

            if (event.key === 'Escape' && shell.classList.contains('ss-shell--open')) {
                closeDrawer();
                toggles.find((toggle) => toggle.offsetParent !== null)?.focus();
            }

            if (event.key === '/' && search && !event.ctrlKey && !event.metaKey && !event.altKey && !isTyping(event.target)) {
                event.preventDefault();
                search.focus();
            }
        });

        desktop.addEventListener('change', () => {
            shell.classList.remove('ss-shell--open');
            sync();
        });

        sync();
    };

    const start = () => document.querySelectorAll('[data-ss-shell]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

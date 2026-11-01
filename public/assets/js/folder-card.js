/**
 * Zakładki karty-teczki (x-admiro::folder-card z extraTitle, [data-ss-card-tabs]): wypustki [data-ss-card-tab]
 * przełączają części ciała teczki [data-ss-card-pane] (aria-selected, hidden; strzałki w lewo/prawo przechodzą między
 * wypustkami). Karta z błędem w ukrytej części otwiera się na niej (pole z aria-invalid albo .ss-field__error). Bez
 * skryptu obie części są widoczne. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const select = (card, tab) => {
        card.querySelectorAll(':scope > .ss-folders__tabs [data-ss-card-tab]').forEach((other) => {
            const selected = other === tab;
            other.setAttribute('aria-selected', selected ? 'true' : 'false');
            other.tabIndex = selected ? 0 : -1;
            const pane = document.getElementById(other.getAttribute('aria-controls') ?? '');

            if (pane) {
                pane.hidden = !selected;
            }
        });
    };

    const start = () => {
        document.querySelectorAll('[data-ss-card-tabs]').forEach((card) => {
            const tabs = Array.from(card.querySelectorAll(':scope > .ss-folders__tabs [data-ss-card-tab]'));
            const withError = tabs.find((tab) => document.getElementById(tab.getAttribute('aria-controls') ?? '')?.querySelector('[aria-invalid="true"], .ss-field__error'));
            select(card, withError ?? tabs[0]);
        });

        document.addEventListener('click', (event) => {
            const tab = event.target instanceof Element ? event.target.closest('[data-ss-card-tab]') : null;
            const card = tab?.closest('[data-ss-card-tabs]');

            if (tab && card) {
                select(card, tab);
            }
        });

        document.addEventListener('keydown', (event) => {
            const tab = event.target instanceof Element ? event.target.closest('[data-ss-card-tab]') : null;
            const card = tab?.closest('[data-ss-card-tabs]');

            if (!tab || !card || (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight')) {
                return;
            }

            const tabs = Array.from(card.querySelectorAll(':scope > .ss-folders__tabs [data-ss-card-tab]'));
            const next = tabs[(tabs.indexOf(tab) + (event.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
            event.preventDefault();
            select(card, next);
            next.focus();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

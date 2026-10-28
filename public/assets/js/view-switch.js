/**
 * Przełącznik widoku obszaru roboczego (nav[data-ss-view-switch="<id obszaru>"], np. Aktywne / Ukryte w panelu
 * „Język aplikacji”): kliknięty odnośnik [data-ss-view] od razu dostaje aria-current="true" (suwak .ss-tabs--slider
 * przesuwa się w CSS), a skrypt pobiera jego stronę i podmienia tylko obszar o podanym id; adres w pasku dostaje adres
 * odnośnika (history.replaceState), więc odświeżenie i powrót po zapisie przełącznika pokazują ten sam widok. Kilka
 * przełączników tego samego obszaru (np. widok i układ) z atrybutem id dostaje po podmianie adresy i stan odnośników
 * z nowej strony, więc każdy zachowuje wybór drugiego. Bez
 * skryptu odnośnik przeładowuje stronę. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    document.addEventListener('click', async (event) => {
        const link = event.target instanceof Element ? event.target.closest('[data-ss-view-switch] a[data-ss-view]') : null;
        const nav = link?.closest('[data-ss-view-switch]');
        const area = document.getElementById(nav?.dataset.ssViewSwitch ?? '');

        if (!link || !area || event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0) {
            return;
        }

        event.preventDefault();
        nav.querySelectorAll('a[data-ss-view]').forEach((item) => item.setAttribute('aria-current', String(item === link)));
        area.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(link.href, { headers: { Accept: 'text/html' } });
            const fresh = response.ok ? new DOMParser().parseFromString(await response.text(), 'text/html').getElementById(area.id) : null;

            if (!fresh) {
                window.location.assign(link.href);

                return;
            }

            area.innerHTML = fresh.innerHTML;
            document.querySelectorAll(`[data-ss-view-switch="${CSS.escape(area.id)}"][id]`).forEach((other) => {
                const freshNav = fresh.ownerDocument.getElementById(other.id);
                const freshLinks = freshNav ? [...freshNav.querySelectorAll('a[data-ss-view]')] : [];

                other.querySelectorAll('a[data-ss-view]').forEach((item, index) => {
                    item.href = freshLinks[index]?.getAttribute('href') ?? item.href;
                    item.setAttribute('aria-current', freshLinks[index]?.getAttribute('aria-current') ?? item.getAttribute('aria-current') ?? 'false');
                });
            });
            window.history.replaceState(null, '', link.href);
        } catch {
            window.location.assign(link.href);
        } finally {
            area.removeAttribute('aria-busy');
        }
    });
})();

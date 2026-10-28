/**
 * Zachowanie pozycji strony po wysłaniu formularza (form[data-ss-keep-scroll], np. przesłanie tłumaczeń): przed
 * wysłaniem skrypt zapamiętuje przewinięcie strony w sessionStorage, a po powrocie na ten sam adres (przekierowanie po
 * zapisie) przywraca je, więc strona nie skacze na górę ani do kotwicy. Bez sessionStorage (np. tryb prywatny) nic nie
 * robi. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const KEY = 'ss-keep-scroll';

    document.addEventListener('submit', (event) => {
        if (!(event.target instanceof HTMLFormElement) || !event.target.hasAttribute('data-ss-keep-scroll')) {
            return;
        }

        try {
            window.sessionStorage.setItem(KEY, JSON.stringify({ path: window.location.pathname, y: window.scrollY }));
        } catch {
            // Bez sessionStorage strona po prostu wraca na górę.
        }
    });

    const restore = () => {
        try {
            const saved = JSON.parse(window.sessionStorage.getItem(KEY) ?? 'null');
            window.sessionStorage.removeItem(KEY);

            if (saved && saved.path === window.location.pathname && Number.isFinite(saved.y)) {
                window.scrollTo(0, saved.y);
            }
        } catch {
            // Brak zapisanej pozycji albo sessionStorage niedostępny.
        }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', restore);
    } else {
        restore();
    }
})();

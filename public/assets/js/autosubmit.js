/**
 * Formularze wysyłane od razu po zmianie pola (form[data-ss-autosubmit]), np. przełącznik włącz/wyłącz w wierszu
 * tabeli: zmiana pola wysyła formularz (requestSubmit), a przycisk zapasowy [data-ss-autosubmit-fallback] chowa się.
 * Zmiany łapie jeden słuchacz na dokumencie, więc działa też w formularzach dodanych później (np. wyniki wyszukiwania
 * na żywo, js/live-search.js). Bez skryptu przycisk zostaje i wysyła formularz ręcznie. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const start = () => {
        document.querySelectorAll('form[data-ss-autosubmit] [data-ss-autosubmit-fallback]').forEach((button) => {
            button.hidden = true;
        });
        document.addEventListener('change', (event) => {
            const form = event.target instanceof Element ? event.target.closest('form[data-ss-autosubmit]') : null;

            form?.requestSubmit();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

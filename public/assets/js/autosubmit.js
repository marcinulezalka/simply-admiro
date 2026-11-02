/**
 * Formularze wysyłane od razu po zmianie pola (form[data-ss-autosubmit]), np. przełącznik włącz/wyłącz w wierszu
 * tabeli: zmiana pola wysyła formularz (requestSubmit), a przycisk zapasowy [data-ss-autosubmit-fallback] chowa się.
 * Zmiany łapie jeden słuchacz na dokumencie, więc działa też w formularzach dodanych później (np. wyniki wyszukiwania
 * na żywo, js/live-search.js) i dla pól powiązanych z formularzem atrybutem form (pole leży w innym formularzu, np.
 * przełącznik w karcie formularza edycji). Bez skryptu przycisk zostaje i wysyła formularz ręcznie. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const start = () => {
        document.querySelectorAll('[data-ss-autosubmit-fallback]').forEach((button) => {
            if (button.form?.matches('[data-ss-autosubmit]')) {
                button.hidden = true;
            }
        });
        document.addEventListener('change', (event) => {
            const form = event.target instanceof Element ? event.target.form ?? event.target.closest('form') : null;

            if (form instanceof HTMLFormElement && form.matches('[data-ss-autosubmit]')) {
                form.requestSubmit();
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

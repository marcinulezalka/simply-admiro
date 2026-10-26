/**
 * Formularze wysyłane od razu po zmianie pola (form[data-ss-autosubmit]), np. przełącznik włącz/wyłącz w wierszu
 * tabeli: zmiana pola wysyła formularz (requestSubmit), a przycisk zapasowy [data-ss-autosubmit-fallback] chowa się.
 * Bez skryptu przycisk zostaje i wysyła formularz ręcznie. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const init = (form) => {
        form.querySelectorAll('[data-ss-autosubmit-fallback]').forEach((button) => {
            button.hidden = true;
        });
        form.addEventListener('change', () => form.requestSubmit());
    };

    const start = () => document.querySelectorAll('form[data-ss-autosubmit]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

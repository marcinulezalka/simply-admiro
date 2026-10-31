/**
 * Modale x-admiro::dialog (dialog[data-ss-dialog]): klik w [data-ss-dialog-open="<id>"] otwiera modal o tym id
 * (showModal — reszta strony jest nieaktywna, kursor w pierwszym polu) zamiast przejścia pod adres odnośnika
 * i zamyka dropdown, z którego go otwarto; klik w tło zamyka modal (Esc i × zamyka przeglądarka). Modal narysowany
 * otwarty przez serwer ([data-ss-dialog-autoshow], np. po błędzie walidacji) zostaje po załadowaniu przełączony
 * w modal. Bez skryptu odnośnik otwiera stronę z modalem stojącym w treści. Osadzany inline z nonce CSP
 * (x-admiro::scripts).
 */
(() => {
    const show = (dialog) => {
        if (dialog.open) {
            dialog.close();
        }

        dialog.showModal();
        dialog.querySelector('input:not([type="hidden"]), select, textarea')?.focus();
    };

    const start = () => {
        document.querySelectorAll('dialog[data-ss-dialog][data-ss-dialog-autoshow]').forEach((dialog) => {
            if (dialog instanceof HTMLDialogElement) {
                show(dialog);
            }
        });

        document.addEventListener('click', (event) => {
            const target = event.target instanceof Element ? event.target : null;
            const opener = target?.closest('[data-ss-dialog-open]');

            if (opener) {
                const dialog = document.getElementById(opener.getAttribute('data-ss-dialog-open') ?? '');

                if (dialog instanceof HTMLDialogElement) {
                    event.preventDefault();
                    const dropdown = opener.closest('details');

                    if (dropdown instanceof HTMLDetailsElement) {
                        dropdown.open = false;
                    }

                    show(dialog);
                }

                return;
            }

            if (target instanceof HTMLDialogElement && target.matches('[data-ss-dialog]')) {
                target.close();
            }
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

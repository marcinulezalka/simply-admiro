/**
 * Szybkie akcje teczek (x-admiro::folder-actions, [data-ss-folder-actions]): „Zapisz” [data-ss-folder-save] wysyła
 * formularz wybranej teczki — form[data-ss-folder-form] w widocznym panelu [data-ss-tab-panel] tych samych teczek
 * (requestSubmit, więc działa walidacja pól) — i zamyka menu; bez takiego formularza przycisk niczego nie robi. Bez
 * skryptu „Zapisz” jest ukryte. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const start = () => {
        document.querySelectorAll('[data-ss-folder-actions] [data-ss-folder-save]').forEach((button) => {
            button.hidden = false;
        });
        document.addEventListener('click', (event) => {
            const button = event.target instanceof Element ? event.target.closest('[data-ss-folder-save]') : null;

            if (!button) {
                return;
            }

            const menu = button.closest('[data-ss-folder-actions]');
            const folders = menu?.closest('[data-ss-tabs]') ?? document;
            const panel = [...folders.querySelectorAll('[data-ss-tab-panel]')].find((candidate) => !candidate.hidden);
            const form = (panel ?? folders).querySelector('form[data-ss-folder-form]');

            if (menu instanceof HTMLDetailsElement) {
                menu.open = false;
            }

            if (form instanceof HTMLFormElement) {
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

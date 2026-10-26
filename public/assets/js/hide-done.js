/**
 * Przełącznik „nie pokazuj zrobionych” (input[data-ss-hide-done]): włączony dodaje klasę .ss-hide-done kontenerowi
 * o id z data-ss-hide-done, a CSS chowa w nim elementy [data-ss-done]. Wybór jest zapamiętany w przeglądarce
 * (localStorage, klucz z data-ss-hide-done-key); bez dostępu do pamięci działa tylko do przeładowania. Bez skryptu
 * przełącznik zostaje ukryty (najbliższy [data-ss-hide-done-control] ma hidden). Osadzany inline z nonce CSP.
 */
(() => {
    const read = (key) => {
        try {
            return window.localStorage.getItem(key) === '1';
        } catch {
            return false;
        }
    };

    const write = (key, value) => {
        try {
            window.localStorage.setItem(key, value ? '1' : '0');
        } catch {
            // Pamięć przeglądarki niedostępna (np. tryb prywatny): wybór obowiązuje do przeładowania.
        }
    };

    const init = (input) => {
        const target = document.getElementById(input.dataset.ssHideDone ?? '');
        const key = input.dataset.ssHideDoneKey ?? 'ss-hide-done';

        if (!(input instanceof HTMLInputElement) || target === null) {
            return;
        }

        const apply = () => target.classList.toggle('ss-hide-done', input.checked);

        input.checked = read(key);
        apply();
        input.addEventListener('change', () => {
            write(key, input.checked);
            apply();
        });
        input.closest('[data-ss-hide-done-control]')?.removeAttribute('hidden');
    };

    const start = () => document.querySelectorAll('input[data-ss-hide-done]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

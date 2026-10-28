/**
 * Strefa upuszczania plików (form[data-ss-file-drop]): pole pliku przykrywa strefę [data-ss-file-drop-zone], więc
 * przeglądarka sama przyjmuje upuszczone pliki (także bez skryptu); skrypt podświetla strefę przy przeciąganiu
 * (.is-drop-target) i wypisuje nazwy wybranych plików w liście [data-ss-file-drop-list]. Osadzany inline z nonce CSP
 * (x-admiro::scripts).
 */
(() => {
    const OVER = 'is-drop-target';

    const zoneOf = (target) => (target instanceof Element ? target.closest('[data-ss-file-drop] [data-ss-file-drop-zone]') : null);

    ['dragenter', 'dragover'].forEach((type) => {
        document.addEventListener(type, (event) => zoneOf(event.target)?.classList.add(OVER));
    });

    ['dragleave', 'drop'].forEach((type) => {
        document.addEventListener(type, (event) => zoneOf(event.target)?.classList.remove(OVER));
    });

    document.addEventListener('change', (event) => {
        const input = event.target;
        const form = input instanceof HTMLInputElement && input.type === 'file' ? input.closest('form[data-ss-file-drop]') : null;
        const list = form?.querySelector('[data-ss-file-drop-list]');

        if (!list) {
            return;
        }

        list.replaceChildren(...Array.from(input.files ?? [], (file) => {
            const item = document.createElement('li');
            item.textContent = file.name;

            return item;
        }));
        list.hidden = list.childElementCount === 0;
    });
})();

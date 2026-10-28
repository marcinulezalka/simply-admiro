/**
 * Strefa upuszczania plików (form[data-ss-file-drop]): pole pliku przykrywa strefę [data-ss-file-drop-zone], więc
 * przeglądarka sama przyjmuje upuszczone pliki (także bez skryptu); skrypt podświetla strefę przy przeciąganiu
 * (.is-drop-target) i sprawdza wybrane pliki przed importem: każdy musi być JSON-em tłumaczeń — dokumentem z kodem
 * języka (`language`) i obiektem `texts` albo eksportem z listą takich dokumentów (`languages`) — a plików nie może być
 * więcej niż data-ss-file-drop-max. Wynik trafia do listy [data-ss-file-drop-list] (nazwa z dopiskiem
 * data-ss-file-drop-valid albo -invalid, stan w data-state), a przycisk przesłania [data-ss-file-drop-submit]
 * (powiązany atrybutem form, np. w pasie przycisków nad formularzem) pokazuje się dopiero, gdy wszystkie pliki
 * przeszły sprawdzenie. Ostateczną walidację robi serwer. Osadzany inline z nonce CSP (x-admiro::scripts).
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

    const isDocument = (value) => value !== null
        && typeof value === 'object'
        && typeof value.language === 'string'
        && value.language.trim() !== ''
        && value.texts !== null
        && typeof value.texts === 'object'
        && !Array.isArray(value.texts);

    const isTranslations = (value) => (Array.isArray(value?.languages) && value.languages.length > 0
        ? value.languages.every(isDocument)
        : isDocument(value));

    const check = async (file) => {
        try {
            return isTranslations(JSON.parse(await file.text()));
        } catch {
            return false;
        }
    };

    document.addEventListener('change', async (event) => {
        const input = event.target;
        const form = input instanceof HTMLInputElement && input.type === 'file' ? input.closest('form[data-ss-file-drop]') : null;

        if (!form) {
            return;
        }

        const list = form.querySelector('[data-ss-file-drop-list]');
        const submits = form.id ? document.querySelectorAll(`[data-ss-file-drop-submit][form="${CSS.escape(form.id)}"]`) : [];
        const files = Array.from(input.files ?? []);
        const max = Number(form.dataset.ssFileDropMax ?? 0);
        const results = await Promise.all(files.map(check));
        const ready = files.length > 0 && (max === 0 || files.length <= max) && results.every(Boolean);

        if (list) {
            list.replaceChildren(...files.map((file, index) => {
                const item = document.createElement('li');
                item.dataset.state = results[index] ? 'valid' : 'invalid';
                item.textContent = `${file.name} — ${results[index] ? form.dataset.ssFileDropValid ?? '' : form.dataset.ssFileDropInvalid ?? ''}`;

                return item;
            }));
            list.hidden = files.length === 0;
        }

        submits.forEach((button) => {
            button.hidden = !ready;
        });
    });
})();

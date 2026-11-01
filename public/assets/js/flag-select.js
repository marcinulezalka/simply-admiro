/**
 * Flaga wybranego kraju jako ikona w polu wyboru (x-admiro::flag-select, [data-ss-flag-select]): po zmianie listy
 * ustawia obrazek [data-ss-flag-image] na flagę wybranej opcji: kod z mapy data-ss-flags (wartość → kod flagi) wstawiony
 * do szablonu adresu data-ss-flag-url (trasa Core `admiro.flags.show`) w miejsce kodu z data-ss-flag-placeholder,
 * a bez flagi go chowa. Jeden słuchacz na dokumencie, więc działa też w treści wstawionej później. Bez skryptu zostaje
 * flaga opcji wybranej przy otwarciu strony. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    document.addEventListener('change', (event) => {
        const select = event.target instanceof HTMLSelectElement ? event.target : null;
        const wrap = select?.closest('[data-ss-flag-select]');
        const image = wrap?.querySelector('[data-ss-flag-image]');

        if (!select || !(image instanceof HTMLImageElement)) {
            return;
        }

        let flags = {};

        try {
            flags = JSON.parse(wrap.dataset.ssFlags ?? '{}');
        } catch {
            flags = {};
        }

        const code = typeof flags[select.value] === 'string' ? flags[select.value] : '';
        const placeholder = wrap.dataset.ssFlagPlaceholder ?? '';
        const url = code === '' || placeholder === '' ? '' : (wrap.dataset.ssFlagUrl ?? '').replace(placeholder, encodeURIComponent(code));
        image.hidden = url === '';

        if (url !== '') {
            image.src = url;
        }
    });
})();

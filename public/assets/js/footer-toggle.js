/**
 * Wysuwana stopka simplysmart (logowanie strefy z odnośnikami prawnymi — x-mainboard::auth.layout — i stopka menu
 * bocznego — x-mainboard::app.layout): przyciski [data-ss-footer-toggle] (nazwa simplysmart na banerze albo logo)
 * wysuwają i chowają stopkę [data-ss-footer] w obrębie [data-ss-footer-root]. Wartość data-ss-footer-root to klasa
 * bloku, któremu skrypt dodaje modyfikatory <blok>--footer-open (stopka wysunięta) i <blok>--footer-ready (CSS chowa
 * stopkę dopiero po starcie skryptu, więc bez niego jest stale widoczna); aria-expanded na przyciskach, inert na
 * schowanej stopce; Escape ją chowa. Osadzany inline z nonce CSP (x-mainboard::scripts).
 */
(() => {
    const start = () => {
        document.querySelectorAll('[data-ss-footer-root]').forEach((root) => {
            const block = root.dataset.ssFooterRoot ?? '';
            const footer = root.querySelector('[data-ss-footer]');
            const toggles = root.querySelectorAll('[data-ss-footer-toggle]');

            if (block === '' || footer === null || toggles.length === 0) {
                return;
            }

            const open = `${block}--footer-open`;
            const set = (isOpen) => {
                root.classList.toggle(open, isOpen);
                footer.inert = !isOpen;
                toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', String(isOpen)));
            };

            toggles.forEach((toggle) => {
                toggle.addEventListener('click', () => set(!root.classList.contains(open)));
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && root.classList.contains(open)) {
                    set(false);
                }
            });

            set(false);
            root.classList.add(`${block}--footer-ready`);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

/**
 * Stopka simplysmart na logowaniu strefy z odnośnikami prawnymi (x-mainboard::auth.layout): przyciski
 * [data-ss-auth-footer-toggle] (nazwa simplysmart na banerze albo logo) wysuwają i chowają stopkę
 * [data-ss-auth-footer] spod kart — klasa ss-auth--footer-open na [data-ss-auth-footer-root], aria-expanded na
 * przyciskach, inert na schowanej stopce; Escape ją chowa. Klasa ss-auth--footer-ready włącza chowanie w CSS, więc
 * bez skryptu stopka jest stale widoczna.
 */
(() => {
    const start = () => {
        document.querySelectorAll('[data-ss-auth-footer-root]').forEach((root) => {
            const footer = root.querySelector('[data-ss-auth-footer]');
            const toggles = root.querySelectorAll('[data-ss-auth-footer-toggle]');

            if (footer === null || toggles.length === 0) {
                return;
            }

            const set = (open) => {
                root.classList.toggle('ss-auth--footer-open', open);
                footer.inert = !open;
                toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', String(open)));
            };

            toggles.forEach((toggle) => {
                toggle.addEventListener('click', () => set(!root.classList.contains('ss-auth--footer-open')));
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && root.classList.contains('ss-auth--footer-open')) {
                    set(false);
                }
            });

            set(false);
            root.classList.add('ss-auth--footer-ready');
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

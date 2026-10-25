/**
 * Wysuwana stopka simplysmart (x-admiro::brand-footer na logowaniu strefy i w layoucie aplikacji): przyciski
 * [data-ss-footer-toggle] (logo simplysmart) odsłaniają i chowają stopkę [data-ss-footer] w obrębie
 * [data-ss-footer-root]. Wartość data-ss-footer-root to klasa bloku, któremu skrypt dodaje modyfikatory
 * <blok>--footer-open (stopka odsłonięta) i <blok>--footer-ready (zwijanie działa: CSS chowa stopkę dopiero wtedy, więc
 * bez skryptu jest stale widoczna); aria-expanded na przyciskach, inert na schowanej stopce; Escape ją chowa. Na korzeniu
 * zmienna --ss-footer-height (wysokość stopki w px, aktualna przy przełączeniu i zmianie rozmiaru okna) — o tyle CSS
 * przesuwa treść nad stopką. Dłuższa strona przy otwarciu płynnie przewija się na dół (treść musi kończyć się przy
 * dole okna, żeby odsłonić stopkę), a przewinięcie w górę chowa stopkę. data-ss-footer-media na stopce (media query) ogranicza zwijanie do pasujących ekranów:
 * poza nimi nie ma <blok>--footer-ready ani inert (stopka jest zwykłą stopką na końcu strony, a przycisk przewija do niej). Osadzany inline z nonce
 * CSP (x-admiro::scripts).
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
            const media = footer.dataset.ssFooterMedia ? window.matchMedia(footer.dataset.ssFooterMedia) : null;
            const measure = () => root.style.setProperty('--ss-footer-height', `${footer.offsetHeight}px`);
            const collapsible = () => media === null || media.matches;
            const set = (isOpen) => {
                measure();
                root.classList.toggle(open, isOpen);
                footer.inert = !isOpen && collapsible();
                toggles.forEach((toggle) => toggle.setAttribute('aria-expanded', String(isOpen)));
            };
            const ready = () => root.classList.toggle(`${block}--footer-ready`, collapsible());
            const page = document.documentElement;
            let scrolling = false;
            const atBottom = () => window.innerHeight + window.scrollY >= page.scrollHeight - 2;

            media?.addEventListener('change', () => {
                set(false);
                ready();
            });
            window.addEventListener('resize', measure);
            window.addEventListener('scroll', () => {
                if (root.classList.contains(open) && !atBottom() && !scrolling) {
                    set(false);
                }
            }, { passive: true });
            window.addEventListener('scrollend', () => {
                scrolling = false;
            });

            toggles.forEach((toggle) => {
                toggle.addEventListener('click', () => {
                    if (!collapsible()) {
                        footer.scrollIntoView({ behavior: 'smooth', block: 'end' });

                        return;
                    }

                    const isOpen = !root.classList.contains(open);

                    if (isOpen && !atBottom()) {
                        scrolling = true;
                        window.scrollTo({ top: page.scrollHeight, behavior: 'smooth' });
                    }

                    set(isOpen);
                });
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && root.classList.contains(open)) {
                    set(false);
                }
            });

            set(false);
            ready();
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

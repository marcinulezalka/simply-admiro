/**
 * Karta obracana (x-admiro::flip-card, [data-ss-flip]): przyciski w rogu [data-ss-flip-toggle="<odwrót>"] obracają kartę
 * na swój odwrót (klasa .ss-flip--flipped, aria-expanded; widoczny tylko panel [data-ss-flip-side="<odwrót>"]), a
 * „Wróć” [data-ss-flip-return], zjechanie kursorem z karty i Esc obracają ją z powrotem; niewidoczna strona dostaje inert
 * i aria-hidden. Bez skryptu przyciski są ukryte i widać tylko przód. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const init = (card) => {
        const front = card.querySelector('[data-ss-flip-front]');
        const back = card.querySelector('[data-ss-flip-back]');
        const toggles = [...card.querySelectorAll('[data-ss-flip-toggle]')];
        const sides = [...card.querySelectorAll('[data-ss-flip-side]')];
        let opener = toggles[toggles.length - 1];

        /**
         * @param {?string} side nazwa odwrotu albo null — przód
         */
        const flip = (side) => {
            const flipped = side !== null;
            card.classList.toggle('ss-flip--flipped', flipped);
            if (flipped) {
                sides.forEach((panel) => {
                    panel.hidden = panel.dataset.ssFlipSide !== side;
                });
            }
            toggles.forEach((button) => button.setAttribute('aria-expanded', String(button.dataset.ssFlipToggle === side)));
            [[front, !flipped], [back, flipped]].forEach(([face, visible]) => {
                if (face) {
                    face.inert = !visible;
                    face.setAttribute('aria-hidden', String(!visible));
                }
            });
        };

        toggles.forEach((button) => {
            button.hidden = false;
            button.addEventListener('click', () => {
                opener = button;
                flip(button.dataset.ssFlipToggle);
            });
        });
        card.querySelector('[data-ss-flip-return]')?.addEventListener('click', () => {
            flip(null);
            opener?.focus();
        });
        card.addEventListener('mouseleave', () => flip(null));
        card.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && card.classList.contains('ss-flip--flipped')) {
                flip(null);
                opener?.focus();
            }
        });
    };

    const start = () => document.querySelectorAll('[data-ss-flip]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

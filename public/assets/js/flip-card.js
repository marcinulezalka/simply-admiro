/**
 * Karta obracana (x-admiro::flip-card, [data-ss-flip]): przycisk [data-ss-flip-toggle] obraca kartę na drugą stronę
 * (klasa .ss-flip--flipped, aria-expanded), a zjechanie kursorem z karty i Esc obracają ją z powrotem; niewidoczna
 * strona dostaje inert i aria-hidden. Bez skryptu przycisk jest ukryty i widać tylko przód. Osadzany inline z nonce
 * CSP (x-admiro::scripts).
 */
(() => {
    const init = (card) => {
        const front = card.querySelector('[data-ss-flip-front]');
        const back = card.querySelector('[data-ss-flip-back]');
        const toggles = [...card.querySelectorAll('[data-ss-flip-toggle]')];
        const corner = toggles.find((button) => !back?.contains(button));

        const flip = (flipped) => {
            card.classList.toggle('ss-flip--flipped', flipped);
            corner?.setAttribute('aria-expanded', String(flipped));
            [[front, !flipped], [back, flipped]].forEach(([face, visible]) => {
                if (face) {
                    face.inert = !visible;
                    face.setAttribute('aria-hidden', String(!visible));
                }
            });
        };

        toggles.forEach((button) => {
            button.hidden = false;
            button.addEventListener('click', () => flip(!card.classList.contains('ss-flip--flipped')));
        });
        card.addEventListener('mouseleave', () => flip(false));
        card.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && card.classList.contains('ss-flip--flipped')) {
                flip(false);
                corner?.focus();
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

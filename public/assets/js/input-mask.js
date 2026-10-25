/**
 * Maski pól (x-mainboard::input z `data-ss-mask`): w trakcie pisania wartość jest formatowana według wzoru —
 * `9` cyfra, `A` litera, pozostałe znaki wzoru wstawiane same. Wklejony tekst też jest formatowany. Bez skryptu
 * pole przyjmuje dowolny tekst (format podaje podpowiedź). Osadzany inline z nonce CSP (x-mainboard::scripts).
 */
(() => {
    const SLOTS = { 9: /\d/, A: /\p{L}/u };

    const format = (value, pattern) => {
        const chars = [...value].filter((char) => /[\d\p{L}]/u.test(char));
        let result = '';

        for (const slot of pattern) {
            if (chars.length === 0) {
                break;
            }

            const rule = SLOTS[slot];

            if (rule === undefined) {
                result += slot;

                if (chars[0] === slot) {
                    chars.shift();
                }

                continue;
            }

            while (chars.length > 0 && !rule.test(chars[0])) {
                chars.shift();
            }

            if (chars.length > 0) {
                result += chars.shift().toUpperCase();
            }
        }

        return result;
    };

    const init = (input) => {
        const pattern = input.dataset.ssMask;

        input.addEventListener('input', () => {
            const formatted = format(input.value, pattern);

            if (formatted !== input.value) {
                input.value = formatted;
            }
        });
    };

    const start = () => document.querySelectorAll('input[data-ss-mask]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

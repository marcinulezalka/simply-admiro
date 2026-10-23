/**
 * Motyw: jasny, ciemny i dwa motywy wysokiego kontrastu WCAG (czerń na bieli, żółty na czarnym). Zapisany wybór
 * (localStorage) ustawia data-theme na <html> od razu, w <head>, zanim strona się narysuje (bez błysku); przyciski
 * [data-ss-theme] w przełączniku [data-ss-theme-toggle] (x-mainboard::theme-toggle) ustawiają motyw, a wybrany ma
 * aria-pressed="true". Bez wyboru obowiązuje motyw systemu (jasny albo ciemny); bez skryptu przełącznik zostaje ukryty.
 *
 * Kolorystyka: meta ss-palettes (x-mainboard::page; content: kolorystyki strony po spacji, data-scope: zakres
 * zapamiętania, np. strefa); zapisany wybór z tej listy ustawia data-palette na <html>, przyciski
 * [data-ss-palette] w [data-ss-palette-switch] (x-mainboard::palette-switch) go zmieniają (pusta wartość = kolory
 * bazowe). Bez meta strona nie ma kolorystyk i nie dostaje data-palette.
 */
(() => {
    const STORAGE_KEY = 'ss-theme';
    const PALETTE_KEY = 'ss-palette';
    const THEMES = ['light', 'dark', 'contrast', 'contrast-yellow'];
    const root = document.documentElement;
    const system = window.matchMedia('(prefers-color-scheme: dark)');

    const read = () => {
        try {
            const value = window.localStorage.getItem(STORAGE_KEY);

            return THEMES.includes(value) ? value : null;
        } catch {
            return null;
        }
    };

    const write = (key, value) => {
        try {
            if (value === '') {
                window.localStorage.removeItem(key);
            } else {
                window.localStorage.setItem(key, value);
            }
        } catch {
            // Bez dostępu do localStorage wybór obowiązuje tylko do przeładowania.
        }
    };

    const stored = read();

    if (stored !== null) {
        root.dataset.theme = stored;
    }

    const paletteMeta = document.querySelector('meta[name="ss-palettes"]');
    const palettes = (paletteMeta?.content ?? '').split(' ').filter((name) => name !== '');
    const paletteKey = `${PALETTE_KEY}:${paletteMeta?.dataset.scope ?? ''}`;

    const readPalette = () => {
        try {
            const value = window.localStorage.getItem(paletteKey) ?? '';

            return palettes.includes(value) ? value : '';
        } catch {
            return '';
        }
    };

    const applyPalette = (value) => {
        if (value === '') {
            delete root.dataset.palette;
        } else {
            root.dataset.palette = value;
        }
    };

    if (palettes.length > 0) {
        applyPalette(readPalette());
    }

    const current = () => root.dataset.theme ?? (system.matches ? 'dark' : 'light');

    const sync = () => {
        document.querySelectorAll('[data-ss-theme]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.ssTheme === current()));
        });
        document.querySelectorAll('[data-ss-palette]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.ssPalette === (root.dataset.palette ?? '')));
        });
    };

    const start = () => {
        document.querySelectorAll('[data-ss-theme-toggle]').forEach((group) => {
            group.hidden = false;
            group.querySelectorAll('[data-ss-theme]').forEach((button) => {
                button.addEventListener('click', () => {
                    if (!THEMES.includes(button.dataset.ssTheme)) {
                        return;
                    }

                    root.dataset.theme = button.dataset.ssTheme;
                    write(STORAGE_KEY, button.dataset.ssTheme);
                    sync();
                });
            });
        });

        document.querySelectorAll('[data-ss-palette-switch]').forEach((menu) => {
            menu.hidden = palettes.length === 0;
            menu.querySelectorAll('[data-ss-palette]').forEach((button) => {
                button.addEventListener('click', () => {
                    const value = palettes.includes(button.dataset.ssPalette) ? button.dataset.ssPalette : '';

                    applyPalette(value);
                    write(paletteKey, value);
                    sync();
                    menu.open = false;
                });
            });
        });

        system.addEventListener('change', sync);
        sync();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

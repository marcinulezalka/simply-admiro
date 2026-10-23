/**
 * Motyw jasny / ciemny: zapisany wybór (localStorage) ustawia data-theme na <html> od razu, w <head>, zanim strona
 * się narysuje (bez błysku); przyciski [data-ss-theme-toggle] (x-mainboard::theme-toggle) przełączają motyw
 * i pokazują stan w aria-pressed. Bez wyboru obowiązuje motyw systemu; bez skryptu przycisk zostaje ukryty.
 *
 * Kolorystyka: meta ss-palettes (x-mainboard::page; content: kolorystyki strony po spacji, data-scope: zakres
 * zapamiętania, np. strefa); zapisany wybór z tej listy ustawia data-palette na <html>, przyciski
 * [data-ss-palette] w [data-ss-palette-switch] (x-mainboard::palette-switch) go zmieniają (pusta wartość = kolory
 * bazowe). Bez meta strona nie ma kolorystyk i nie dostaje data-palette.
 */
(() => {
    const STORAGE_KEY = 'ss-theme';
    const PALETTE_KEY = 'ss-palette';
    const root = document.documentElement;
    const system = window.matchMedia('(prefers-color-scheme: dark)');

    const read = () => {
        try {
            const value = window.localStorage.getItem(STORAGE_KEY);

            return value === 'light' || value === 'dark' ? value : null;
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
        document.querySelectorAll('[data-ss-theme-toggle]').forEach((button) => {
            button.setAttribute('aria-pressed', String(current() === 'dark'));
        });
        document.querySelectorAll('[data-ss-palette]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.ssPalette === (root.dataset.palette ?? '')));
        });
    };

    const start = () => {
        document.querySelectorAll('[data-ss-theme-toggle]').forEach((button) => {
            button.hidden = false;
            button.addEventListener('click', () => {
                const next = current() === 'dark' ? 'light' : 'dark';

                root.dataset.theme = next;
                write(STORAGE_KEY, next);
                sync();
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

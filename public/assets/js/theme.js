/**
 * Motyw: jasny, ciemny i dwa motywy wysokiego kontrastu WCAG (czerń na bieli, żółty na czarnym). Zapisany wybór
 * (localStorage) ustawia data-theme na <html> od razu, w <head>, zanim strona się narysuje (bez błysku); przyciski
 * [data-ss-theme] w przełączniku [data-ss-theme-toggle] (x-admiro::theme-toggle) ustawiają motyw, a wybrany ma
 * aria-pressed="true". Bez wyboru obowiązuje motyw systemu (jasny albo ciemny, a przy prefers-contrast: more kontrast
 * czarno-biały); bez skryptu przełącznik zostaje ukryty. Kafelek [data-ss-theme-flip] (szybkie ustawienia stopki,
 * x-admiro::app.quick-settings, w [data-ss-theme-flip-control] odsłanianym skryptem) przełącza jasny ↔ ciemny
 * i ma aria-pressed="true" przy ciemnym. Przyciski [data-ss-preference-step] (-1 / 1) w grupie [data-ss-preference]
 * przesuwają wybór o jeden stopień (np. pomniejsz / powiększ rozmiar tekstu), na końcu zakresu wyłączone.
 *
 * Kolorystyka: meta ss-palettes (x-admiro::page; content: kolorystyki strony po spacji, data-scope: zakres
 * zapamiętania, np. strefa); zapisany wybór z tej listy ustawia data-palette na <html>, przyciski
 * [data-ss-palette] w [data-ss-palette-switch] (x-admiro::palette-switch) go zmieniają (pusta wartość = kolory
 * bazowe). Bez meta strona nie ma kolorystyk i nie dostaje data-palette.
 *
 * Wybory wyglądu (Enums\AppearancePreference): rozmiar tekstu (data-text-size, Enums\TextSize), szerokość treści
 * (data-layout-width, Enums\LayoutWidth) i krój strefy (data-font; kroje z meta ss-fonts, zapamiętane w zakresie
 * data-scope jak kolorystyka). Zapisany wybór ustawia atrybut na <html> w <head>, wartość domyślna (fallback) go
 * usuwa; przyciski [data-ss-preference-value] w [data-ss-preference] (x-admiro::preference-toggle) go zmieniają.
 * Listy wartości muszą być takie same jak przypadki enumów.
 *
 * Wygląd w koncie: z meta ss-appearance (x-admiro::page; content: adres zapisu, strefa z `appearance.account`) wybory
 * rysuje serwer na <html> (domyślny wygląd aplikacji nadpisany wyborami konta, Profile\AccountAppearance), więc skrypt
 * nie czyta i nie zapisuje localStorage, tylko wysyła każdą zmianę do konta (PUT JSON z tokenem CSRF; nazwy pól jak
 * Enums\AppearanceSetting: theme, palette, text-size, layout-width, font). Nieudany zapis obowiązuje do przeładowania.
 */
(() => {
    const STORAGE_KEY = 'ss-theme';
    const PALETTE_KEY = 'ss-palette';
    const THEMES = ['light', 'dark', 'contrast', 'contrast-yellow'];
    const root = document.documentElement;
    const system = window.matchMedia('(prefers-color-scheme: dark)');
    const accountUrl = document.querySelector('meta[name="ss-appearance"]')?.content ?? null;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

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

    const saveToAccount = (setting, value) => {
        window.fetch(accountUrl, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
            body: JSON.stringify({ [setting]: value }),
            credentials: 'same-origin',
            keepalive: true,
        }).catch(() => {
            // Bez połączenia wybór obowiązuje tylko do przeładowania.
        });
    };

    // Zapamiętanie wyboru: w koncie (setting — nazwa pola) albo w przeglądarce (key).
    const remember = (setting, key, value) => {
        if (accountUrl !== null) {
            saveToAccount(setting, value);
        } else {
            write(key, value);
        }
    };

    const stored = accountUrl !== null ? (THEMES.includes(root.dataset.theme) ? root.dataset.theme : null) : read();

    if (stored !== null) {
        root.dataset.theme = stored;
    } else if (window.matchMedia('(prefers-contrast: more)').matches) {
        // Bez wyboru system z „większym kontrastem” dostaje kontrast czarno-biały (jak tryb systemu dla jasny / ciemny).
        root.dataset.theme = 'contrast';
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

    if (palettes.length > 0 && accountUrl === null) {
        applyPalette(readPalette());
    }

    const fontMeta = document.querySelector('meta[name="ss-fonts"]');
    const preferences = {
        'text-size': { key: 'ss-text-size', fallback: 'normal', values: ['normal', 'large', 'larger'] },
        'layout-width': { key: 'ss-layout-width', fallback: 'full', values: ['full', 'centered'] },
    };

    if (fontMeta !== null) {
        preferences.font = {
            key: `ss-font:${fontMeta.dataset.scope ?? ''}`,
            fallback: '',
            values: ['', ...fontMeta.content.split(' ').filter((name) => name !== '')],
        };
    }

    const readPreference = (preference) => {
        try {
            const value = window.localStorage.getItem(preference.key) ?? preference.fallback;

            return preference.values.includes(value) ? value : preference.fallback;
        } catch {
            return preference.fallback;
        }
    };

    const applyPreference = (name, value) => {
        if (value === preferences[name].fallback) {
            root.removeAttribute(`data-${name}`);
        } else {
            root.setAttribute(`data-${name}`, value);
        }
    };

    const currentPreference = (name) => root.getAttribute(`data-${name}`) ?? preferences[name].fallback;

    if (accountUrl === null) {
        Object.entries(preferences).forEach(([name, preference]) => applyPreference(name, readPreference(preference)));
    }

    const current = () => root.dataset.theme ?? (system.matches ? 'dark' : 'light');

    const sync = () => {
        document.querySelectorAll('[data-ss-theme]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.ssTheme === current()));
        });
        document.querySelectorAll('[data-ss-theme-flip]').forEach((button) => {
            button.setAttribute('aria-pressed', String(current() === 'dark'));
        });
        document.querySelectorAll('[data-ss-palette]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.ssPalette === (root.dataset.palette ?? '')));
        });
        document.querySelectorAll('[data-ss-preference]').forEach((group) => {
            const name = group.dataset.ssPreference;

            group.querySelectorAll('[data-ss-preference-value]').forEach((button) => {
                button.setAttribute('aria-pressed', String(name in preferences && button.dataset.ssPreferenceValue === currentPreference(name)));
            });
            group.querySelectorAll('[data-ss-preference-step]').forEach((button) => {
                const values = preferences[name]?.values ?? [];
                const index = values.indexOf(currentPreference(name));
                const step = Number(button.dataset.ssPreferenceStep);

                button.disabled = values.length === 0 || (step < 0 ? index <= 0 : index >= values.length - 1);
            });
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
                    remember('theme', STORAGE_KEY, button.dataset.ssTheme);
                    sync();
                });
            });
        });

        document.querySelectorAll('[data-ss-theme-flip-control]').forEach((control) => {
            control.hidden = false;
        });
        document.querySelectorAll('[data-ss-theme-flip]').forEach((button) => {
            button.addEventListener('click', () => {
                const next = current() === 'dark' ? 'light' : 'dark';

                root.dataset.theme = next;
                remember('theme', STORAGE_KEY, next);
                sync();
            });
        });

        document.querySelectorAll('[data-ss-palette-switch]').forEach((menu) => {
            menu.hidden = palettes.length === 0;
            menu.querySelectorAll('[data-ss-palette]').forEach((button) => {
                button.addEventListener('click', () => {
                    const value = palettes.includes(button.dataset.ssPalette) ? button.dataset.ssPalette : '';

                    applyPalette(value);
                    remember('palette', paletteKey, value);
                    sync();
                    menu.open = false;
                });
            });
        });

        document.querySelectorAll('[data-ss-preference]').forEach((group) => {
            const name = group.dataset.ssPreference;
            const preference = preferences[name];

            group.hidden = preference === undefined;
            group.querySelectorAll('[data-ss-preference-value]').forEach((button) => {
                button.addEventListener('click', () => {
                    const value = button.dataset.ssPreferenceValue;

                    if (preference === undefined || !preference.values.includes(value)) {
                        return;
                    }

                    applyPreference(name, value);
                    remember(name, preference.key, value === preference.fallback ? '' : value);
                    sync();
                });
            });
            group.querySelectorAll('[data-ss-preference-step]').forEach((button) => {
                button.addEventListener('click', () => {
                    if (preference === undefined) {
                        return;
                    }

                    const index = preference.values.indexOf(currentPreference(name));
                    const next = preference.values[Math.min(preference.values.length - 1, Math.max(0, index + Number(button.dataset.ssPreferenceStep)))];

                    applyPreference(name, next);
                    remember(name, preference.key, next === preference.fallback ? '' : next);
                    sync();
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

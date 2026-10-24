/**
 * Zgoda na cookies (x-mainboard::cookie-consent): baner przy braku zgody albo zgodzie na starszą wersję polityki,
 * okno ustawień z kategoriami (przycisk „Dostosuj” i każdy element z `data-ss-cookie-settings`), zapis wyboru
 * w ciasteczku pierwszej strony (JSON: wersja, kategorie, losowy identyfikator UUID, czas) i w rejestrze zgód
 * (POST JSON na `data-endpoint`, bez danych osobowych), a potem uruchomienie skryptów opcjonalnych zgodnych kategorii
 * (`<script type="text/plain" data-ss-consent-script>` → prawdziwy <script src>). Zamknięcie ustawień bez decyzji
 * przywraca baner. Osadzany inline z nonce CSP (x-mainboard::scripts).
 */
(() => {
    const root = document.querySelector('[data-ss-consent]');

    if (!root) {
        return;
    }

    const version = Number(root.dataset.version ?? '0');
    const cookieName = root.dataset.cookie ?? 'ss_consent';
    const maxAge = Number(root.dataset.days ?? '365') * 86400;
    const banner = root.querySelector('[data-ss-consent-banner]');
    const settings = root.querySelector('[data-ss-consent-settings]');
    const switches = [...root.querySelectorAll('[data-ss-consent-category]')];
    const optional = switches.map((input) => input.value);

    const read = () => {
        const entry = document.cookie.split('; ').find((part) => part.startsWith(`${cookieName}=`));

        if (!entry) {
            return null;
        }

        try {
            const value = JSON.parse(decodeURIComponent(entry.slice(cookieName.length + 1)));

            return value && typeof value === 'object' && Array.isArray(value.c) ? value : null;
        } catch {
            return null;
        }
    };

    const uuid = () => {
        if (window.crypto?.randomUUID) {
            return window.crypto.randomUUID();
        }

        const bytes = window.crypto.getRandomValues(new Uint8Array(16));
        bytes[6] = (bytes[6] & 0x0f) | 0x40;
        bytes[8] = (bytes[8] & 0x3f) | 0x80;
        const hex = [...bytes].map((byte) => byte.toString(16).padStart(2, '0')).join('');

        return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`;
    };

    let stored = read();
    let decided = stored !== null && stored.v === version;

    const activate = (categories) => {
        root.querySelectorAll('script[data-ss-consent-script]').forEach((placeholder) => {
            if (!categories.includes(placeholder.dataset.ssConsentScript) || placeholder.dataset.loaded) {
                return;
            }

            const script = document.createElement('script');
            script.src = placeholder.dataset.src;
            script.async = true;
            placeholder.dataset.loaded = '1';
            placeholder.after(script);
        });
    };

    const closeAll = () => {
        [banner, settings].forEach((dialog) => dialog?.open && dialog.close());
    };

    const save = (decision, categories) => {
        const id = stored?.id ?? uuid();
        const value = { v: version, c: categories, id, t: Date.now() };
        const secure = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = `${cookieName}=${encodeURIComponent(JSON.stringify(value))}; Max-Age=${maxAge}; Path=/; SameSite=Lax${secure}`;
        stored = value;
        decided = true;
        closeAll();
        activate(categories);

        if (root.dataset.endpoint) {
            window.fetch(root.dataset.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({ consent_id: id, version, decision, categories }),
                credentials: 'same-origin',
                keepalive: true,
            }).catch(() => {});
        }
    };

    const openSettings = () => {
        const current = decided ? stored.c : [];
        switches.forEach((input) => {
            input.checked = current.includes(input.value);
        });

        if (banner?.open) {
            banner.close();
        }

        if (settings && !settings.open) {
            settings.showModal();
        }
    };

    const closeSettings = () => {
        if (settings?.open) {
            settings.close();
        }
    };

    const actions = {
        accept: () => save('accepted', optional),
        reject: () => save('rejected', []),
        customize: openSettings,
        save: () => save('customized', switches.filter((input) => input.checked).map((input) => input.value)),
        close: closeSettings,
    };

    root.addEventListener('click', (event) => {
        const button = event.target.closest('[data-ss-consent-action]');

        if (button) {
            actions[button.dataset.ssConsentAction]?.();
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target.closest('[data-ss-cookie-settings]')) {
            event.preventDefault();
            openSettings();
        }
    });

    banner?.addEventListener('cancel', (event) => event.preventDefault());
    settings?.addEventListener('close', () => {
        if (!decided && banner && !banner.open) {
            banner.showModal();
        }
    });

    if (decided) {
        activate(stored.c);
    } else {
        banner?.showModal();
    }
})();

/**
 * Potwierdzanie akcji (cały system, x-admiro::page): przycisk wysyłający formularz usuwania (pole _method = DELETE)
 * albo przycisk z [data-ss-confirm] („delete” — domyślnie, „accept” — akceptacja) przy pierwszym kliknięciu nie wysyła
 * formularza, tylko kładzie na sobie — w tym samym miejscu, na wierzchniej warstwie — przycisk „Potwierdź” (albo
 * „Potwierdzam” przy akceptacji; teksty z data-ss-confirm-delete / data-ss-confirm-accept na <body>) w tym samym stylu
 * i dokładnie tego samego rozmiaru (.ss-confirm: oba w jednej komórce siatki, szerokość pierwszego przycisku), więc wiersz
 * ma zawsze jeden przycisk. Dopiero on wysyła formularz (z nazwą i wartością pierwszego przycisku). Wokół obwódki biegnie
 * odliczanie jak wskazówka zegara; bez reakcji przez DELAY ms przycisk potwierdzenia znika. [data-ss-confirm="off"]
 * wyłącza potwierdzenie. Bez skryptu formularz wysyła się od razu. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const DELAY = 4000;
    const labels = document.body.dataset;

    const eligible = (button) => {
        if (!(button instanceof HTMLButtonElement) || button.type !== 'submit' || button.dataset.ssConfirm === 'off' || button.dataset.ssConfirmSubmit !== undefined) {
            return false;
        }

        const method = button.form?.querySelector('input[name="_method"]');

        return button.dataset.ssConfirm !== undefined || method?.value.toUpperCase() === 'DELETE';
    };

    const disarm = (button) => {
        const wrap = button.parentElement;

        clearTimeout(button.ssConfirmTimer);
        button.removeAttribute('data-ss-confirm-armed');
        button.setAttribute('aria-pressed', 'false');
        button.ssConfirmSubmit?.remove();
        button.ssConfirmSubmit = null;

        if (wrap?.classList.contains('ss-confirm')) {
            wrap.removeAttribute('data-ss-confirm-armed');
            wrap.style.removeProperty('width');
        }
    };

    const arm = (button) => {
        const kind = button.dataset.ssConfirm === 'accept' ? 'accept' : 'delete';
        const submit = document.createElement('button');
        let wrap = button.parentElement;

        if (!wrap?.classList.contains('ss-confirm')) {
            wrap = document.createElement('span');
            wrap.className = 'ss-confirm';
            button.replaceWith(wrap);
            wrap.append(button);
        }

        wrap.style.width = `${button.offsetWidth}px`;
        wrap.style.setProperty('--ss-confirm-delay', `${DELAY}ms`);
        wrap.dataset.ssConfirmKind = kind;

        submit.type = 'submit';
        submit.className = `${button.className} ss-confirm__submit ss-confirm__submit--${kind}`;
        submit.textContent = kind === 'accept' ? (labels.ssConfirmAccept ?? 'OK') : (labels.ssConfirmDelete ?? 'OK');
        submit.dataset.ssConfirmSubmit = '';

        if (button.name) {
            submit.name = button.name;
            submit.value = button.value;
        }

        if (button.getAttribute('form')) {
            submit.setAttribute('form', button.getAttribute('form'));
        }

        wrap.append(submit);
        wrap.setAttribute('data-ss-confirm-armed', '');
        button.ssConfirmSubmit = submit;
        button.setAttribute('data-ss-confirm-armed', '');
        button.setAttribute('aria-pressed', 'true');
        button.ssConfirmTimer = setTimeout(() => disarm(button), DELAY);
        submit.focus();
    };

    document.addEventListener('click', (event) => {
        const button = event.target instanceof Element ? event.target.closest('button') : null;

        if (button === null || !eligible(button)) {
            return;
        }

        event.preventDefault();

        if (button.hasAttribute('data-ss-confirm-armed')) {
            disarm(button);
        } else {
            arm(button);
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            document.querySelectorAll('button[data-ss-confirm-armed]').forEach((button) => disarm(button));
        }
    });
})();

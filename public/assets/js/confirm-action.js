/**
 * Potwierdzanie akcji (cały system, x-admiro::page): przycisk wysyłający formularz usuwania (pole _method = DELETE)
 * albo przycisk z [data-ss-confirm] („delete” — domyślnie, „accept” — akceptacja) przy pierwszym kliknięciu nie wysyła
 * formularza, tylko wciska się jak przełącznik (aria-pressed, [data-ss-confirm-armed]) i pokazuje pod sobą drugi przycisk
 * „Potwierdź” (albo „Potwierdzam” przy akceptacji; teksty z data-ss-confirm-delete / data-ss-confirm-accept na <body>),
 * który dopiero wysyła formularz (z nazwą i wartością pierwszego przycisku). Bez reakcji przez DELAY ms wszystko wraca
 * do stanu wyjściowego (pasek na przycisku odlicza czas). [data-ss-confirm="off"] wyłącza potwierdzenie. Bez skryptu
 * formularz wysyła się od razu. Osadzany inline z nonce CSP (x-admiro::scripts).
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
        clearTimeout(button.ssConfirmTimer);
        button.removeAttribute('data-ss-confirm-armed');
        button.setAttribute('aria-pressed', 'false');
        button.ssConfirmSubmit?.remove();
        button.ssConfirmSubmit = null;
    };

    const arm = (button) => {
        const kind = button.dataset.ssConfirm === 'accept' ? 'accept' : 'delete';
        const submit = document.createElement('button');

        submit.type = 'submit';
        submit.className = `ss-button ss-confirm__submit ss-confirm__submit--${kind}`;
        submit.textContent = kind === 'accept' ? (labels.ssConfirmAccept ?? 'OK') : (labels.ssConfirmDelete ?? 'OK');
        submit.dataset.ssConfirmSubmit = '';
        submit.style.setProperty('--ss-confirm-delay', `${DELAY}ms`);

        if (button.name) {
            submit.name = button.name;
            submit.value = button.value;
        }

        if (button.getAttribute('form')) {
            submit.setAttribute('form', button.getAttribute('form'));
        }

        let wrap = button.parentElement;

        if (!wrap?.classList.contains('ss-confirm')) {
            wrap = document.createElement('span');
            wrap.className = 'ss-confirm';
            button.replaceWith(wrap);
            wrap.append(button);
        }

        wrap.append(submit);
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
})();

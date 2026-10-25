/**
 * Toasty (x-admiro::toasts): przycisk zamknięcia i samoczynne znikanie toastów z `data-ss-toast-auto` po
 * DISMISS_AFTER ms (błąd zostaje do zamknięcia). Odliczanie zatrzymuje najechanie i fokus w toaście, żeby można
 * było przeczytać treść i użyć akcji. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    const DISMISS_AFTER = 6000;

    const remove = (toast) => {
        toast.classList.add('ss-toast--leaving');
        window.setTimeout(() => toast.remove(), window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 200);
    };

    const init = (toast) => {
        toast.querySelector('[data-ss-toast-close]')?.addEventListener('click', () => remove(toast));

        if (!toast.hasAttribute('data-ss-toast-auto')) {
            return;
        }

        let timer = window.setTimeout(() => remove(toast), DISMISS_AFTER);
        const pause = () => window.clearTimeout(timer);
        const resume = () => {
            if (!toast.matches(':hover, :focus-within')) {
                timer = window.setTimeout(() => remove(toast), DISMISS_AFTER);
            }
        };

        toast.addEventListener('mouseenter', pause);
        toast.addEventListener('focusin', pause);
        toast.addEventListener('mouseleave', resume);
        toast.addEventListener('focusout', resume);
    };

    const start = () => document.querySelectorAll('[data-ss-toast]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();

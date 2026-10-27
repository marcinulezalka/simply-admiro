/**
 * Przełącznik zapisywany żądaniem POST (input[data-ss-post-toggle="<adres>"]) tam, gdzie nie może stać własny formularz
 * (np. przełącznik w podpowiedziach wyszukiwarki, wstawianych do jej formularza): zmiana wysyła POST z tokenem CSRF
 * z meta csrf-token i przeładowuje stronę, więc widać nowy stan (np. włączona organizacja na liście aktywnych).
 * Słuchacz na dokumencie, więc działa też w treści wstawionej później. Osadzany inline z nonce CSP (x-admiro::scripts).
 */
(() => {
    document.addEventListener('change', (event) => {
        const input = event.target;

        if (!(input instanceof HTMLInputElement) || !input.matches('[data-ss-post-toggle]')) {
            return;
        }

        event.stopPropagation();
        input.disabled = true;
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

        fetch(input.dataset.ssPostToggle ?? '', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
        }).finally(() => window.location.reload());
    });
})();

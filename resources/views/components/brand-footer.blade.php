{{-- Pełnoszeroka stopka strony simplysmart (logowanie strefy — x-admiro::auth.layout — i layout aplikacji — x-admiro::app.layout): logo, polityka cookies i odnośniki prawne strefy, w dolnym wierszu copyright, ustawienia cookies z ikoną suwaków i przełącznik motywu, na końcu duże logo simplysmart ucięte dolną krawędzią. Zwijana przez js/footer-toggle.js w obrębie korzenia .ss-footer-host (data-ss-footer-root="ss-footer-host"): przycisk [data-ss-footer-toggle] (logo simplysmart) przesuwa warstwę treści .ss-footer-stage w górę i odsłania stopkę stojącą przy dole okna; zwijanie tylko na ekranach z media (poza nimi stopka jest zwykłą stopką na końcu strony). legalLinks: list<array{url: string, label: string}>; media: media query zwijania; id: id stopki (aria-controls przycisków). --}}
@props(['legalLinks' => [], 'media' => '(min-width: 56rem)', 'id' => 'ss-footer'])
<footer class="ss-footer" id="{{ $id }}" data-ss-footer data-ss-footer-media="{{ $media }}">
    <div class="ss-footer__inner">
        <div class="ss-footer__body">
            <div class="ss-footer__row">
                <x-admiro::logo class="ss-footer__logo" />
                <nav class="ss-footer__legal" aria-label="{{ text('layout.legal') }}">
                    <x-admiro::cookie-links class="ss-footer__legal-cookies" :parts="['policy']" />
                    @foreach ($legalLinks as $link)
                        <a class="ss-link" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                    @endforeach
                </nav>
            </div>
            <div class="ss-footer__meta">
                <p class="ss-footer__copyright">{{ text('layout.copyright', ['year' => (string) now()->year]) }}</p>
                <div class="ss-footer__actions">
                    <x-admiro::cookie-links class="ss-footer__cookie-settings" :parts="['settings']" icon />
                    <x-admiro::theme-toggle />
                </div>
            </div>
        </div>
    </div>
    <div class="ss-footer__brand" aria-hidden="true">
        <x-admiro::logo class="ss-footer__brand-logo" />
    </div>
</footer>
<x-admiro::scripts :files="['js/footer-toggle.js']" />

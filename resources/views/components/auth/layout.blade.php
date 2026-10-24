{{-- Układ stron logowania stref na szablonie bazowym x-mainboard::page (docs/design-system.md): tytuł (fragment w *gwiazdkach* wyróżniony, View\AccentText; w <title> bez gwiazdek), opcjonalny wstęp (lead), komunikat stanu, formularz w slocie, opcjonalna notka pod formularzem (note) i akcje drugorzędne (slot actions). Wariant (Enums\AuthLayout) z `auth.layout` strefy ($zoneAuthLayout z ZonesServiceProvider), prop layout go nadpisuje: centered — jedna karta .ss-card z logo; split — rama .ss-frame z kartą marki (.ss-card--inverse, .ss-card--elevated; logo albo baner strefy z `auth.brand` (wersja ciemna przełączana z motywem: .ss-theme-light / .ss-theme-dark), $zoneAuthBrand albo prop brand: ?array{light: string, dark: ?string, alt: string}) po lewej i kartą formularza (.ss-card--tinted) po prawej; z banerem (.ss-auth--image) karta banera nie ma ramki, a karta formularza nachodzi na nią i zakrywa jej zaokrąglenie. W prawym górnym rogu przełącznik motywu, a pod kartami odnośniki cookies; kolorystyka strefy ($zonePalettes, $zonePaletteScope) obowiązuje bez przełącznika. Strefa z odnośnikami prawnymi (`legal_links`, $zoneLegalLinks, prop legalLinks: list<array{url: string, label: string}>) dostaje stopkę simplysmart (.ss-auth__footer: logo, polityka cookies i odnośniki prawne, a w dolnym wierszu copyright, ustawienia cookies z ikoną suwaków i przełącznik motywu) wysuwaną spod dolnej krawędzi kart po kliknięciu nazwy simplysmart na banerze albo logo (.ss-auth__footer-trigger, js/auth-footer.js; bez skryptu i na telefonie stopka jest stale widoczna), a przełącznik motywu jest w stopce obok copyrightu (.ss-auth__footer-meta). Props stylesheets (ścieżki CSS) i fontFaces (reguły @font-face) zastępują arkusze i fonty strefy żądania (podgląd strefy w design systemie). Wygląd wyłącznie z klas design systemu. Strony logowania nie są indeksowane. --}}
@props(['title' => null, 'description' => null, 'lead' => null, 'note' => null, 'layout' => null, 'brand' => null, 'legalLinks' => null, 'stylesheets' => null, 'fontFaces' => null])
@php
    $heading = $title ?? text('sign_in.title');
    $pageTitle = \Simplysmart\Mainboard\View\AccentText::plain($heading);
    $brand ??= $zoneAuthBrand ?? null;
    $legalLinks ??= $zoneLegalLinks ?? [];
    $stylesheets ??= $zoneStylesheets ?? [];
    $isSplit = ($layout ?? $zoneAuthLayout ?? \Simplysmart\Mainboard\Enums\AuthLayout::Centered) === \Simplysmart\Mainboard\Enums\AuthLayout::Split;
@endphp
<x-mainboard::page :title="$pageTitle" :description="$description" robots="noindex, nofollow" :palettes="$zonePalettes ?? []" :palette-scope="$zonePaletteScope ?? ''" :font-faces="$fontFaces">
    <x-slot:head>
        <x-mainboard::styles />
        <x-mainboard::zone-styles :paths="$stylesheets" />
    </x-slot:head>

    @php($hasFooter = $legalLinks !== [])
    <div @class(['ss-auth', 'ss-auth--split' => $isSplit, 'ss-auth--image' => $isSplit && $brand !== null, 'ss-auth--footer' => $hasFooter]) @if ($hasFooter) data-ss-auth-footer-root @endif>
        @unless ($hasFooter)
            <div class="ss-auth__theme">
                <x-mainboard::theme-toggle />
            </div>
        @endunless
        <main @class(['ss-auth__cards', 'ss-frame ss-frame--row' => $isSplit])>
            @if ($isSplit)
                @if ($brand !== null)
                    <aside class="ss-card ss-card--inverse ss-card--elevated ss-auth__brand ss-auth__brand--image">
                        @if ($brand['dark'] !== null)
                            <img class="ss-auth__brand-image ss-theme-light" src="{{ $brand['light'] }}" alt="{{ $brand['alt'] }}" loading="lazy">
                            <img class="ss-auth__brand-image ss-theme-dark" src="{{ $brand['dark'] }}" alt="{{ $brand['alt'] }}" loading="lazy">
                        @else
                            <img class="ss-auth__brand-image" src="{{ $brand['light'] }}" alt="{{ $brand['alt'] }}">
                        @endif
                        @if ($hasFooter)
                            <button class="ss-auth__footer-trigger ss-auth__footer-trigger--image" type="button" aria-expanded="false" aria-controls="ss-auth-footer" title="{{ text('layout.footer_toggle') }}" data-ss-auth-footer-toggle>
                                <span class="ss-sr-only">{{ text('layout.footer_toggle') }}</span>
                            </button>
                        @endif
                    </aside>
                @else
                    <aside class="ss-card ss-card--inverse ss-card--elevated ss-auth__brand">
                        @if ($hasFooter)
                            <button class="ss-auth__footer-trigger" type="button" aria-expanded="false" aria-controls="ss-auth-footer" title="{{ text('layout.footer_toggle') }}" data-ss-auth-footer-toggle>
                                <x-mainboard::logo class="ss-auth__brand-logo" />
                            </button>
                        @else
                            <x-mainboard::logo class="ss-auth__brand-logo" />
                        @endif
                    </aside>
                @endif
            @endif

            <div @class(['ss-card', 'ss-card--tinted' => $isSplit, 'ss-auth__card'])>
                @unless ($isSplit)
                    @if ($hasFooter)
                        <button class="ss-auth__footer-trigger ss-auth__footer-trigger--logo" type="button" aria-expanded="false" aria-controls="ss-auth-footer" title="{{ text('layout.footer_toggle') }}" data-ss-auth-footer-toggle>
                            <x-mainboard::logo class="ss-auth__logo" />
                        </button>
                    @else
                        <x-mainboard::logo class="ss-auth__logo" />
                    @endif
                @endunless

                <div class="ss-auth__header">
                    <h1 class="ss-auth__title">{{ \Simplysmart\Mainboard\View\AccentText::html($heading) }}</h1>
                    @if (filled($lead))
                        <p class="ss-auth__lead">{{ $lead }}</p>
                    @endif
                </div>

                @if (session('status'))
                    <p class="ss-notice" role="status">{{ session('status') }}</p>
                @endif

                {{ $slot }}

                @if (filled($note))
                    <p class="ss-auth__note">{{ $note }}</p>
                @endif

                @isset($actions)
                    <div class="ss-auth__actions">{{ $actions }}</div>
                @endisset
            </div>
        </main>
        @if ($hasFooter)
            <footer class="ss-auth__footer" id="ss-auth-footer" data-ss-auth-footer>
                <div class="ss-auth__footer-inner">
                    <div class="ss-auth__footer-body">
                        <div class="ss-auth__footer-row">
                            <x-mainboard::logo class="ss-auth__footer-logo" />
                            <nav class="ss-auth__legal" aria-label="{{ text('layout.legal') }}">
                                <x-mainboard::cookie-links class="ss-auth__legal-cookies" :parts="['policy']" />
                                @foreach ($legalLinks as $link)
                                    <a class="ss-link" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                                @endforeach
                            </nav>
                        </div>
                        <div class="ss-auth__footer-meta">
                            <p class="ss-auth__copyright">{{ text('layout.copyright', ['year' => (string) now()->year]) }}</p>
                            <div class="ss-auth__footer-actions">
                                <x-mainboard::cookie-links class="ss-auth__cookie-settings" :parts="['settings']" icon />
                                <x-mainboard::theme-toggle />
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
            <x-mainboard::scripts :files="['js/auth-footer.js']" />
        @else
            <x-mainboard::cookie-links />
        @endif
    </div>
</x-mainboard::page>

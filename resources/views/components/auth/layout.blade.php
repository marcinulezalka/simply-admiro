{{-- Układ stron logowania stref na szablonie bazowym x-admiro::page (docs/design-system.md): tytuł (fragment w *gwiazdkach* wyróżniony, View\AccentText; w <title> bez gwiazdek, chyba że strefa daje tekst `page_title` — $zoneAuthPageTitle albo prop pageTitle — wtedy on jest tytułem karty przeglądarki na każdym ekranie logowania), opcjonalny wstęp (lead), komunikaty z sesji (np. po wylogowaniu) i błędy walidacji kroku (Toast::fromErrors() — pola nie są oznaczane na czerwono) jako globalne toasty (Toast::fromFlash()), formularz w slocie, opcjonalna notka pod formularzem (note), akcje drugorzędne (slot actions) i przycisk powrotu na stronę startową (.ss-auth__home, przycisk z ikoną domu jak na stronach błędów, url('/'); strefa wyłącza go `auth.home_link` = false — $zoneAuthHomeLink, prop homeLink). Wariant (Enums\AuthLayout) z `auth.layout` strefy ($zoneAuthLayout z ZonesServiceProvider), prop layout go nadpisuje: centered — jedna karta .ss-card z logo; split-reversed — split w odbiciu (.ss-auth--reversed: formularz po lewej, marka po prawej; na telefonie marka nad formularzem); split — rama .ss-frame z kartą marki (.ss-card--inverse, .ss-card--elevated; logo albo baner strefy z `auth.brand` (wersja ciemna przełączana z motywem: .ss-theme-light / .ss-theme-dark), $zoneAuthBrand albo prop brand: ?array{light: string, dark: ?string, alt: string}; bez banera logo strefy z `auth.logo` — SVG inline w kolorze karty, $zoneAuthLogo albo prop logo — a bez niego logo simplysmart) po lewej i kartą formularza (.ss-card--tinted) po prawej; z banerem (.ss-auth--image) karta banera nie ma ramki, a karta formularza nachodzi na nią i zakrywa jej zaokrąglenie. Bez przełącznika motywu (obowiązuje motyw systemu albo wybrany w ustawieniach konta); na dole karty formularza odnośniki prawne strefy (`legal_links`, $zoneLegalLinks, prop legalLinks: list<array{url: string, label: string}>) w kolejności: odnośniki strefy (np. polityka prywatności, RODO), polityka cookies, ustawienia cookies — strefa może dać politykę cookies na początek i ukryć ustawienia cookies (`auth.legal`, $zoneAuthCookieLinks), a nazwy zmienić tekstami `sign_in.<strefa>.legal_<klucz>` (.ss-auth__legal; odnośniki cookies tylko z opublikowaną polityką cookies albo skryptami zgody; bez stopki strony simplysmart); kolorystyka i krój strefy ($zonePalettes, $zonePaletteScope, $zoneFontStyles) obowiązują bez przełącznika. Props stylesheets (ścieżki CSS) i fontFaces (reguły @font-face) zastępują arkusze i fonty strefy żądania (podgląd strefy w design systemie). Wygląd wyłącznie z klas design systemu. Strony logowania nie są indeksowane. --}}
@props(['homeLink' => null, 'title' => null, 'description' => null, 'lead' => null, 'note' => null, 'layout' => null, 'brand' => null, 'logo' => null, 'legalLinks' => null, 'pageTitle' => null, 'stylesheets' => null, 'fontFaces' => null])
@php
    $heading = $title ?? text('sign_in.title');
    $pageTitle ??= $zoneAuthPageTitle ?? '';
    $pageTitle = filled($pageTitle) ? $pageTitle : \Simplysmart\Admiro\View\AccentText::plain($heading);
    $brand ??= $zoneAuthBrand ?? null;
    $logo ??= $zoneAuthLogo ?? null;
    $legalLinks ??= $zoneLegalLinks ?? [];
    $cookieLinks = $zoneAuthCookieLinks ?? ['first' => false, 'settings' => true, 'policyLabel' => null];
    $stylesheets ??= $zoneStylesheets ?? [];
    $layout ??= $zoneAuthLayout ?? \Simplysmart\Admiro\Enums\AuthLayout::Centered;
    $homeLink ??= $zoneAuthHomeLink ?? true;
    $isSplit = $layout->isSplit();
@endphp
<x-admiro::page :title="$pageTitle" :description="$description" robots="noindex, nofollow" :palettes="$zonePalettes ?? []" :palette-scope="$zonePaletteScope ?? ''" :font-styles="$zoneFontStyles ?? []" :font-faces="$fontFaces">
    <x-slot:head>
        <x-admiro::styles />
        <x-admiro::zone-styles :paths="$stylesheets" />
        <x-admiro::scripts :files="['js/toasts.js', 'js/pin-keypad.js', 'js/code-field.js']" />
    </x-slot:head>

    <div @class(['ss-auth', 'ss-auth--split' => $isSplit, 'ss-auth--reversed' => $layout === \Simplysmart\Admiro\Enums\AuthLayout::SplitReversed, 'ss-auth--image' => $isSplit && $brand !== null])>
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
                    </aside>
                @else
                    <aside class="ss-card ss-card--inverse ss-card--elevated ss-auth__brand">
                        @if ($logo !== null)
                            <span class="ss-logo ss-auth__brand-logo">{!! $logo !!}</span>
                        @else
                            <x-admiro::logo class="ss-auth__brand-logo" />
                        @endif
                    </aside>
                @endif
            @endif

            <div @class(['ss-card', 'ss-card--tinted' => $isSplit, 'ss-auth__card'])>
                @unless ($isSplit)
                    <x-admiro::logo class="ss-auth__logo" />
                @endunless

                <div class="ss-auth__header">
                    <h1 class="ss-auth__title">{{ \Simplysmart\Admiro\View\AccentText::html($heading) }}</h1>
                    @if (filled($lead))
                        <p class="ss-auth__lead">{{ $lead }}</p>
                    @endif
                </div>

                {{ $slot }}

                @if (filled($note))
                    <p class="ss-auth__note">{{ $note }}</p>
                @endif

                @isset($actions)
                    <div class="ss-auth__actions">{{ $actions }}</div>
                @endisset

                @if ($homeLink)
                    <div class="ss-auth__home">
                        <a class="ss-button" href="{{ url('/') }}"><x-admiro::icon name="home" class="ss-button__icon" />{{ text('layout.home') }}</a>
                    </div>
                @endif

                <nav class="ss-auth__legal" aria-label="{{ text('layout.legal') }}">
                    @if ($cookieLinks['first'])
                        <x-admiro::cookie-links class="ss-auth__legal-cookies" :parts="['policy']" :policy-label="$cookieLinks['policyLabel']" />
                    @endif
                    @foreach ($legalLinks as $link)
                        <a class="ss-link" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                    @endforeach
                    @unless ($cookieLinks['first'])
                        <x-admiro::cookie-links class="ss-auth__legal-cookies" :parts="['policy']" :policy-label="$cookieLinks['policyLabel']" />
                    @endunless
                    @if ($cookieLinks['settings'])
                        <x-admiro::cookie-links class="ss-auth__legal-cookies" :parts="['settings']" />
                    @endif
                </nav>
            </div>
        </main>
    </div>

    <x-admiro::toasts :toasts="[...\Simplysmart\Admiro\View\Toast::fromFlash(session(\Simplysmart\Admiro\View\Toast::SESSION_KEY), session(\Simplysmart\Admiro\View\Toast::STATUS_KEY)), ...\Simplysmart\Admiro\View\Toast::fromErrors($errors ?? null)]" />
</x-admiro::page>

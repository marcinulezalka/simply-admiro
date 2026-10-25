{{-- Układ stron logowania stref na szablonie bazowym x-mainboard::page (docs/design-system.md): tytuł (fragment w *gwiazdkach* wyróżniony, View\AccentText; w <title> bez gwiazdek), opcjonalny wstęp (lead), komunikaty z sesji (np. po wylogowaniu) jako globalne toasty (Toast::fromFlash()), formularz w slocie, opcjonalna notka pod formularzem (note) i akcje drugorzędne (slot actions). Wariant (Enums\AuthLayout) z `auth.layout` strefy ($zoneAuthLayout z ZonesServiceProvider), prop layout go nadpisuje: centered — jedna karta .ss-card z logo; split — rama .ss-frame z kartą marki (.ss-card--inverse, .ss-card--elevated; logo albo baner strefy z `auth.brand` (wersja ciemna przełączana z motywem: .ss-theme-light / .ss-theme-dark), $zoneAuthBrand albo prop brand: ?array{light: string, dark: ?string, alt: string}) po lewej i kartą formularza (.ss-card--tinted) po prawej; z banerem (.ss-auth--image) karta banera nie ma ramki, a karta formularza nachodzi na nią i zakrywa jej zaokrąglenie. Bez przełącznika motywu (obowiązuje motyw systemu albo wybrany w ustawieniach konta); na dole karty formularza odnośniki prawne strefy (`legal_links`, $zoneLegalLinks, prop legalLinks: list<array{url: string, label: string}>) z polityką i ustawieniami cookies (.ss-auth__legal; bez stopki strony simplysmart); kolorystyka strefy ($zonePalettes, $zonePaletteScope) obowiązuje bez przełącznika. Props stylesheets (ścieżki CSS) i fontFaces (reguły @font-face) zastępują arkusze i fonty strefy żądania (podgląd strefy w design systemie). Wygląd wyłącznie z klas design systemu. Strony logowania nie są indeksowane. --}}
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
        <x-mainboard::scripts :files="['js/toasts.js', 'js/pin-keypad.js']" />
    </x-slot:head>

    <div @class(['ss-auth', 'ss-auth--split' => $isSplit, 'ss-auth--image' => $isSplit && $brand !== null])>
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
                        <x-mainboard::logo class="ss-auth__brand-logo" />
                    </aside>
                @endif
            @endif

            <div @class(['ss-card', 'ss-card--tinted' => $isSplit, 'ss-auth__card'])>
                @unless ($isSplit)
                    <x-mainboard::logo class="ss-auth__logo" />
                @endunless

                <div class="ss-auth__header">
                    <h1 class="ss-auth__title">{{ \Simplysmart\Mainboard\View\AccentText::html($heading) }}</h1>
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

                <nav class="ss-auth__legal" aria-label="{{ text('layout.legal') }}">
                    @foreach ($legalLinks as $link)
                        <a class="ss-link" href="{{ $link['url'] }}">{{ $link['label'] }}</a>
                    @endforeach
                    <x-mainboard::cookie-links class="ss-auth__legal-cookies" />
                </nav>
            </div>
        </main>
    </div>

    <x-mainboard::toasts :toasts="\Simplysmart\Mainboard\View\Toast::fromFlash(session(\Simplysmart\Mainboard\View\Toast::SESSION_KEY), session(\Simplysmart\Mainboard\View\Toast::STATUS_KEY))" />
</x-mainboard::page>

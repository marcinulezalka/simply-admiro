{{-- Układ stron logowania stref na szablonie bazowym x-mainboard::page (docs/design-system.md): tytuł, opcjonalny wstęp (lead), komunikat stanu, formularz w slocie i akcje drugorzędne (slot actions). Wariant (Enums\AuthLayout) z `auth.layout` strefy ($zoneAuthLayout z ZonesServiceProvider), prop layout go nadpisuje: centered — jedna karta .ss-card z logo; split — rama .ss-frame z kartą marki (.ss-card--inverse, .ss-card--elevated; logo albo baner strefy z `auth.brand` jako <picture> z wersją dla ciemnego motywu, $zoneAuthBrand albo prop brand: ?array{light: string, dark: ?string, alt: string}) po lewej i kartą formularza (.ss-card--tinted) po prawej. Wygląd wyłącznie z klas design systemu. Strony logowania nie są indeksowane. --}}
@props(['title' => null, 'description' => null, 'lead' => null, 'layout' => null, 'brand' => null])
@php
    $pageTitle = $title ?? text('sign_in.title');
    $brand ??= $zoneAuthBrand ?? null;
    $isSplit = ($layout ?? $zoneAuthLayout ?? \Simplysmart\Mainboard\Enums\AuthLayout::Centered) === \Simplysmart\Mainboard\Enums\AuthLayout::Split;
@endphp
<x-mainboard::page :title="$pageTitle" :description="$description" robots="noindex, nofollow">
    <x-slot:head>
        <x-mainboard::styles />
        <x-mainboard::zone-styles :paths="$zoneStylesheets ?? []" />
    </x-slot:head>

    <div @class(['ss-auth', 'ss-auth--split' => $isSplit])>
        <main @class(['ss-auth__cards', 'ss-frame ss-frame--row' => $isSplit])>
            @if ($isSplit)
                @if ($brand !== null)
                    <aside class="ss-card ss-card--inverse ss-card--elevated ss-auth__brand ss-auth__brand--image">
                        <picture>
                            @if ($brand['dark'] !== null)
                                <source srcset="{{ $brand['dark'] }}" media="(prefers-color-scheme: dark)">
                            @endif
                            <img class="ss-auth__brand-image" src="{{ $brand['light'] }}" alt="{{ $brand['alt'] }}">
                        </picture>
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
                    <h1 class="ss-auth__title">{{ $pageTitle }}</h1>
                    @if (filled($lead))
                        <p class="ss-auth__lead">{{ $lead }}</p>
                    @endif
                </div>

                @if (session('status'))
                    <p class="ss-notice" role="status">{{ session('status') }}</p>
                @endif

                {{ $slot }}

                @isset($actions)
                    <div class="ss-auth__actions">{{ $actions }}</div>
                @endisset
            </div>
        </main>
    </div>
</x-mainboard::page>

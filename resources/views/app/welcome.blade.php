{{-- Domyślna strona startowa aplikacji (trasa `/` szkieletu: view('welcome')). Aplikacja zastępuje ją własnym resources/views/welcome.blade.php. Jedna strona bez akcji: marka, funkcje Core jako ikona, tytuł i opis, stopka z logo Laravela i wersją admiro; rama .ss-frame z dwiema kartami w kolorystyce stron błędów (karta treści .ss-card--tinted, karta marki .ss-card--inverse .ss-card--elevated); w karcie marki logo simplysmart na środku (odnośnik do `admiro.welcome.website` w nowej karcie), pod nim siatka kafelków z subtelnym gradientem i logo zarejestrowanych stref (View\ZoneLogos; logo strefy z `website` jest odnośnikiem do niej w nowej karcie). Nazwy simplysmart i admiro wyróżnia View\BrandNames, w nagłówku nazwa simplysmart to logo SVG (BrandNames::withLogo()). --}}
@php($highlight = \Simplysmart\Admiro\View\BrandNames::highlight(...))
@php($features = ['zones' => 'zones', 'sign_in' => 'sign-in', 'dictionaries' => 'dictionaries', 'history' => 'history', 'locale' => 'locale', 'standards' => 'standards'])
<x-admiro::page :title="config('app.name')" :description="text('welcome.lead')">
    <x-slot:head>
        <x-admiro::styles />
    </x-slot:head>

    <div class="ss-welcome">
        <main class="ss-frame ss-frame--row ss-welcome__card">
            <section class="ss-card ss-card--tinted ss-welcome__content">
                <h1 class="ss-welcome__title">{{ \Simplysmart\Admiro\View\BrandNames::withLogo(text('welcome.title')) }}</h1>
                <p class="ss-welcome__lead ss-muted">{{ $highlight(text('welcome.lead')) }}</p>

                <h2 class="ss-welcome__heading">{{ text('welcome.inside') }}</h2>
                <ul class="ss-welcome__features">
                    @foreach ($features as $feature => $icon)
                        <li class="ss-welcome__feature">
                            <x-admiro::icon :name="$icon" class="ss-icon--tile" />
                            <span class="ss-welcome__feature-text">
                                <span class="ss-welcome__feature-title">{{ text('welcome.features.' . $feature . '.title') }}</span>
                                <span class="ss-muted">{{ text('welcome.features.' . $feature . '.description') }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>

                <footer class="ss-welcome__footer">
                    <span class="ss-welcome__built-on">
                        <span class="ss-muted">{{ text('welcome.built_on') }}</span>
                        <span class="ss-welcome__laravel">{!! \Simplysmart\Admiro\View\CoreAssets::contents('img/vendor/laravel.svg') !!}</span>
                    </span>
                    <span class="ss-badge">{{ text('welcome.admiro_version', ['version' => \Simplysmart\Admiro\View\CoreVersion::current()]) }}</span>
                </footer>
            </section>

            <aside class="ss-card ss-card--inverse ss-card--elevated ss-welcome__brand">
                @php($website = config('admiro.welcome.website'))
                @if (is_string($website) && $website !== '')
                    <a class="ss-welcome__brand-link" href="{{ $website }}" target="_blank" rel="noopener noreferrer" aria-label="{{ text('welcome.website', ['site' => parse_url($website, PHP_URL_HOST) ?? $website]) }}"><x-admiro::logo /></a>
                @else
                    <x-admiro::logo />
                @endif
                @php($zoneLogos = app(\Simplysmart\Admiro\View\ZoneLogos::class)->all())
                @if ($zoneLogos !== [])
                    <ul class="ss-welcome__zones">
                        @foreach ($zoneLogos as $zoneLogo)
                            <li class="ss-welcome__zone-logo">
                                @if ($zoneLogo['website'] !== null)
                                    <a class="ss-welcome__zone-link" href="{{ $zoneLogo['website'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ text('welcome.website', ['site' => parse_url($zoneLogo['website'], PHP_URL_HOST) ?? $zoneLogo['website']]) }}">{!! $zoneLogo['svg'] !!}</a>
                                @else
                                    <span class="ss-welcome__zone-link">{!! $zoneLogo['svg'] !!}</span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </aside>
        </main>
    </div>
</x-admiro::page>

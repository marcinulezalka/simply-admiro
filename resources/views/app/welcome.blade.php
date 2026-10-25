{{-- Domyślna strona startowa aplikacji (trasa `/` szkieletu: view('welcome')). Aplikacja zastępuje ją własnym resources/views/welcome.blade.php. Jedna strona bez akcji: marka, funkcje Core jako ikona, tytuł i opis, stopka z logo Laravela i wersją admiro; rama .ss-frame z dwiema kartami; w karcie marki logo simplysmart na środku, pod nim siatka kafelków z subtelnym gradientem i logo zarejestrowanych stref (View\ZoneLogos). Nazwy simplysmart i admiro wyróżnia View\BrandNames. --}}
@php($highlight = \Simplysmart\Admiro\View\BrandNames::highlight(...))
@php($features = ['zones' => 'zones', 'sign_in' => 'sign-in', 'dictionaries' => 'dictionaries', 'history' => 'history', 'locale' => 'locale', 'standards' => 'standards'])
<x-admiro::page :title="config('app.name')" :description="text('welcome.lead')">
    <x-slot:head>
        <x-admiro::styles />
    </x-slot:head>

    <div class="ss-welcome">
        <main class="ss-frame ss-frame--row ss-welcome__card">
            <section class="ss-card ss-welcome__content">
                <h1 class="ss-welcome__title">{{ $highlight(text('welcome.title')) }}</h1>
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

            <aside class="ss-card ss-card--elevated ss-welcome__brand">
                <x-admiro::logo />
                <p class="ss-welcome__tagline">{!! nl2br(e(text('welcome.tagline')), false) !!}</p>
                @php($zoneLogos = app(\Simplysmart\Admiro\View\ZoneLogos::class)->svgs())
                @if ($zoneLogos !== [])
                    <ul class="ss-welcome__zones">
                        @foreach ($zoneLogos as $zoneLogo)
                            <li class="ss-welcome__zone-logo">{!! $zoneLogo !!}</li>
                        @endforeach
                    </ul>
                @endif
            </aside>
        </main>
    </div>
</x-admiro::page>

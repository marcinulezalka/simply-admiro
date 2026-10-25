{{-- Domyślna strona startowa aplikacji (trasa `/` szkieletu: view('welcome')). Aplikacja zastępuje ją własnym resources/views/welcome.blade.php. Jedna strona bez akcji: marka, funkcje Core jako ikona, tytuł i opis, stopka z logo Laravela i wersją mainboard. Nazwy simplysmart i mainboard wyróżnia View\BrandNames. --}}
@php($highlight = \Simplysmart\Mainboard\View\BrandNames::highlight(...))
@php($features = ['zones' => 'zones', 'sign_in' => 'sign-in', 'dictionaries' => 'dictionaries', 'history' => 'history', 'locale' => 'locale', 'standards' => 'standards'])
<x-mainboard::page :title="config('app.name')" :description="text('welcome.lead')">
    <x-slot:head>
        <x-mainboard::styles />
    </x-slot:head>

    <div class="ss-welcome">
        <main class="ss-welcome__card">
            <section class="ss-welcome__content">
                <h1 class="ss-welcome__title">{{ $highlight(text('welcome.title')) }}</h1>
                <p class="ss-welcome__lead ss-muted">{{ $highlight(text('welcome.lead')) }}</p>

                <h2 class="ss-welcome__heading">{{ text('welcome.inside') }}</h2>
                <ul class="ss-welcome__features">
                    @foreach ($features as $feature => $icon)
                        <li class="ss-welcome__feature">
                            <x-mainboard::icon :name="$icon" class="ss-icon--tile" />
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
                        <span class="ss-welcome__laravel">{!! \Simplysmart\Mainboard\View\CoreAssets::contents('img/vendor/laravel.svg') !!}</span>
                    </span>
                    <span class="ss-badge">{{ text('welcome.mainboard_version', ['version' => \Simplysmart\Mainboard\View\CoreVersion::current()]) }}</span>
                </footer>
            </section>

            <aside class="ss-welcome__brand">
                <x-mainboard::logo />
                <p class="ss-welcome__tagline">{{ text('welcome.tagline') }}</p>
            </aside>
        </main>
    </div>
</x-mainboard::page>

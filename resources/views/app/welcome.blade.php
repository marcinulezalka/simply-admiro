{{-- Domyślna strona startowa aplikacji (trasa `/` szkieletu: view('welcome')). Aplikacja zastępuje ją własnym resources/views/welcome.blade.php. Jedna strona bez akcji: marka i krótki opis tego, co daje Core. --}}
<x-mainboard::page :title="config('app.name')" :description="text('welcome.lead')">
    <x-slot:head>
        <x-mainboard::styles />
    </x-slot:head>

    <div class="ss-welcome">
        <main class="ss-welcome__card">
            <section class="ss-welcome__content">
                <h1 class="ss-welcome__title">{{ text('welcome.title') }}</h1>
                <p class="ss-muted">{{ text('welcome.lead') }}</p>

                <h2 class="ss-welcome__heading">{{ text('welcome.inside') }}</h2>
                <ul class="ss-welcome__list">
                    @foreach (['zones', 'sign_in', 'dictionaries', 'history', 'locale'] as $feature)
                        <li>{{ text('welcome.features.' . $feature) }}</li>
                    @endforeach
                </ul>

                <p class="ss-welcome__note ss-muted">{{ text('welcome.override') }}</p>
            </section>

            <aside class="ss-welcome__brand">
                <x-mainboard::logo />
                <p class="ss-welcome__tagline">{{ text('welcome.tagline') }}</p>
            </aside>
        </main>
    </div>
</x-mainboard::page>

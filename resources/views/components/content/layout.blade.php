{{-- Układ strony treści (np. polityka prywatności, regulamin; docs/design-system.md §5) na szablonie bazowym x-admiro::page: pasek x-admiro::content.bar (powrót i odnośniki cookies, bez przełącznika motywu) nad i pod treścią, spis treści w karcie po lewej (na telefonie nad treścią, na szerokim ekranie przyklejony), karta dokumentu z nagłówkiem (eyebrow, tytuł z opcjonalną ikoną — prop icon, nazwa ikony x-admiro::icon — lead) i treścią w .ss-prose oraz opcjonalna karta „Zobacz także” (slot related, np. lista .ss-related). back: adres powrotu (bez niego pasek ma tylko przełącznik); toc: list<array{id: string, label: string}> — id nagłówków sekcji w treści; robots: np. "noindex, nofollow" (strony treści są domyślnie indeksowane). W strefie arkusze strefy ($zoneStylesheets z ZonesServiceProvider) po stylach Core. --}}
@props(['title', 'icon' => null, 'description' => null, 'eyebrow' => null, 'lead' => null, 'back' => null, 'toc' => [], 'robots' => null])
<x-admiro::page :title="$title" :description="$description ?? $lead" :robots="$robots">
    <x-slot:head>
        <x-admiro::styles />
        <x-admiro::zone-styles :paths="$zoneStylesheets ?? []" />
    </x-slot:head>

    <div class="ss-content-page">
        <x-admiro::content.bar :back="$back" />

        <div class="ss-content-page__layout">
            @if ($toc !== [])
                <aside class="ss-content-page__toc">
                    <nav class="ss-card ss-toc" aria-labelledby="ss-toc-title">
                        <p class="ss-toc__title" id="ss-toc-title">{{ text('layout.content.toc') }}</p>
                        <ol class="ss-toc__list">
                            @foreach ($toc as $entry)
                                <li><a class="ss-toc__link" href="#{{ $entry['id'] }}">{{ $entry['label'] }}</a></li>
                            @endforeach
                        </ol>
                    </nav>
                </aside>
            @endif

            <main class="ss-content-page__main">
                <article class="ss-card ss-content-page__card">
                    <header class="ss-content-page__header">
                        @if (filled($eyebrow))
                            <p class="ss-content-page__eyebrow">{{ $eyebrow }}</p>
                        @endif
                        <h1 class="ss-content-page__title">@if (filled($icon))<x-admiro::icon :name="$icon" class="ss-content-page__title-icon" />@endif{{ $title }}</h1>
                        @if (filled($lead))
                            <p class="ss-content-page__lead">{{ $lead }}</p>
                        @endif
                    </header>

                    <div class="ss-prose">{{ $slot }}</div>
                </article>

                @isset($related)
                    <section class="ss-card" aria-labelledby="ss-related-title">
                        <h2 class="ss-card__title" id="ss-related-title">{{ text('layout.content.see_also') }}</h2>
                        {{ $related }}
                    </section>
                @endisset
            </main>
        </div>

        <x-admiro::content.bar :back="$back" />
    </div>
</x-admiro::page>

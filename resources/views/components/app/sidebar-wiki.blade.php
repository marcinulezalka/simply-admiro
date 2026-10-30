{{-- Prawy sidebar wiki i podpowiedzi layoutu stref z kolumnami (.ss-sidebar-wiki, x-admiro::app.columns; js/sidebar-wiki.js). Wiki i podpowiedzi strony dodaje się wyłącznie w ramach sidebar-wiki: slot `sidebar-wiki` w x-admiro::zone.layout (np. wpisy słownika x-admiro::wiki-tip), a nie w treści strony; bez slotu — tekst o braku podpowiedzi. Pinezka (aria-pressed) przypina panel jako stałą prawą kolumnę na wysokość okna z własnym przewijaniem (stan w przeglądarce, tylko na szerokim ekranie); nieprzypięty panel chowa się za wąską zakładką przy prawej krawędzi, która wysuwa go nad treść (×, Esc albo klik obok chowają). Bez skryptu panel stoi przypięty, bez przycisków. Slot: zawartość sidebaru. --}}
<aside class="ss-sidebar-wiki" data-ss-sidebar-wiki aria-labelledby="ss-sidebar-wiki-title">
    <button class="ss-sidebar-wiki__rail" type="button" data-ss-sidebar-wiki-open aria-controls="ss-sidebar-wiki-panel" aria-expanded="false" title="{{ text('layout.sidebar_wiki_open') }}" hidden>
        <x-admiro::icon name="help-circle" class="ss-sidebar-wiki__rail-icon" />
        <span class="ss-sidebar-wiki__rail-label">{{ text('layout.sidebar_wiki') }}</span>
    </button>
    <div class="ss-sidebar-wiki__panel" id="ss-sidebar-wiki-panel" data-ss-sidebar-wiki-panel>
        <div class="ss-sidebar-wiki__head">
            <p class="ss-sidebar-wiki__title" id="ss-sidebar-wiki-title"><x-admiro::icon name="help-circle" class="ss-sidebar-wiki__title-icon" />{{ text('layout.sidebar_wiki') }}</p>
            <button class="ss-icon-button ss-sidebar-wiki__pin" type="button" data-ss-sidebar-wiki-pin aria-pressed="false" title="{{ text('layout.sidebar_wiki_pin') }}" hidden><x-admiro::icon name="pin" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.sidebar_wiki_pin') }}</span></button>
            <button class="ss-icon-button ss-sidebar-wiki__close" type="button" data-ss-sidebar-wiki-close title="{{ text('layout.close') }}" hidden><x-admiro::icon name="close" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.close') }}</span></button>
        </div>
        <div class="ss-sidebar-wiki__body" tabindex="0">
            @if ($slot->isEmpty())
                <p class="ss-sidebar-wiki__empty">{{ text('layout.sidebar_wiki_empty') }}</p>
            @else
                {{ $slot }}
            @endif
        </div>
    </div>
</aside>

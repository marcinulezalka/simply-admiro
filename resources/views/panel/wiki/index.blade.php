{{-- Słownik pojęć (WikiController): opis, wyszukiwarka (GET `q`) i wpisy jako porady i Q&A (x-admiro::wiki-tip, każdy z kotwicą #wiki-<klucz>); bez wyników — komunikat. $term: string, $entries: list<Wiki\WikiEntry>. --}}
<x-admiro::zone.layout :title="text('wiki.title')">
    <div class="ss-wiki">
        <p class="ss-muted">{{ text('wiki.lead') }}</p>
        <form class="ss-search" role="search" method="GET" action="{{ route('admiro.wiki') }}">
            <label class="ss-sr-only" for="ss-wiki-search">{{ text('wiki.search_label') }}</label>
            <x-admiro::icon name="search" class="ss-search__icon" />
            <input class="ss-search__input" id="ss-wiki-search" name="q" type="search" value="{{ $term }}" placeholder="{{ text('wiki.search_placeholder') }}" autocomplete="off">
        </form>
        @if ($entries === [])
            <p class="ss-notice">{{ text('wiki.none', ['term' => $term]) }}</p>
        @else
            <ul class="ss-wiki__list">
                @foreach ($entries as $entry)
                    <li><x-admiro::wiki-tip :term="$entry->key" /></li>
                @endforeach
            </ul>
        @endif
    </div>
</x-admiro::zone.layout>

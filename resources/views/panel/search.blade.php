{{-- Wszystkie wyniki globalnej wyszukiwarki strefy administratorów (SearchController::index): grupy wyników (języki z flagami, kraje, waluty, organizacje, administratorzy, strony panelu) jako listy kart-odnośników; bez tekstu — zachęta do wpisania, bez wyników — komunikat. $term: string; $groups: list<View\Navigation\SearchResultGroup>. --}}
<x-admiro::zone.layout :title="$term === '' ? text('admiro::search.title') : text('admiro::search.results_for', ['term' => $term])">
    @if ($term === '')
        <p class="ss-notice">{{ text('admiro::search.prompt') }}</p>
    @elseif ($groups === [])
        <p class="ss-notice">{{ text('admiro::search.empty', ['term' => $term]) }}</p>
    @else
        @foreach ($groups as $index => $group)
            <section class="ss-card ss-search-results" aria-labelledby="ss-search-results-{{ $index }}">
                <h2 class="ss-card__title" id="ss-search-results-{{ $index }}">{{ $group->label }} <span class="ss-badge">{{ count($group->results) }}</span></h2>
                <ul class="ss-search-results__list">
                    @foreach ($group->results as $result)
                        <li>
                            <a class="ss-search__option" href="{{ $result->url }}">
                                <span class="ss-search__option-mark" aria-hidden="true">
                                    @if ($result->flags !== [])
                                        @foreach (array_slice($result->flags, 0, 3) as $flag)
                                            <x-admiro::flag :icon="$flag" />
                                        @endforeach
                                    @elseif ($result->icon !== null)
                                        <x-admiro::icon :name="$result->icon" class="ss-search__option-icon" />
                                    @endif
                                </span>
                                <span class="ss-search__option-text">
                                    <span class="ss-search__option-label">{{ $result->label }}</span>
                                    @if (filled($result->description))
                                        <span class="ss-search__option-description">{{ $result->description }}</span>
                                    @endif
                                </span>
                            </a>
                        </li>
                    @endforeach
                </ul>
                @if ($group->moreUrl !== null)
                    <a class="ss-link" href="{{ $group->moreUrl }}">{{ text('layout.search_more') }}</a>
                @endif
            </section>
        @endforeach
    @endif
</x-admiro::zone.layout>

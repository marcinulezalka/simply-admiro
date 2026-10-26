{{-- Podpowiedzi globalnej wyszukiwarki (fragment HTML wstawiany przez js/global-search.js do .ss-search__suggestions): grupy wyników z nagłówkiem, każdy wynik jako opcja listy (role="option") z flagami albo ikoną, etykietą i opisem; pod grupą odnośnik do wszystkich wyników, gdy jest ich więcej; bez wyników — komunikat. $groups: list<View\Navigation\SearchResultGroup>; $allUrl: adres strony wszystkich wyników. --}}
@if ($groups === [])
    <p class="ss-search__empty">{{ text('layout.search_empty') }}</p>
@else
    @foreach ($groups as $groupIndex => $group)
        <div class="ss-search__group" role="group" aria-labelledby="ss-search-group-{{ $groupIndex }}">
            <p class="ss-search__group-title" id="ss-search-group-{{ $groupIndex }}">{{ $group->label }}</p>
            @foreach ($group->results as $resultIndex => $result)
                <a class="ss-search__option" id="ss-search-option-{{ $groupIndex }}-{{ $resultIndex }}" role="option" aria-selected="false" href="{{ $result->url }}" data-ss-search-option>
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
            @endforeach
            @if ($group->moreUrl !== null)
                <a class="ss-search__more" href="{{ $group->moreUrl }}">{{ text('layout.search_more') }}</a>
            @endif
        </div>
    @endforeach
@endif
<a class="ss-search__all" href="{{ $allUrl }}">{{ text('layout.search_all') }}</a>

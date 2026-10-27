{{-- Podpowiedzi globalnej wyszukiwarki (fragment HTML wstawiany przez js/global-search.js do .ss-search__suggestions): grupy wyników z nagłówkiem, każdy wynik jako opcja listy (role="option") z flagami albo ikoną, etykietą i opisem, a obok niego przełącznik stanu (toggleUrl, zapis POST przez js/post-toggle.js i przeładowanie strony — formularza nie da się zagnieździć w formularzu wyszukiwarki) albo plakietka (badge, np. „Interfejs”), a przy wpisie domyślnym (badgeIcon) sama wypełniona ikona .ss-default-mark z nazwą w podpowiedzi; pod grupą odnośnik do wszystkich wyników, gdy jest ich więcej; bez wyników — komunikat. $groups: list<View\Navigation\SearchResultGroup>; $allUrl: adres strony wszystkich wyników. --}}
@if ($groups === [])
    <p class="ss-search__empty">{{ text('layout.search_empty') }}</p>
@else
    @foreach ($groups as $groupIndex => $group)
        <div class="ss-search__group" role="group" aria-labelledby="ss-search-group-{{ $groupIndex }}">
            <p class="ss-search__group-title" id="ss-search-group-{{ $groupIndex }}">{{ $group->label }}</p>
            @foreach ($group->results as $resultIndex => $result)
                @if ($result->toggleUrl !== null || $result->badge !== null)
                    <div class="ss-search__row">
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
                        @if ($result->badge !== null)
                            @if ($result->badgeIcon !== null)<span class="ss-default-mark" role="img" aria-label="{{ $result->badge }}" title="{{ $result->badge }}"><x-admiro::icon :name="$result->badgeIcon" class="ss-default-mark__icon" /></span>@else<span class="ss-badge ss-badge--outline ss-badge--info">{{ $result->badge }}</span>@endif
                        @else
                            <x-admiro::switch :id="'ss-search-toggle-' . $groupIndex . '-' . $resultIndex" :label="$result->label" :checked="$result->active" hide-label data-ss-post-toggle="{{ $result->toggleUrl }}" />
                        @endif
                    </div>
                @else
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
                @endif
            @endforeach
            @if ($group->moreUrl !== null)
                <a class="ss-search__more" href="{{ $group->moreUrl }}">{{ text('layout.search_more') }}</a>
            @endif
        </div>
    @endforeach
@endif
<a class="ss-search__all" href="{{ $allUrl }}">{{ text('layout.search_all') }}</a>

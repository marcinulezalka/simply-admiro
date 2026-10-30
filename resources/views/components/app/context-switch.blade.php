{{-- Przełącznik kontekstu konta (organizacja, rola) w layoucie aplikacji: przycisk z ikoną `context` (postać na tle z ukośnymi liniami, Streamline Plump) otwiera menu (<details data-ss-dropdown>) z tytułem „Przełącz kontekst” i grupami pozycji — formularze POST (NavigationForm) albo odnośniki, bieżąca z haczykiem i aria-current. contexts: list<View\Navigation\NavigationGroup>; toggleClass, iconClass, labelClass: klasy przycisku, ikony i etykiety (domyślnie tylko dla czytników ekranu) (ikona w menu bocznym albo przycisk w stopce Workspace, menu nad stopką). tree: role pod nazwą organizacji z wcięciem i linią drzewa (.ss-context-switch--tree), żeby było widać, do której organizacji należy każdy obszar. Identyfikator NavigationItem::CONTEXT_SWITCH_ANCHOR: odnośnik do niego (np. nad tytułem strony) otwiera menu (js/app-shell.js). --}}
@props(['contexts', 'toggleClass' => 'ss-icon-button', 'iconClass' => 'ss-icon-button__icon', 'labelClass' => 'ss-sr-only', 'tree' => false])
<details @class(['ss-account-menu', 'ss-context-switch', 'ss-context-switch--tree' => $tree]) id="{{ \Simplysmart\Admiro\View\Navigation\NavigationItem::CONTEXT_SWITCH_ANCHOR }}" data-ss-dropdown>
    <summary class="{{ $toggleClass }} ss-context-switch__toggle" @if ($labelClass === 'ss-sr-only') title="{{ text('layout.switch_context') }}" @endif>
        <x-admiro::icon name="context" :class="$iconClass" />
        <span class="{{ $labelClass }}">{{ text('layout.switch_context') }}</span>
    </summary>
    <div class="ss-account-menu__panel ss-sidebar__panel">
        <p class="ss-context-switch__title"><x-admiro::icon name="context" class="ss-context-switch__title-icon" />{{ text('layout.switch_context') }}</p>
        @foreach ($contexts as $group)
            @if (filled($group->label))
                <p class="ss-context-switch__group">@if ($group->icon !== null)<span class="ss-context-switch__kind" @if (filled($group->iconLabel)) title="{{ $group->iconLabel }}" @endif><x-admiro::icon :name="$group->icon" class="ss-context-switch__kind-icon" />@if (filled($group->iconLabel))<span class="ss-sr-only">{{ $group->iconLabel }}: </span>@endif</span>@endif{{ $group->label }}</p>
            @endif
            <ul class="ss-account-menu__list">
                @foreach ($group->items as $item)
                    <li>
                        @if ($item instanceof \Simplysmart\Admiro\View\Navigation\NavigationForm)
                            <form method="POST" action="{{ $item->action }}">
                                @csrf
                                <input type="hidden" name="{{ $item->field }}" value="{{ $item->value }}">
                                <button class="ss-account-menu__link" type="submit" @if ($item->current) aria-current="true" @endif>
                                    <x-admiro::icon :name="$item->icon" class="ss-account-menu__icon" />
                                    <span>{{ $item->label }}@if ($item->current)<span class="ss-sr-only"> ({{ text('layout.current') }})</span>@endif</span>
                                    @if ($item->current)
                                        <x-admiro::icon name="check" class="ss-context-switch__check" />
                                    @endif
                                </button>
                            </form>
                        @else
                            <a class="ss-account-menu__link" href="{{ $item->url }}" @if ($item->current) aria-current="true" @endif>
                                <x-admiro::icon :name="$item->icon" class="ss-account-menu__icon" />
                                <span>{{ $item->label }}@if ($item->current)<span class="ss-sr-only"> ({{ text('layout.current') }})</span>@endif</span>
                                @if ($item->current)
                                    <x-admiro::icon name="check" class="ss-context-switch__check" />
                                @endif
                            </a>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endforeach
    </div>
</details>

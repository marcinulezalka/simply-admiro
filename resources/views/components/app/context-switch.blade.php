{{-- Przełącznik kontekstu konta (organizacja, rola) w layoucie aplikacji: przycisk z ikoną `switch` otwiera menu (<details data-ss-dropdown>) z grupami pozycji — formularze POST (NavigationForm) albo odnośniki, bieżąca z haczykiem i aria-current. contexts: list<View\Navigation\NavigationGroup>; toggleClass, iconClass, labelClass: klasy przycisku, ikony i etykiety (domyślnie tylko dla czytników ekranu) (ikona w menu bocznym albo przycisk w stopce Workspace, menu nad stopką). Identyfikator NavigationItem::CONTEXT_SWITCH_ANCHOR: odnośnik do niego (np. nad tytułem strony) otwiera menu (js/app-shell.js). --}}
@props(['contexts', 'toggleClass' => 'ss-icon-button', 'iconClass' => 'ss-icon-button__icon', 'labelClass' => 'ss-sr-only'])
<details class="ss-account-menu ss-context-switch" id="{{ \Simplysmart\Admiro\View\Navigation\NavigationItem::CONTEXT_SWITCH_ANCHOR }}" data-ss-dropdown>
    <summary class="{{ $toggleClass }} ss-context-switch__toggle" @if ($labelClass === 'ss-sr-only') title="{{ text('layout.switch_context') }}" @endif>
        <x-admiro::icon name="switch" :class="$iconClass" />
        <span class="{{ $labelClass }}">{{ text('layout.switch_context') }}</span>
    </summary>
    <div class="ss-account-menu__panel ss-sidebar__panel">
        @foreach ($contexts as $group)
            @if (filled($group->label))
                <p class="ss-context-switch__group">{{ $group->label }}</p>
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

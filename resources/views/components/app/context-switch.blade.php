{{-- Przełącznik kontekstu konta (organizacja, rola) w layoucie aplikacji: przycisk z ikoną `context` (postać na tle z ukośnymi liniami, Streamline Plump) otwiera menu (<details data-ss-dropdown>) z listą kontekstów x-admiro::app.context-list (tytuł „Przełącz kontekst”, grupy, pozycje — formularze POST albo odnośniki, bieżąca z haczykiem i aria-current). contexts: list<View\Navigation\NavigationGroup>; toggleClass, iconClass, labelClass: klasy przycisku, ikony i etykiety (domyślnie tylko dla czytników ekranu) (ikona w menu bocznym albo przycisk w stopce Workspace, menu nad stopką). tree: role pod nazwą organizacji z wcięciem i linią drzewa (.ss-context-switch--tree), żeby było widać, do której organizacji należy każdy obszar. Identyfikator NavigationItem::CONTEXT_SWITCH_ANCHOR: odnośnik do niego (np. nad tytułem strony) otwiera menu (js/app-shell.js). --}}
@props(['contexts', 'toggleClass' => 'ss-icon-button', 'iconClass' => 'ss-icon-button__icon', 'labelClass' => 'ss-sr-only', 'tree' => false])
<details @class(['ss-account-menu', 'ss-context-switch', 'ss-context-switch--tree' => $tree]) id="{{ \Simplysmart\Admiro\View\Navigation\NavigationItem::CONTEXT_SWITCH_ANCHOR }}" data-ss-dropdown>
    <summary class="{{ $toggleClass }} ss-context-switch__toggle" @if ($labelClass === 'ss-sr-only') title="{{ text('layout.switch_context') }}" @endif>
        <x-admiro::icon name="context" :class="$iconClass" />
        <span class="{{ $labelClass }}">{{ text('layout.switch_context') }}</span>
    </summary>
    <div class="ss-account-menu__panel ss-sidebar__panel">
        <x-admiro::app.context-list :contexts="$contexts" />
    </div>
</details>

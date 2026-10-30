{{-- Lista kontekstów konta (tytuł „Przełącz kontekst” i grupy — organizacja z ikoną rodzaju, pod nią role — z pozycjami: formularze POST NavigationForm albo odnośniki, bieżąca z haczykiem i aria-current): treść menu przełącznika (x-admiro::app.context-switch) i np. odwrotu wizytówki. contexts: list<View\Navigation\NavigationGroup>. --}}
@props(['contexts'])
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

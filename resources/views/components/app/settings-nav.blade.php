{{-- Kategorie ustawień jako lista kart-odnośników (.ss-settings-area__nav): grupy menu ustawień z tytułem, każda pozycja z ikoną w kafelku, bieżąca z ramką akcentu — wygląd listy kroków kreatora. Lewa kolumna obszaru ustawień (x-admiro::app.settings-area) i layoutu stref z kolumnami (x-admiro::app.columns). settings: View\Navigation\SettingsMenu. --}}
@props(['settings'])
<nav class="ss-settings-area__nav" aria-label="{{ text('layout.settings') }}">
    @foreach ($settings->groups as $group)
        <div class="ss-settings-area__group">
            @if (filled($group->label))
                <p class="ss-settings-area__group-title">{{ $group->label }}</p>
            @endif
            <ul class="ss-settings-area__list">
                @foreach ($group->items as $item)
                    @if ($item instanceof \Simplysmart\Admiro\View\Navigation\NavigationItem)
                        <li><a class="ss-settings-area__link" href="{{ $item->url }}" @if ($item->current) aria-current="page" @endif><x-admiro::icon :name="$item->icon" class="ss-icon--tile ss-settings-area__icon" /><span class="ss-settings-area__label">{{ $item->label }}</span></a></li>
                    @endif
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>

{{-- Strona ustawień jako jeden obszar jak ustawienia systemu (.ss-settings-area, layouty x-admiro::app.layout Workspace i x-admiro::app.zone-layout): po lewej grupy menu ustawień jako lista kategorii (karty-odnośniki z ikoną w kafelku, bieżąca z ramką akcentu — wygląd listy kroków kreatora), po prawej treść strony (slot). settings: View\Navigation\SettingsMenu. --}}
@props(['settings'])
<div class="ss-settings-area">
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
    <div class="ss-settings-area__panel">
        {{ $slot }}
    </div>
</div>

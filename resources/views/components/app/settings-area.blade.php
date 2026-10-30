{{-- Strona ustawień jako jeden obszar jak ustawienia systemu (.ss-settings-area, layouty x-admiro::app.layout Workspace i x-admiro::app.zone-layout): po lewej kategorie ustawień (x-admiro::app.settings-nav), po prawej treść strony (slot). settings: View\Navigation\SettingsMenu. --}}
@props(['settings'])
<div class="ss-settings-area">
    <x-admiro::app.settings-nav :settings="$settings" />
    <div class="ss-settings-area__panel">
        {{ $slot }}
    </div>
</div>

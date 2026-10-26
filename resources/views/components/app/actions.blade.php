{{-- Akcje konta pod nazwą konta w menu bocznym layoutu aplikacji (wariant Sidebar): zwijanie menu, przełącznik kontekstu (x-admiro::app.context-switch: organizacja, rola), ustawienia (settings) i powiadomienia (x-admiro::app.notifications). W wariancie Workspace te akcje są na karcie konta. contexts: ?list<NavigationGroup>; settings: ?SettingsMenu; notifications: ?NotificationPanel. --}}
@props(['contexts' => null, 'settings' => null, 'notifications' => null])
<button class="ss-icon-button ss-sidebar__collapse" type="button" aria-controls="ss-sidebar" aria-expanded="true" data-ss-sidebar-toggle>
    <x-admiro::icon name="sidebar" class="ss-icon-button__icon" />
    <span class="ss-sr-only">{{ text('layout.toggle_menu') }}</span>
</button>

@if ($contexts !== null && $contexts !== [])
    <x-admiro::app.context-switch :contexts="$contexts" />
@endif

@if ($settings !== null)
    <a class="ss-icon-button ss-sidebar__settings-toggle" href="{{ $settings->url }}" title="{{ text('layout.settings') }}" @if ($settings->active) aria-current="page" @endif>
        <x-admiro::icon name="settings" class="ss-icon-button__icon" />
        <span class="ss-sr-only">{{ text('layout.settings') }}</span>
    </a>
@endif

@if ($notifications !== null)
    <x-admiro::app.notifications :notifications="$notifications" />
@endif

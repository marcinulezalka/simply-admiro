{{-- Treść layoutu stref z kolumnami (Enums\AppLayout::Columns, .ss-columns; x-admiro::app.zone-layout z `columns`): po lewej menu strony w pudełku na wysokość okna z własnym przewijaniem — także palcem na ekranie dotykowym (__left; slot `left`, a bez niego na stronie ustawień kategorie x-admiro::app.settings-nav; bez obu kolumny nie ma), na środku zwykła treść (__content, slot) i po prawej sidebar wiki i podpowiedzi (x-admiro::app.sidebar-wiki ze slotu `sidebarWiki`). Węziej niż 64rem menu stoi nad treścią (do połowy wysokości okna), a sidebar zawsze się wysuwa. left, sidebarWiki: ?Illuminate\View\ComponentSlot; settings: ?View\Navigation\SettingsMenu (aktywne menu ustawień). --}}
@props(['left' => null, 'sidebarWiki' => null, 'settings' => null])
@php($hasLeft = ($left !== null && $left->isNotEmpty()) || $settings !== null)
<div @class(['ss-columns', 'ss-columns--with-left' => $hasLeft])>
    @if ($hasLeft)
        <div class="ss-columns__left" role="region" aria-label="{{ text('layout.columns_left') }}" tabindex="0">
            @if ($left !== null && $left->isNotEmpty())
                {{ $left }}
            @else
                <x-admiro::app.settings-nav :settings="$settings" />
            @endif
        </div>
    @endif
    <div class="ss-columns__content">
        {{ $slot }}
    </div>
    <x-admiro::app.sidebar-wiki>{{ $sidebarWiki ?? '' }}</x-admiro::app.sidebar-wiki>
</div>

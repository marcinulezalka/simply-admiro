{{-- Nawigacja design systemu w karcie, w dwóch grupach: fundamenty (sekcje strony głównej) i gotowe widoki z katalogu (Contracts\DesignSystemCatalog: Core i paczki stref) w rozwijanych obszarach (<details>: obszar bieżącego widoku rozwinięty). $sections: lista kotwic sekcji albo [] poza stroną główną; $current: klucz bieżącego widoku albo null. --}}
@props(['sections' => [], 'current' => null])
<nav {{ $attributes->merge(['class' => 'ss-card ss-ds__nav']) }} aria-label="{{ text('design_system.title') }}">
    <p class="ss-ds__nav-heading">{{ text('design_system.nav_foundations') }}</p>
    <ul class="ss-ds__nav-list">
        @foreach ($sections as $section)
            <li><a class="ss-ds__nav-link" href="#{{ $section }}">{{ text('design_system.sections.' . $section) }}</a></li>
        @endforeach
        @if ($sections === [])
            <li><a class="ss-ds__nav-link" href="{{ route('mainboard.design-system') }}">{{ text('design_system.back') }}</a></li>
        @endif
    </ul>

    <p class="ss-ds__nav-heading">{{ text('design_system.nav_views') }}</p>
    @foreach (app(\Simplysmart\Mainboard\Contracts\DesignSystemCatalog::class)->areas() as $area)
        <details class="ss-ds__nav-group" @if ($area->contains($current)) open @endif>
            <summary class="ss-ds__nav-summary">{{ $area->label() }}</summary>
            <ul class="ss-ds__nav-list">
                @foreach ($area->views as $view)
                    <li><a class="ss-ds__nav-link" href="{{ route('mainboard.design-system.view', $view->key) }}" @if ($view->key === $current) aria-current="page" @endif>{{ $view->label() }}</a></li>
                @endforeach
            </ul>
        </details>
    @endforeach
</nav>

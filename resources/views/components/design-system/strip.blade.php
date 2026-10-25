{{-- Pasek podglądu nad widokiem na pełnym layoutie aplikacji (slot before x-admiro::app.layout): powrót do design systemu i widoki z tego samego obszaru katalogu jako pigułki. $current: klucz bieżącego widoku. --}}
@props(['current'])
@php($area = collect(app(\Simplysmart\Admiro\Contracts\DesignSystemCatalog::class)->areas())->first(fn ($area) => $area->contains($current)))
<nav class="ss-ds-strip" aria-label="{{ text('design_system.strip_label') }}">
    <x-admiro::theme-toggle />
    <a class="ss-ds-strip__back ss-link" href="{{ route('simplysmart.design-system') }}"><x-admiro::icon name="arrow-left" class="ss-ds-strip__icon" />{{ text('design_system.back') }}</a>
    @if ($area !== null)
        <span class="ss-ds-strip__area">{{ $area->label() }}</span>
        <ul class="ss-ds-strip__views">
            @foreach ($area->views as $view)
                <li><a class="ss-ds__nav-link" href="{{ route('simplysmart.design-system.view', $view->key) }}" @if ($view->key === $current) aria-current="page" @endif>{{ $view->label() }}</a></li>
            @endforeach
        </ul>
    @endif
</nav>

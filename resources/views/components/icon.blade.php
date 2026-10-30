{{-- Ikona liniowa Core osadzona inline z public/assets/img/icons/<name>.svg (stroke="currentColor", dekoracyjna: aria-hidden); brak pliku to pusta ikona zgłoszona do logu (View\CoreAssets::icon()), nie błąd strony. Rozmiar ustala klasa .ss-icon albo rodzic. --}}
@props(['name'])
@if (preg_match('/^[a-z][a-z-]*$/', $name) !== 1)
    @php(throw new \InvalidArgumentException("Invalid icon name [{$name}]."))
@endif
<span {{ $attributes->merge(['class' => 'ss-icon']) }}>{!! \Simplysmart\Admiro\View\CoreAssets::icon($name) !!}</span>

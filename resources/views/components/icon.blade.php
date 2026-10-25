{{-- Ikona liniowa Core osadzona inline z public/assets/img/icons/<name>.svg (stroke="currentColor", dekoracyjna: aria-hidden). Rozmiar ustala klasa .ss-icon albo rodzic. --}}
@props(['name'])
@if (preg_match('/^[a-z][a-z-]*$/', $name) !== 1)
    @php(throw new \InvalidArgumentException("Invalid icon name [{$name}]."))
@endif
<span {{ $attributes->merge(['class' => 'ss-icon']) }}>{!! \Simplysmart\Admiro\View\CoreAssets::contents('img/icons/' . $name . '.svg') !!}</span>

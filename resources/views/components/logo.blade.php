{{-- Logotyp simplysmart osadzony inline (fill="currentColor": przyjmuje kolor tekstu, sam przełącza się w motywie ciemnym). Szerokość ustala klasa .ss-logo albo rodzic. --}}
<span {{ $attributes->merge(['class' => 'ss-logo']) }}>{!! \Simplysmart\Mainboard\View\CoreAssets::contents('img/brand/logo.svg') !!}</span>

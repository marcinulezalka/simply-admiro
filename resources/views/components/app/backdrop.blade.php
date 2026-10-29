{{-- Tło layoutu aplikacji (bazowe płótno): duży logotyp simplysmart (x-admiro::logo) na środku okna — albo logo strefy (logo: treść SVG z fill="currentColor", zone `backdrop` = zone-logo, Enums\AppBackdrop) dolną krawędzią równo z dołem okna, przycięte stopką na dole (.ss-backdrop--bottom) — pod treścią, wtopiony w płótno jak litery wycięte w papierze — lico liter w kolorze płótna z delikatnym ukośnym prążkowaniem (wzór #ss-backdrop-grain), wewnętrzny cień od góry z lewej i jasna krawędź od dołu z prawej (filtr #ss-backdrop-deboss); kolory z tokenów w CSS (.ss-backdrop), więc działa w motywie jasnym i ciemnym, bez zmiany proporcji. Czysta dekoracja (aria-hidden); bez motywów wysokiego kontrastu i wydruku. --}}
@props(['logo' => null])
<div @class(['ss-backdrop', 'ss-backdrop--bottom' => $logo !== null]) aria-hidden="true">
    <svg class="ss-backdrop__defs" xmlns="http://www.w3.org/2000/svg" width="0" height="0" focusable="false">
        <defs>
            <pattern id="ss-backdrop-grain" width="3" height="3" patternUnits="userSpaceOnUse" patternTransform="rotate(-38)">
                <rect class="ss-backdrop__face" width="3" height="3" />
                <line class="ss-backdrop__grain" x1="0" y1="0" x2="0" y2="3" />
            </pattern>
            <filter id="ss-backdrop-deboss" x="-3%" y="-12%" width="106%" height="124%" color-interpolation-filters="sRGB">
                <feComponentTransfer in="SourceAlpha" result="hole">
                    <feFuncA type="table" tableValues="1 0" />
                </feComponentTransfer>
                <feGaussianBlur in="hole" stdDeviation="3" result="hole-blur" />
                <feOffset in="hole-blur" dx="2.4" dy="3" result="shadow-offset" />
                <feFlood class="ss-backdrop__shadow" result="shadow-color" />
                <feComposite in="shadow-color" in2="shadow-offset" operator="in" result="shadow-tinted" />
                <feComposite in="shadow-tinted" in2="SourceAlpha" operator="in" result="inner-shadow" />
                <feOffset in="hole" dx="-0.8" dy="-0.8" result="light-offset" />
                <feGaussianBlur in="light-offset" stdDeviation="0.35" result="light-blur" />
                <feFlood class="ss-backdrop__light" result="light-color" />
                <feComposite in="light-color" in2="light-blur" operator="in" result="light-tinted" />
                <feComposite in="light-tinted" in2="SourceAlpha" operator="in" result="inner-light" />
                <feMerge>
                    <feMergeNode in="SourceGraphic" />
                    <feMergeNode in="inner-shadow" />
                    <feMergeNode in="inner-light" />
                </feMerge>
            </filter>
        </defs>
    </svg>
    @if ($logo !== null)
        <span class="ss-backdrop__logo">{!! $logo !!}</span>
    @else
        <x-admiro::logo class="ss-backdrop__logo" />
    @endif
</div>

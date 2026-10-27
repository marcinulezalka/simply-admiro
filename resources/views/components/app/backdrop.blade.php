{{-- Tło layoutu aplikacji (bazowe płótno): duży logotyp simplysmart (x-admiro::logo) na środku okna, pod treścią, wtopiony w płótno — litery w kolorze marki (--ss-color-brand: czerń albo biel, zgodnie z brand/README.md) z bardzo niskim kryciem, bez zmiany proporcji. Czysta dekoracja (aria-hidden); bez motywów wysokiego kontrastu i wydruku. --}}
<div class="ss-backdrop" aria-hidden="true">
    <x-admiro::logo class="ss-backdrop__logo" />
</div>

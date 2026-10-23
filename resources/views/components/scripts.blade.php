{{-- Skrypty Core osadzone inline z nonce CSP (bez osobnego serwowania plików), jak x-mainboard::styles; pliki z public/assets/js/. --}}
@props(['files'])
<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
@foreach ($files as $file)
{!! \Simplysmart\Mainboard\View\CoreAssets::contents($file) !!}
@endforeach
</script>

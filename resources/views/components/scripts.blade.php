{{-- Skrypty Core osadzone inline z nonce CSP (bez osobnego serwowania plików), jak x-admiro::styles; pliki z public/assets/js/. --}}
@props(['files'])
<script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
@foreach ($files as $file)
{!! \Simplysmart\Admiro\View\CoreAssets::contents($file) !!}
@endforeach
</script>

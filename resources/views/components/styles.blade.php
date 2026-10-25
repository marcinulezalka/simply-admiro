{{-- Style Core osadzone inline z nonce CSP (bez osobnego serwowania plików): tokeny, baza i komponenty z public/assets/css/. --}}
@props(['files' => \Simplysmart\Admiro\View\CoreAssets::STYLESHEETS])
<style nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
@foreach ($files as $file)
{!! \Simplysmart\Admiro\View\CoreAssets::contents($file) !!}
@endforeach
</style>

{{-- Style Core osadzone inline z nonce CSP (bez osobnego serwowania plików): tokeny, baza i komponenty z public/assets/css/. --}}
@props(['files' => ['css/tokens.css', 'css/base.css', 'css/app.css']])
<style nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
@foreach ($files as $file)
{!! \Simplysmart\Mainboard\View\CoreAssets::contents($file) !!}
@endforeach
</style>

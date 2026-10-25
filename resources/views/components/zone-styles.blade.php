{{-- Arkusze CSS strefy (`stylesheets` w configu strefy, ZoneDefinition::$stylesheets) osadzone inline z nonce CSP po stylach Core: wygląd właściwy strefie bez zmian w Core. paths: list<string> (ścieżki plików). --}}
@props(['paths' => []])
@if ($paths !== [])
    <style nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
@foreach ($paths as $path)
{!! file_get_contents($path) !!}
@endforeach
    </style>
@endif

{{-- Układ stron logowania stref (bez skryptów i stylów inline, zgodnie z CSP). --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? text('sign_in.title') }}</title>
</head>
<body>
    <main>
        <h1>{{ $title ?? text('sign_in.title') }}</h1>

        @if (session('status'))
            <p role="status">{{ session('status') }}</p>
        @endif

        {{ $slot }}
    </main>
</body>
</html>

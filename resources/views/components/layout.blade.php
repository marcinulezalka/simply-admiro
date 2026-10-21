{{-- Układ stron strefy administratorów (bez skryptów i stylów inline, zgodnie z CSP). --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
</head>
<body>
    <header>
        <form method="POST" action="{{ route('admiro.auth.logout') }}">
            @csrf
            <button type="submit">{{ text('sign_in.sign_out') }}</button>
        </form>
    </header>
    <main>
        <h1>{{ $title }}</h1>

        {{ $slot }}
    </main>
</body>
</html>

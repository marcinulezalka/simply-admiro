{{-- Układ stron strefy (bez skryptów i stylów inline, zgodnie z CSP). $zone i $account (konto zalogowane w strefie albo null) daje Core. --}}
@props(['title'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if ($zone->requiresLogin())
        <meta name="robots" content="noindex, nofollow">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
</head>
<body>
    @if ($account !== null)
        <header>
            <p>{{ text('sign_in.signed_in_as', ['name' => $account->displayName()]) }}</p>
            <form method="POST" action="{{ route($zone->routeName('auth.logout')) }}">
                @csrf
                <button type="submit">{{ text('sign_in.sign_out') }}</button>
            </form>
        </header>
    @endif
    <main>
        <h1>{{ $title }}</h1>

        {{ $slot }}
    </main>
</body>
</html>

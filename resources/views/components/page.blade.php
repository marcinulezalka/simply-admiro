{{-- Szablon bazowy każdej strony (bez skryptów i stylów inline, zgodnie z CSP): <head> z meta i treść w slocie. Layouty (auth, zone) składają się z niego. keywords: lista albo tekst; tags: lista (np. json_tags rekordu) jako meta article:tag; robots: np. "noindex, nofollow"; slot head: dodatki do <head> (np. x-mainboard::styles); palettes: list<Enums\Palette> kolorystyk strony i paletteScope: zakres ich zapamiętania (np. nazwa strefy) — meta ss-palettes dla js/theme.js, przed nim, więc wybrana kolorystyka obowiązuje bez błysku; favicons: list<array{rel: string, href: string, type: ?string, sizes: ?string}>, domyślnie ikony strefy żądania ($zoneFavicons z ZonesServiceProvider, `favicon` strefy). Na końcu <body> zgoda na cookies (x-mainboard::cookie-consent), gdy jest potrzebna. --}}
@props(['title', 'description' => null, 'keywords' => null, 'tags' => null, 'robots' => null, 'palettes' => [], 'paletteScope' => '', 'favicons' => null])
@php
    $keywordsContent = is_array($keywords) ? implode(', ', array_filter($keywords, is_string(...))) : $keywords;
    $tagList = is_array($tags) ? array_filter($tags, is_string(...)) : [];
    $favicons ??= $zoneFavicons ?? [];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @if (filled($robots))
        <meta name="robots" content="{{ $robots }}">
    @endif
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    @if ($palettes !== [])
        <meta name="ss-palettes" content="{{ implode(' ', array_map(static fn (\Simplysmart\Mainboard\Enums\Palette $palette): string => $palette->value, $palettes)) }}" data-scope="{{ $paletteScope }}">
    @endif
    <x-mainboard::scripts :files="['js/theme.js']" />
    @foreach ($favicons as $favicon)
        <link {{ new \Illuminate\View\ComponentAttributeBag(array_filter($favicon, static fn (?string $value): bool => $value !== null)) }}>
    @endforeach
    @if (filled($description))
        <meta name="description" content="{{ $description }}">
    @endif
    @if (filled($keywordsContent))
        <meta name="keywords" content="{{ $keywordsContent }}">
    @endif
    @foreach ($tagList as $tag)
        <meta property="article:tag" content="{{ $tag }}">
    @endforeach
    {{ $head ?? '' }}
</head>
<body>
    {{ $slot }}
    <x-mainboard::cookie-consent />
</body>
</html>

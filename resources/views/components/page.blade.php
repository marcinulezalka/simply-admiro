{{-- Szablon bazowy każdej strony (skrypty i style inline tylko z nonce CSP): <head> z meta i treść w slocie. Layouty (auth, zone) składają się z niego. keywords: lista albo tekst; tags: lista (np. json_tags rekordu) jako meta article:tag; robots: np. "noindex, nofollow"; slot head: dodatki do <head> (np. x-admiro::styles); palettes: list<Enums\Palette> kolorystyk strony i paletteScope: zakres ich zapamiętania (np. nazwa strefy) — meta ss-palettes dla js/theme.js, przed nim, więc wybrana kolorystyka obowiązuje bez błysku; fontStyles: list<Zones\ZoneFontStyle> krojów strefy — meta ss-fonts (ten sam zakres), tak samo bez błysku; favicons: list<array{rel: string, href: string, type: ?string, sizes: ?string}>, domyślnie ikony strefy żądania ($zoneFavicons z ZonesServiceProvider, `favicon` strefy); fontFaces: reguły @font-face (styl z nonce CSP), domyślnie fonty strefy żądania ($zoneFontFaces, `fonts` strefy). Na końcu <body> zgoda na cookies (x-admiro::cookie-consent), gdy jest potrzebna, i potwierdzanie akcji (js/confirm-action.js: usuwanie — formularze DELETE — i przyciski [data-ss-confirm] wymagają drugiego kliknięcia „Potwierdź”/„Potwierdzam”, teksty w data-ss-confirm-* na <body>). --}}
@props(['title', 'description' => null, 'keywords' => null, 'tags' => null, 'robots' => null, 'palettes' => [], 'paletteScope' => '', 'fontStyles' => [], 'favicons' => null, 'fontFaces' => null])
@php
    $keywordsContent = is_array($keywords) ? implode(', ', array_filter($keywords, is_string(...))) : $keywords;
    $tagList = is_array($tags) ? array_filter($tags, is_string(...)) : [];
    $favicons ??= $zoneFavicons ?? [];
    $fontFaces ??= $zoneFontFaces ?? '';
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
        <meta name="ss-palettes" content="{{ implode(' ', array_map(static fn (\Simplysmart\Admiro\Enums\Palette $palette): string => $palette->value, $palettes)) }}" data-scope="{{ $paletteScope }}">
    @endif
    @if ($fontStyles !== [])
        <meta name="ss-fonts" content="{{ implode(' ', array_map(static fn (\Simplysmart\Admiro\Zones\ZoneFontStyle $style): string => $style->key, $fontStyles)) }}" data-scope="{{ $paletteScope }}">
    @endif
    <x-admiro::scripts :files="['js/theme.js']" />
    @foreach ($favicons as $favicon)
        <link {{ new \Illuminate\View\ComponentAttributeBag(array_filter($favicon, static fn (?string $value): bool => $value !== null)) }}>
    @endforeach
    @if ($fontFaces !== '')
        <style nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">{!! $fontFaces !!}</style>
    @endif
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
<body data-ss-confirm-delete="{{ text('layout.confirm_delete') }}" data-ss-confirm-accept="{{ text('layout.confirm_accept') }}">
    {{ $slot }}
    <x-admiro::cookie-consent />
    <x-admiro::scripts :files="['js/confirm-action.js']" />
</body>
</html>

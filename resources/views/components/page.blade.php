{{-- Szablon bazowy każdej strony (bez skryptów i stylów inline, zgodnie z CSP): <head> z meta i treść w slocie. Layouty (auth, zone) składają się z niego. keywords: lista albo tekst; tags: lista (np. json_tags rekordu) jako meta article:tag; robots: np. "noindex, nofollow"; slot head: dodatki do <head> (np. x-mainboard::styles). --}}
@props(['title', 'description' => null, 'keywords' => null, 'tags' => null, 'robots' => null])
@php
    $keywordsContent = is_array($keywords) ? implode(', ', array_filter($keywords, is_string(...))) : $keywords;
    $tagList = is_array($tags) ? array_filter($tags, is_string(...)) : [];
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
</body>
</html>

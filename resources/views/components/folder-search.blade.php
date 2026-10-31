{{-- Wyszukiwarka w pasku teczki (Core .ss-folder__toolbar) wtopiona w tło jak pole komend menu błyskawicy (admiro::search.commands): przezroczysta pigułka z delikatną szarą obwódką, w niej ikona lupy, pole i wyciszony przycisk Enter. Formularz GET na action z parametrami params (ukryte pola) wyszukuje na żywo (js/live-search.js): podmienia tylko element wyników o id results; bez skryptu wysyła się zwykłą drogą. action: string, results: string (id elementu wyników), label: string (etykieta dla czytnika ekranu), name: string (parametr zapytania), value: string, placeholder: string, params: array<string, string>. --}}
@props(['action', 'results', 'label', 'name' => 'q', 'value' => '', 'placeholder' => '', 'params' => []])
<form class="ss-command ss-command--search" method="GET" action="{{ $action }}" role="search" data-ss-live-search="{{ $results }}">
    @foreach ($params as $param => $paramValue)
        <input type="hidden" name="{{ $param }}" value="{{ $paramValue }}">
    @endforeach
    <x-admiro::icon name="search" class="ss-command__search-icon" />
    <label class="ss-sr-only" for="{{ $results }}-search">{{ $label }}</label>
    <input class="ss-command__input" id="{{ $results }}-search" name="{{ $name }}" type="search" value="{{ $value }}" maxlength="100" autocomplete="off" placeholder="{{ $placeholder }}">
    <button class="ss-search__send ss-command__send" type="submit">
        <x-admiro::icon name="corner-down-left" class="ss-search__send-icon ss-command__send-icon" />
        <span class="ss-sr-only">{{ text('layout.search_submit') }}</span>
    </button>
</form>

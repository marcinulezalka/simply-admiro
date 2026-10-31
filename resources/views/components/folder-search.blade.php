{{-- Pole komend w pasku teczki (Core .ss-folder__toolbar) w wyglądzie pola komend menu błyskawicy (admiro::search.commands): białe pole z delikatną szarą obwódką, po lewej jedna szara pigułka (__group) z „+” — dropdown akcji dodania (slot add: elementy <li> menu, np. „Dodaj gabinet” otwierający modal x-admiro::dialog; bez slotu go nie ma) — i przyciskiem komendy z błyskawicą w miejscu „/” (co 12 s drży jak piorun; bez animacji przy prefers-reduced-motion) i wybraną komendą, dalej pole i wyciszony przycisk Enter. Oba dropdowny są [data-ss-dropdown] (js/app-shell.js: otwarcie jednego zamyka drugi, klik obok zamyka). Inne akcje teczki niż dodanie (slot actions: np. formularz POST z przyciskiem .ss-command__option i prefiksem `sync:`) stoją w dropdownie błyskawicy pod komendami. Domyślna komenda `szukaj:` to formularz GET na action z parametrami params (ukryte pola), wyszukujący na żywo (js/live-search.js: podmienia tylko element wyników o id results); bez skryptu wysyła się zwykłą drogą. Z commandUrl przycisk komendy rozwija też komendy systemu (Enums\QuickCommand, np. `todo:`) — wybór przepina pole i Enter do formularza POST komend (atrybuty form, js/command-box.js: zapis w tle, odświeżona lista TODO). action: string, results: string (id elementu wyników), label: string (etykieta pola dla czytnika ekranu), name: string (parametr zapytania), value: string, placeholder: string, params: array<string, string>, commandUrl: ?string (adres zapisu komend strefy). --}}
@props(['action', 'results', 'label', 'name' => 'q', 'value' => '', 'placeholder' => '', 'params' => [], 'commandUrl' => null, 'add' => null, 'actions' => null])
@php
    $searchForm = $results . '-search-form';
    $commandForm = $results . '-command-form';
    $searchPrefix = text('layout.command_search_prefix');
    $hasAdd = $add !== null && $add->isNotEmpty();
    $hasActions = $actions !== null && $actions->isNotEmpty();
@endphp
<div class="ss-command ss-command--folder" data-ss-command>
    <span class="ss-command__group">
        @if ($hasAdd)
            <details class="ss-command__add" data-ss-dropdown>
                <summary class="ss-command__add-toggle" title="{{ text('layout.search_actions') }}">
                    <x-admiro::icon name="plus" class="ss-command__add-icon" />
                    <span class="ss-sr-only">{{ text('layout.search_actions') }}</span>
                </summary>
                <div class="ss-command__add-panel">
                    <ul class="ss-account-menu__list">
                        {{ $add }}
                    </ul>
                </div>
            </details>
        @endif
        @if ($commandUrl === null && ! $hasActions)
            <x-admiro::icon name="zap" class="ss-command__zap" />
        @else
            <details class="ss-command__picker" data-ss-dropdown>
                <summary class="ss-command__toggle" title="{{ text('layout.commands') }}">
                    <x-admiro::icon name="zap" class="ss-command__zap" />
                    <span class="ss-command__current" data-ss-command-current>{{ $searchPrefix }}</span>
                    <span class="ss-sr-only">{{ text('layout.commands') }}</span>
                </summary>
                <div class="ss-command__list" role="group" aria-label="{{ text('layout.commands') }}">
                    <p class="ss-command__title">{{ text('layout.commands') }}</p>
                    <label class="ss-command__option">
                        <input class="ss-command__radio" type="radio" name="command" value="search" form="{{ $commandForm }}" checked data-ss-command-option data-prefix="{{ $searchPrefix }}" data-placeholder="{{ $placeholder }}" data-form="{{ $searchForm }}" data-name="{{ $name }}">
                        <x-admiro::icon name="search" class="ss-account-menu__icon" />
                        <span class="ss-command__prefix">{{ $searchPrefix }}</span>
                        <span class="ss-command__label">{{ $label }}</span>
                    </label>
                    @if ($commandUrl !== null)
                        @foreach (\Simplysmart\Admiro\Enums\QuickCommand::cases() as $command)
                            <label class="ss-command__option">
                                <input class="ss-command__radio" type="radio" name="command" value="{{ $command->value }}" form="{{ $commandForm }}" data-ss-command-option data-prefix="{{ $command->prefix() }}" data-placeholder="{{ $command->placeholder() }}" data-form="{{ $commandForm }}" data-name="text">
                                <x-admiro::icon :name="$command->icon()" class="ss-account-menu__icon" />
                                <span class="ss-command__prefix">{{ $command->prefix() }}</span>
                                <span class="ss-command__label">{{ $command->label() }}</span>
                            </label>
                        @endforeach
                    @endif
                    @if ($hasActions)
                        <p class="ss-command__title">{{ text('layout.command_actions') }}</p>
                        {{ $actions }}
                    @endif
                </div>
            </details>
        @endif
    </span>
    <label class="ss-sr-only" for="{{ $results }}-search">{{ $label }}</label>
    <input class="ss-command__input" id="{{ $results }}-search" name="{{ $name }}" type="search" form="{{ $searchForm }}" value="{{ $value }}" maxlength="255" autocomplete="off" placeholder="{{ $placeholder }}" data-ss-command-input>
    <button class="ss-search__send ss-command__send" type="submit" form="{{ $searchForm }}" data-ss-command-send>
        <x-admiro::icon name="corner-down-left" class="ss-search__send-icon ss-command__send-icon" />
        <span class="ss-command__loader" aria-hidden="true"></span>
        <span class="ss-sr-only">{{ text('layout.command_submit') }}</span>
    </button>
    <span class="ss-sr-only" role="status" data-ss-command-status></span>
</div>
<form id="{{ $searchForm }}" method="GET" action="{{ $action }}" role="search" hidden data-ss-live-search="{{ $results }}">
    @foreach ($params as $param => $paramValue)
        <input type="hidden" name="{{ $param }}" value="{{ $paramValue }}">
    @endforeach
</form>
@if ($commandUrl !== null)
    <form id="{{ $commandForm }}" method="POST" action="{{ $commandUrl }}" hidden data-ss-command-form>
        @csrf
    </form>
@endif

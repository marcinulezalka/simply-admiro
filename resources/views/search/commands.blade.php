{{-- Pole komend systemu w dolnej sekcji menu błyskawicy (admiro::search.folder z $commandUrl): subtelne pole na jasnym tle z delikatną szarą obwódką, w nim po lewej przycisk „/” z wybraną komendą (domyślnie Enums\QuickCommand::default() — `todo:`), który rozwija listę komend dostępnych w systemie (radio), pole na treść komendy (przy `todo:` — nazwa zadania) i wyciszony przycisk Enter wewnątrz pola. Menu leży w formularzu wyszukiwarki, więc pola należą do osobnego formularza POST #ss-command-form (atrybut form, x-admiro::app.search); wybór komendy podmienia prefiks i podpowiedź pola, a skrypt wysyła komendę w tle (js/command-box.js): na czas zapisu strzałka Entera zmienia się w loader (aria-busy), po zapisie pole jest puste i gotowe na kolejne zadanie, lista TODO się odświeża, a komunikat trafia do czytnika ekranu (role="status"). $commandUrl: string. --}}
@php($default = \Simplysmart\Admiro\Enums\QuickCommand::default())
<div class="ss-command" data-ss-command>
    <details class="ss-command__picker">
        <summary class="ss-command__toggle" title="{{ text('layout.commands') }}">
            <span class="ss-command__slash" aria-hidden="true">/</span>
            <span class="ss-command__current" data-ss-command-current>{{ $default->prefix() }}</span>
            <span class="ss-sr-only">{{ text('layout.commands') }}</span>
        </summary>
        <div class="ss-command__list" role="group" aria-label="{{ text('layout.commands') }}">
            <p class="ss-command__title">{{ text('layout.commands') }}</p>
            @foreach (\Simplysmart\Admiro\Enums\QuickCommand::cases() as $command)
                <label class="ss-command__option">
                    <input class="ss-command__radio" type="radio" name="command" value="{{ $command->value }}" form="ss-command-form" @checked($command === $default) data-ss-command-option data-prefix="{{ $command->prefix() }}" data-placeholder="{{ $command->placeholder() }}">
                    <x-admiro::icon :name="$command->icon()" class="ss-account-menu__icon" />
                    <span class="ss-command__prefix">{{ $command->prefix() }}</span>
                    <span class="ss-command__label">{{ $command->label() }}</span>
                </label>
            @endforeach
        </div>
    </details>
    <label class="ss-sr-only" for="ss-command-text">{{ text('layout.command_text') }}</label>
    <input class="ss-command__input" id="ss-command-text" name="text" type="text" form="ss-command-form" maxlength="255" required autocomplete="off" placeholder="{{ $default->placeholder() }}" data-ss-command-input>
    <button class="ss-search__send ss-command__send" type="submit" form="ss-command-form" data-ss-command-send>
        <x-admiro::icon name="corner-down-left" class="ss-search__send-icon ss-command__send-icon" />
        <span class="ss-command__loader" aria-hidden="true"></span>
        <span class="ss-sr-only">{{ text('layout.command_submit') }}</span>
    </button>
    <span class="ss-sr-only" role="status" data-ss-command-status></span>
</div>

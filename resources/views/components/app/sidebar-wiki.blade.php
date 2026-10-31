{{-- Prawy sidebar wiki i podpowiedzi layoutu stref z kolumnami (.ss-sidebar-wiki, x-admiro::app.columns; js/sidebar-wiki.js). Nagłówek jak w panelu powiadomień: ikona na kafelku, tytuł, pinezka i ×. Wiki i podpowiedzi strony dodaje się wyłącznie w ramach sidebar-wiki: slot `sidebar-wiki` w x-admiro::zone.layout (np. wpisy słownika x-admiro::wiki-tip), a nie w treści strony; bez slotu — tekst o braku podpowiedzi. Pod nimi — z tasks — zadania TODO konta, które je zapisało: pole „/todo:” zapisujące zadanie (POST na tasks->commandUrl, w tle — js/command-box.js) i lista x-admiro::app.tasks (zakres sidebar). Sidebar otwiera kafelek „Wiki” w narzędziach panelu kalendarza ([data-ss-sidebar-wiki-open], x-admiro::app.appearance-tools) — wysuwa się z prawej nad treść, a ×, Esc albo klik obok go chowają; pinezka (aria-pressed) przypina go jako stałą prawą kolumnę na wysokość okna z własnym przewijaniem (stan w przeglądarce, tylko na szerokim ekranie). Bez skryptu panel stoi przypięty, bez przycisków. Slot: zawartość sidebaru; tasks: ?View\Tasks\TaskPanel. --}}
@props(['tasks' => null])
<aside class="ss-sidebar-wiki" data-ss-sidebar-wiki aria-labelledby="ss-sidebar-wiki-title">
    <div class="ss-sidebar-wiki__panel" id="ss-sidebar-wiki-panel" data-ss-sidebar-wiki-panel>
        <div class="ss-sidebar-wiki__head">
            <x-admiro::icon name="book-open" class="ss-icon--tile" />
            <h2 class="ss-sidebar-wiki__title" id="ss-sidebar-wiki-title">{{ text('layout.sidebar_wiki') }}</h2>
            <button class="ss-icon-button ss-sidebar-wiki__pin" type="button" data-ss-sidebar-wiki-pin aria-pressed="false" title="{{ text('layout.sidebar_wiki_pin') }}" hidden><x-admiro::icon name="pin" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.sidebar_wiki_pin') }}</span></button>
            <button class="ss-icon-button ss-sidebar-wiki__close" type="button" data-ss-sidebar-wiki-close title="{{ text('layout.close') }}" hidden><x-admiro::icon name="close" class="ss-icon-button__icon" /><span class="ss-sr-only">{{ text('layout.close') }}</span></button>
        </div>
        <div class="ss-sidebar-wiki__body" tabindex="0">
            @if ($slot->isEmpty())
                <p class="ss-sidebar-wiki__empty">{{ text('layout.sidebar_wiki_empty') }}</p>
            @else
                {{ $slot }}
            @endif
        </div>
        @if ($tasks !== null)
            <div class="ss-sidebar-wiki__tasks">
                <form class="ss-sidebar-wiki__todo" id="ss-sidebar-todo-form" method="POST" action="{{ $tasks->commandUrl }}" data-ss-command-form>
                    @csrf
                    <input type="hidden" name="command" value="{{ \Simplysmart\Admiro\Enums\QuickCommand::Todo->value }}">
                    <div class="ss-command" data-ss-command>
                        <span class="ss-command__toggle ss-command__toggle--static" aria-hidden="true"><span class="ss-command__slash">/</span><span class="ss-command__current">{{ \Simplysmart\Admiro\Enums\QuickCommand::Todo->prefix() }}</span></span>
                        <label class="ss-sr-only" for="ss-sidebar-todo-text">{{ \Simplysmart\Admiro\Enums\QuickCommand::Todo->placeholder() }}</label>
                        <input class="ss-command__input" id="ss-sidebar-todo-text" name="text" type="text" maxlength="255" required autocomplete="off" placeholder="{{ \Simplysmart\Admiro\Enums\QuickCommand::Todo->placeholder() }}" data-ss-command-input>
                        <button class="ss-search__send ss-command__send" type="submit" data-ss-command-send>
                            <x-admiro::icon name="corner-down-left" class="ss-search__send-icon ss-command__send-icon" />
                            <span class="ss-command__loader" aria-hidden="true"></span>
                            <span class="ss-sr-only">{{ text('layout.command_submit') }}</span>
                        </button>
                        <span class="ss-sr-only" role="status" data-ss-command-status></span>
                    </div>
                </form>
                <x-admiro::app.tasks :tasks="$tasks" scope="sidebar" />
            </div>
        @endif
    </div>
</aside>

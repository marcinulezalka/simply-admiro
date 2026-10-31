{{-- Modal (natywny <dialog>, js/dialog.js): nagłówek z tytułem i × (form method="dialog"), pod nim treść (slot, np. formularz dodania wpisu teczki). Otwiera go każdy element [data-ss-dialog-open="<id>"] — najlepiej odnośnik do strony z otwartym modalem, który bez skryptu przeładowuje stronę; Esc, × i klik w tło zamykają. open = true (np. po błędzie walidacji formularza w modalu albo z parametru adresu) rysuje go otwartego: skrypt zamienia go wtedy w modal, a bez skryptu stoi w treści strony. id: string, title: string, open: bool. --}}
@props(['id', 'title', 'open' => false])
<dialog class="ss-dialog" id="{{ $id }}" aria-labelledby="{{ $id }}-title" @if ($open) open data-ss-dialog-autoshow @endif data-ss-dialog>
    <div class="ss-dialog__panel">
        <header class="ss-dialog__head">
            <h2 class="ss-dialog__title" id="{{ $id }}-title">{{ $title }}</h2>
            <form method="dialog">
                <button class="ss-dialog__close" type="submit" title="{{ text('layout.close') }}">
                    <x-admiro::icon name="close" class="ss-dialog__close-icon" />
                    <span class="ss-sr-only">{{ text('layout.close') }}</span>
                </button>
            </form>
        </header>
        <div class="ss-dialog__body">
            {{ $slot }}
        </div>
    </div>
</dialog>

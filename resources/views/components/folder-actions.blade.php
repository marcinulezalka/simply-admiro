{{-- Szybkie akcje teczek (Core .ss-folders): sama ikona błyskawicy obok zakładek teczek jako dropdown (<details data-ss-dropdown>) z „Zapisz” i „Anuluj”. „Zapisz” wysyła formularz wybranej teczki oznaczony form[data-ss-folder-form] (js/folder-actions.js; bez skryptu ukryty), „Anuluj” to odnośnik cancelUrl — powrót bez zapisu zmian (np. ta sama strona bez otwartych formularzy). Stawia się w .ss-folders zaraz za listą zakładek. cancelUrl: string. --}}
@props(['cancelUrl'])
<details class="ss-folder-actions" data-ss-dropdown data-ss-folder-actions>
    <summary class="ss-folder-actions__toggle" title="{{ text('layout.folder_actions') }}">
        <x-admiro::icon name="zap" class="ss-folder-actions__icon" />
        <span class="ss-sr-only">{{ text('layout.folder_actions') }}</span>
    </summary>
    <div class="ss-folder-actions__panel">
        <ul class="ss-account-menu__list">
            <li><button class="ss-account-menu__link" type="button" data-ss-folder-save hidden><x-admiro::icon name="check" class="ss-account-menu__icon" />{{ text_action('save') }}</button></li>
            <li><a class="ss-account-menu__link" href="{{ $cancelUrl }}"><x-admiro::icon name="close" class="ss-account-menu__icon" />{{ text_action('cancel') }}</a></li>
        </ul>
    </div>
</details>

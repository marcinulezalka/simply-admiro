{{-- Karta w wyglądzie teczki (.ss-folders/.ss-folder): nagłówek z ikoną na wypustce-zakładce (.ss-folder__tab--title, bez przełączania), treść w ciele teczki i opcjonalna stopka (slot footer). Z editable karta formularza ma dwa stany (js/edit-card.js, [data-ss-edit-card]): podgląd — pola tylko do odczytu (fieldset[data-ss-edit-fields] wyłączone, wygląd tekstu) i „Edytuj” — oraz edycja z „Zapisz” (wysyła formularz, w którym leży karta; przed wysłaniem skrypt włącza pola wszystkich kart, więc zapis obejmuje cały rekord jednym wpisem historii) i „Anuluj” (przywraca wartości karty i wraca do podglądu). Bez skryptu karta zostaje zwykłym formularzem z „Zapisz”; editing otwiera ją od razu w edycji (np. po błędzie walidacji jej pól). id, title, icon: wymagane; editable, editing: bool. Pola karty w slocie, w fieldset[data-ss-edit-fields]. --}}
@props(['id', 'title', 'icon', 'editable' => false, 'editing' => false])
<section {{ $attributes->class(['ss-folders', 'ss-folder-card'])->merge(['aria-labelledby' => $id . '-title', 'data-ss-edit-card' => $editable, 'data-state' => $editable ? 'edit' : null, 'data-ss-edit-open' => $editable && $editing]) }}>
    <div class="ss-folders__tabs">
        <h2 class="ss-folder__tab ss-folder__tab--title" id="{{ $id }}-title"><x-admiro::icon :name="$icon" class="ss-folder__tab-icon" />{{ $title }}</h2>
    </div>
    <div class="ss-folder" id="{{ $id }}">
        <div class="ss-folder__back">
            <div class="ss-folder__body">
                {{ $slot }}
                @if ($editable)
                    <div class="ss-form-actions">
                        <button class="ss-button" type="button" hidden data-ss-edit-start><x-admiro::icon name="pencil" class="ss-button__icon" />{{ text_action('edit') }}</button>
                        <button class="ss-button ss-button--primary" type="submit" data-ss-edit-save><x-admiro::icon name="check" class="ss-button__icon" />{{ text_action('save') }}</button>
                        <button class="ss-button" type="reset" hidden data-ss-edit-cancel><x-admiro::icon name="close" class="ss-button__icon" />{{ text_action('cancel') }}</button>
                    </div>
                @endif
            </div>
            @isset($footer)
                <footer class="ss-folder__footer">{{ $footer }}</footer>
            @endisset
        </div>
    </div>
</section>

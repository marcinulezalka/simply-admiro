{{-- Karta w wyglądzie teczki (.ss-folders/.ss-folder): nagłówek z ikoną na wypustce-zakładce (.ss-folder__tab--title, bez przełączania), treść w ciele teczki i opcjonalna stopka (slot footer). Z extraTitle i slotem extra obok nagłówka stoi druga zakładka (np. „Pola dodatkowe”, x-admiro::attributes-editor): wypustki przełączają dwie części ciała teczki (js/folder-card.js, [data-ss-card-tabs]; bez skryptu obie widoczne), a przyciski karty zostają pod nimi. Z editable karta formularza ma dwa stany (js/edit-card.js, [data-ss-edit-card]): podgląd — widać elementy .ss-folder-card__preview (dane w formie prezentacyjnej) i „Edytuj” — oraz edycja — widać .ss-folder-card__form (pola w fieldset[data-ss-edit-fields]) z „Zapisz” (wysyła formularz, w którym leży karta; przed wysłaniem skrypt włącza pola wszystkich kart, więc zapis obejmuje cały rekord jednym wpisem historii) i „Anuluj” (przywraca wartości karty i wraca do podglądu). Bez skryptu karta zostaje zwykłym formularzem z „Zapisz”; editing otwiera ją od razu w edycji (np. po błędzie walidacji jej pól). Karta bez editable pokazuje to, co dostała (sam podgląd albo sam formularz). id, title, icon: wymagane; extraTitle, extraIcon: ?string; editable, editing: bool. --}}
@props(['id', 'title', 'icon', 'extraTitle' => null, 'extraIcon' => 'list', 'editable' => false, 'editing' => false])
@php($tabbed = $extraTitle !== null && isset($extra))
<section {{ $attributes->class(['ss-folders', 'ss-folder-card'])->merge(['aria-labelledby' => $id . '-title', 'data-ss-edit-card' => $editable, 'data-state' => $editable ? 'edit' : null, 'data-ss-edit-open' => $editable && $editing, 'data-ss-card-tabs' => $tabbed]) }}>
    @if ($tabbed)
        <div class="ss-folders__tabs" role="tablist" aria-label="{{ $title }}">
            <button class="ss-folder__tab" type="button" role="tab" id="{{ $id }}-title" aria-controls="{{ $id }}-main" aria-selected="true" data-ss-card-tab><x-admiro::icon :name="$icon" class="ss-folder__tab-icon" />{{ $title }}</button>
            <button class="ss-folder__tab" type="button" role="tab" id="{{ $id }}-extra-tab" aria-controls="{{ $id }}-extra" aria-selected="false" data-ss-card-tab><x-admiro::icon :name="$extraIcon" class="ss-folder__tab-icon" />{{ $extraTitle }}</button>
        </div>
    @else
        <div class="ss-folders__tabs">
            <h2 class="ss-folder__tab ss-folder__tab--title" id="{{ $id }}-title"><x-admiro::icon :name="$icon" class="ss-folder__tab-icon" />{{ $title }}</h2>
        </div>
    @endif
    <div class="ss-folder" id="{{ $id }}">
        <div class="ss-folder__back">
            <div class="ss-folder__body">
                @if ($tabbed)
                    <div class="ss-folder-card__pane" id="{{ $id }}-main" role="tabpanel" aria-labelledby="{{ $id }}-title" data-ss-card-pane>{{ $slot }}</div>
                    <div class="ss-folder-card__pane" id="{{ $id }}-extra" role="tabpanel" aria-labelledby="{{ $id }}-extra-tab" data-ss-card-pane>{{ $extra }}</div>
                @else
                    {{ $slot }}
                @endif
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

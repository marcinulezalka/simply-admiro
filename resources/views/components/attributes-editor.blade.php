{{-- Pola dodatkowe sekcji rekordu (Attributes\CustomAttributes, `json_attributes.custom.<sekcja>`). Podgląd (.ss-folder-card__preview: lista „grupa · nazwa — wartość”, bez pól — „Brak pól dodatkowych”) i edytor (.ss-folder-card__form; karta x-admiro::folder-card editable pokazuje jeden albo drugi): „Dodaj pole” dopisuje pod listą wiersz grupa, nazwa i wartość, każdy wiersz ma uchwyt do przeciągania (kolejność) i „Usuń” (js/attributes-editor.js, [data-ss-attributes]; nowy wiersz z <template>, nazwy pól `custom_attributes[<sekcja>][i][…]` numerowane po każdej zmianie). Bez pól lista zawsze ma pierwszy pusty wiersz (także ze skryptem); wiersz bez nazwy nie jest zapisywany. Ukryte puste pole sekcji przed wierszami sprawia, że formularz zawsze ją niesie (także pustą — usunięcie wszystkich pól), a wyłączony edytor nie niesie nic (bez zmian). Bez znacznika <form>. items: list<Attributes\CustomAttribute>; section: string (klucz sekcji, CustomAttributes::SECTION_PATTERN); readonly: bool (sam podgląd, bez edytora); preview: bool (podgląd obok edytora, domyślnie tak). --}}
@props(['items' => [], 'section' => \Simplysmart\Admiro\Attributes\CustomAttributes::DEFAULT_SECTION, 'readonly' => false, 'preview' => true])
@php
    $field = \Simplysmart\Admiro\Attributes\CustomAttributes::FIELD . '[' . $section . ']';
    $idPrefix = 'custom-attributes-' . $section;
    $nameMax = \Simplysmart\Admiro\Attributes\CustomAttributes::NAME_MAX;
    $valueMax = \Simplysmart\Admiro\Attributes\CustomAttributes::VALUE_MAX;
    $old = old(\Simplysmart\Admiro\Attributes\CustomAttributes::FIELD . '.' . $section);
    $rows = is_array($old) ? \Simplysmart\Admiro\Attributes\CustomAttributes::fromRows($old) : $items;
@endphp
@if ($preview || $readonly)
    <div class="ss-folder-card__preview">
        @if ($items === [])
            <p class="ss-muted">{{ text('layout.attributes.empty') }}</p>
        @else
            <dl class="ss-attributes__preview">
                @foreach ($items as $item)
                    <div class="ss-attributes__preview-row">
                        <dt>@if ($item->group !== null)<span class="ss-muted">{{ $item->group }} · </span>@endif{{ $item->name }}</dt>
                        <dd>{{ $item->value ?? '—' }}</dd>
                    </div>
                @endforeach
            </dl>
        @endif
    </div>
@endif
@unless ($readonly)
    <div class="ss-attributes ss-folder-card__form" data-ss-attributes data-ss-attributes-field="{{ $field }}" data-ss-attributes-id="{{ $idPrefix }}">
        <input type="hidden" name="{{ $field }}" value="">
        <ul class="ss-attributes__list" data-ss-attributes-list>
            @foreach ([...$rows, null] as $index => $attribute)
                @include('admiro::components.attributes-editor-row', ['index' => $index, 'attribute' => $attribute, 'blank' => $attribute === null])
            @endforeach
        </ul>
        <template data-ss-attributes-template>
            @include('admiro::components.attributes-editor-row', ['index' => '__INDEX__', 'attribute' => null, 'blank' => false])
        </template>
        <p class="ss-attributes__add">
            <button class="ss-button ss-button--sm" type="button" hidden data-ss-attributes-add><x-admiro::icon name="plus" class="ss-button__icon" />{{ text('layout.attributes.add') }}</button>
        </p>
    </div>
@endunless

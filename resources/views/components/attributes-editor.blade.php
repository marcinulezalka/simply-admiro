{{-- Edytor atrybutów rekordu (Attributes\CustomAttributes, `json_attributes.custom`): „Dodaj atrybut” dopisuje pod listą wiersz z polami grupa, nazwa i wartość, każdy wiersz ma uchwyt do przeciągania (kolejność atrybutów) i „Usuń” (js/attributes-editor.js, [data-ss-attributes]; nowy wiersz z <template>, nazwy pól `custom_attributes[i][…]` numerowane po każdej zmianie). Wiersz bez nazwy nie jest zapisywany; ukryte puste pole FIELD przed wierszami sprawia, że formularz zawsze niesie listę (także pustą — usunięcie wszystkich), a wyłączony edytor nie niesie nic (bez zmian). Bez skryptu lista ma jeden pusty wiersz na nowy atrybut, bez przeciągania. Bez znacznika <form> — pola wysyła formularz, w którym leży edytor (np. karta x-admiro::folder-card editable). items: list<Attributes\CustomAttribute>; disabled: bool (tylko do odczytu: bez pustego wiersza i „Dodaj atrybut”). --}}
@props(['items' => [], 'disabled' => false])
@php
    $field = \Simplysmart\Admiro\Attributes\CustomAttributes::FIELD;
    $nameMax = \Simplysmart\Admiro\Attributes\CustomAttributes::NAME_MAX;
    $valueMax = \Simplysmart\Admiro\Attributes\CustomAttributes::VALUE_MAX;
    $old = old($field);
    $rows = is_array($old) ? \Simplysmart\Admiro\Attributes\CustomAttributes::fromRows($old) : $items;
@endphp
<div class="ss-attributes" data-ss-attributes data-ss-attributes-field="{{ $field }}">
    <input type="hidden" name="{{ $field }}" value="">
    <ul class="ss-attributes__list" data-ss-attributes-list>
        @foreach ($disabled ? $rows : [...$rows, null] as $index => $attribute)
            @include('admiro::components.attributes-editor-row', ['index' => $index, 'attribute' => $attribute, 'blank' => $attribute === null])
        @endforeach
    </ul>
    @unless ($disabled)
        <template data-ss-attributes-template>
            @include('admiro::components.attributes-editor-row', ['index' => '__INDEX__', 'attribute' => null, 'blank' => false])
        </template>
        <p class="ss-attributes__add">
            <button class="ss-button ss-button--sm" type="button" hidden data-ss-attributes-add><x-admiro::icon name="plus" class="ss-button__icon" />{{ text('layout.attributes.add') }}</button>
        </p>
    @endunless
</div>

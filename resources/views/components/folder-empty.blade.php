{{-- Pusty stan teczki (Core .ss-folder__body) zamiast wiersza „Brak.”: pośrodku duża ikona na miękkim kole (domyślnie folder-open), pod nią tytuł i tekst, a niżej akcja (slot, np. odnośnik otwierający modal dodania x-admiro::dialog); bez slotu sam opis. Jako element listy (`as="li"`) stoi w liście wierszy, którą podmienia wyszukiwanie na żywo. icon: string, title: string, text: string, as: string (div albo li). --}}
@props(['title', 'text', 'icon' => 'folder-open', 'as' => 'div'])
<{{ $as }} class="ss-folder-empty">
    <span class="ss-folder-empty__art"><x-admiro::icon :name="$icon" class="ss-folder-empty__icon" /></span>
    <span class="ss-folder-empty__title">{{ $title }}</span>
    <span class="ss-folder-empty__text">{{ $text }}</span>
    @if ($slot->isNotEmpty())
        <span class="ss-folder-empty__action">{{ $slot }}</span>
    @endif
</{{ $as }}>

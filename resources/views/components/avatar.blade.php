{{-- Awatar osoby albo organizacji: zdjęcie (image) albo inicjały z imienia i nazwiska (View\Initials). Dekoracyjny — nazwę podaje tekst obok albo atrybut label (np. sam awatar w zwiniętym menu). size: xs, sm, md, lg; color: ?Enums\AvatarColor (kolor konta, avatarColor()); accent: tło akcentu zamiast koloru. --}}
@props(['name', 'image' => null, 'size' => 'md', 'color' => null, 'accent' => false, 'label' => false])
<span {{ $attributes->class(['ss-avatar', 'ss-avatar--' . $size, 'ss-avatar--' . $color?->value => $color !== null && ! $accent, 'ss-avatar--accent' => $accent]) }} @if ($label) role="img" aria-label="{{ $name }}" @else aria-hidden="true" @endif>
    @if ($image !== null)
        <img class="ss-avatar__image" src="{{ $image }}" alt="">
    @else
        {{ \Simplysmart\Mainboard\View\Initials::of($name) }}
    @endif
</span>

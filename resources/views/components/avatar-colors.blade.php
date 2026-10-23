{{-- Wybór koloru awatara (pole `avatar_color`): pigułki z podglądem awatara w każdym kolorze palety (Enums\AvatarColor) i nazwą koloru; bieżący zaznaczony. name: imię i nazwisko do inicjałów; current: ?AvatarColor; field: nazwa pola. --}}
@props(['name', 'current' => null, 'field' => 'avatar_color'])
<fieldset class="ss-field ss-fieldset">
    <legend class="ss-field__label">{{ text('account.avatar_color') }}</legend>
    <div class="ss-choices">
        @foreach (\Simplysmart\Mainboard\Enums\AvatarColor::cases() as $color)
            <label class="ss-choice ss-choice--avatar">
                <input type="radio" name="{{ $field }}" value="{{ $color->value }}" @checked($color === $current)>
                <span><x-mainboard::avatar :name="$name" :color="$color" size="xs" />{{ $color->label() }}</span>
            </label>
        @endforeach
    </div>
</fieldset>

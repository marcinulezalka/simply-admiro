{{-- Wybór ikony organizacji (Models\Organization::ICONS, zapis w `json_attributes.icon`, Organization::icon()): kwadratowe pigułki .ss-choice--icon z samą ikoną, nazwa ikony dla czytników ekranu i w podpowiedzi; zaznaczona — current (domyślnie pierwsza z listy). field: nazwa pola (domyślnie `organization_icon`), current: ?string, label: ?string (legenda; domyślnie „Ikona”). --}}
@props(['field' => 'organization_icon', 'current' => null, 'label' => null])
@php($selected = old($field, $current ?? \Simplysmart\Admiro\Models\Organization::ICONS[0]))
<fieldset class="ss-field ss-fieldset">
    <legend class="ss-field__label">{{ $label ?? text('layout.organization_icons.label') }}</legend>
    <div class="ss-choices">
        @foreach (\Simplysmart\Admiro\Models\Organization::ICONS as $icon)
            @php($name = text('layout.organization_icons.' . $icon))
            <label class="ss-choice ss-choice--icon" title="{{ $name }}">
                <input type="radio" name="{{ $field }}" value="{{ $icon }}" @checked($icon === $selected)>
                <span><x-admiro::icon :name="$icon" class="ss-choice__icon" /><span class="ss-sr-only">{{ $name }}</span></span>
            </label>
        @endforeach
    </div>
    @error($field) <span class="ss-field__error" role="alert">{{ $message }}</span> @enderror
</fieldset>

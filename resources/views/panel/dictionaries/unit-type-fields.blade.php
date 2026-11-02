{{-- Pola typu jednostki organizacyjnej (nazwa, prefiks numeru, ikona, struktura wewnętrzna, domyślnie w publicznym grafiku) dla dodania i edycji w panel/dictionaries/unit-types. $prefix: string (przedrostek identyfikatorów pól), $type: ?OrganizationUnitType (null — nowy), $column: Closure(string): string (etykieta kolumny), $icons: list<string>. --}}
@php($selectedIcon = $type?->iconName() ?? $icons[0])
<x-admiro::input :id="$prefix . '-name'" name="name" :label="$column('name')" :value="$type?->name" required maxlength="255" />
<div class="ss-form-row">
    <x-admiro::input :id="$prefix . '-prefix'" name="number_prefix" field-class="ss-form-row__grow" :label="$column('number_prefix')" :hint="text('admiro::admin.unit_types.prefix_hint')" :value="$type?->number_prefix" :maxlength="\Simplysmart\Admiro\Models\OrganizationUnitType::PREFIX_MAX_LENGTH" />
    <div class="ss-field ss-form-row__grow">
        <label class="ss-field__label" for="{{ $prefix }}-icon">{{ $column('icon') }}</label>
        <select class="ss-input" id="{{ $prefix }}-icon" name="icon">
            @foreach ($icons as $icon)
                <option value="{{ $icon }}" @selected($selectedIcon === $icon)>{{ $icon }}</option>
            @endforeach
        </select>
    </div>
</div>
<x-admiro::switch :id="$prefix . '-internal'" name="is_internal" value="1" :label="$column('is_internal')" :hint="text('admiro::admin.unit_types.internal_hint')" :checked="(bool) $type?->is_internal" />
<x-admiro::switch :id="$prefix . '-public'" name="is_public" value="1" :label="$column('is_public')" :hint="text('admiro::admin.unit_types.public_hint')" :checked="$type === null || $type->is_public" />

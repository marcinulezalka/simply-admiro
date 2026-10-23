{{-- Nowa organizacja albo zmiana organizacji: nazwa i organizacja nadrzędna na karcie formularza. $organization: ?Organization (null = nowa), $parents: Collection<Organization> (aktywne, bez edytowanej). --}}
<x-mainboard::zone.layout :title="$organization === null ? text('admiro::admin.organization_create') : text_action('edit') . ': ' . $organization->name" :back="route('admiro.organizations.index')">
    @error('organization') <p class="ss-notice" role="alert"><x-mainboard::icon name="alert-triangle" /> {{ $message }}</p> @enderror

    <form class="ss-card" method="POST" action="{{ $organization === null ? route('admiro.organizations.store') : route('admiro.organizations.update', ['organization' => $organization->token]) }}">
        @csrf
        @if ($organization !== null)
            @method('PUT')
        @endif
        <div class="ss-form">
            <x-mainboard::input id="name" name="name" :label="text('admiro::admin.organization')" icon="building" :value="old('name', $organization?->name)" :error="$errors->first('name')" required maxlength="255" />
            <div class="ss-field">
                <label class="ss-field__label" for="parent">{{ text('admiro::admin.organization_parent') }}</label>
                <select class="ss-input" id="parent" name="parent">
                    <option value="">—</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->token }}" @selected(old('parent', $organization?->parent_id === $parent->id ? $parent->token : null) === $parent->token)>{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="ss-form-actions">
                <button class="ss-button ss-button--primary" type="submit"><x-mainboard::icon name="check" class="ss-button__icon" />{{ text_action('save') }}</button>
            </div>
        </div>
    </form>
</x-mainboard::zone.layout>

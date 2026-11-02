{{-- Nowa organizacja albo zmiana organizacji: karty danych organizacji (z organizacją nadrzędną), kontaktowych i adresowych (x-admiro::organization.fields), a przy edycji karta marki x-admiro::organization.brand (logo, sygnet, ikona strony: podgląd, wgranie przeciągnięciem albo wyborem, usunięcie; logo trafia do e-maili organizacji). $organization: ?Organization (null = nowa), $parents: Collection<Organization> (aktywne, bez edytowanej). --}}
<x-admiro::zone.layout :title="$organization === null ? text('admiro::admin.organization_create') : text_action('edit') . ': ' . $organization->name" :back="route('admiro.organizations.index')">
    @error('organization') <p class="ss-notice" role="alert"><x-admiro::icon name="alert-triangle" /> {{ $message }}</p> @enderror

    <form class="ss-form" method="POST" action="{{ $organization === null ? route('admiro.organizations.store') : route('admiro.organizations.update', ['organization' => $organization->token]) }}">
        @csrf
        @if ($organization !== null)
            @method('PUT')
        @endif
        <x-admiro::organization.fields :organization="$organization">
            <div class="ss-field">
                <label class="ss-field__label" for="parent">{{ text('admiro::admin.organization_parent') }}</label>
                <select class="ss-input" id="parent" name="parent">
                    <option value="">—</option>
                    @foreach ($parents as $parent)
                        <option value="{{ $parent->token }}" @selected(old('parent', $organization?->parent_id === $parent->id ? $parent->token : null) === $parent->token)>{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
        </x-admiro::organization.fields>
        <div class="ss-form-actions">
            <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="check" class="ss-button__icon" />{{ text_action('save') }}</button>
        </div>
    </form>

    @if ($organization !== null)
        <x-admiro::organization.brand :organization="$organization" :url="static fn (\Simplysmart\Admiro\Enums\BrandAsset $asset, string $action): string => route('admiro.organizations.brand.' . $action, ['organization' => $organization->token, 'asset' => $asset->value])" :hint="text('admiro::admin.organization_brand_hint')" editable />
    @endif
</x-admiro::zone.layout>

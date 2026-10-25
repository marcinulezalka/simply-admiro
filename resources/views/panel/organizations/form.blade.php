{{-- Nowa organizacja albo zmiana organizacji: karty danych organizacji (z organizacją nadrzędną), kontaktowych i adresowych (x-admiro::organization.fields), a przy edycji karta marki (logo, sygnet, ikona strony: podgląd, wgranie, usunięcie; logo trafia do e-maili organizacji). $organization: ?Organization (null = nowa), $parents: Collection<Organization> (aktywne, bez edytowanej), $brandAssets: list<array{asset: BrandAsset, set: bool}> (tylko edycja). --}}
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
        <section class="ss-card" aria-labelledby="brand-title">
            <h2 class="ss-card__title" id="brand-title">{{ text('admiro::admin.organization_brand') }}</h2>
            <p class="ss-muted">{{ text('admiro::admin.organization_brand_hint') }}</p>
            @error('file') <p class="ss-notice" role="alert"><x-admiro::icon name="alert-triangle" /> {{ $message }}</p> @enderror
            <div class="ss-form">
                @foreach ($brandAssets as $item)
                    @php($asset = $item['asset'])
                    @php($routeParameters = ['organization' => $organization->token, 'asset' => $asset->value])
                    <div class="ss-field">
                        <span class="ss-field__label">{{ text('admiro::admin.organization_brand_' . $asset->value) }}</span>
                        @if ($item['set'])
                            <img src="{{ route('admiro.organizations.brand.show', $routeParameters) }}" alt="{{ text('admiro::admin.organization_brand_' . $asset->value) }}" class="ss-brand-preview">
                        @endif
                        <form method="POST" action="{{ route('admiro.organizations.brand.store', $routeParameters) }}" enctype="multipart/form-data">
                            @csrf
                            <x-admiro::input :id="'brand-' . $asset->value" name="file" type="file" :label="text('admiro::admin.organization_brand_file')" icon="upload" :hint="text('admiro::admin.organization_brand_formats', ['formats' => strtoupper(implode(', ', $asset->extensions())), 'size' => $asset->maxKilobytes()])" accept="{{ '.' . implode(',.', $asset->extensions()) }}" required />
                            <button class="ss-button" type="submit"><x-admiro::icon name="upload" class="ss-button__icon" />{{ text_action('save') }}</button>
                        </form>
                        @if ($item['set'])
                            <form method="POST" action="{{ route('admiro.organizations.brand.destroy', $routeParameters) }}">
                                @csrf
                                @method('DELETE')
                                <button class="ss-button" type="submit"><x-admiro::icon name="trash" class="ss-button__icon" />{{ text_action('delete') }}</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</x-admiro::zone.layout>

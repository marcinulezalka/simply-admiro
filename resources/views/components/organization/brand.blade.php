{{-- Karta „Marka” organizacji (x-admiro::folder-card) z plikami Enums\BrandAsset — logo, sygnet, ikona strony — z Organizations\OrganizationBranding: przy każdym podgląd wgranego pliku (.ss-brand-preview), a z editable strefa upuszczania (.ss-drop-zone, [data-ss-file-drop-zone] podświetlana przy przeciąganiu przez js/file-drop.js): pole pliku przykrywa strefę, więc przeglądarka przyjmuje upuszczony plik albo wybór kliknięciem, a formularz (multipart, form[data-ss-autosubmit], js/autosubmit.js) wysyła się od razu po wyborze; bez skryptu przycisk zapasowy „Zapisz”. Wgrany plik można usunąć. Adresy daje strefa: url(BrandAsset $asset, string $action) z akcją `show`, `store` albo `destroy`. Błąd pliku (pole `file`) jako komunikat nad strefami. organization: Organization, url: Closure, editable: bool, hint: ?string (opis pod tytułem). --}}
@props(['organization', 'url', 'editable' => false, 'hint' => null])
@php($branding = app(\Simplysmart\Admiro\Organizations\OrganizationBranding::class))
<x-admiro::folder-card id="organization-brand" icon="image" :title="text('layout.organization_brand.title')">
    @if ($hint !== null)
        <p class="ss-muted">{{ $hint }}</p>
    @endif
    @error('file') <p class="ss-notice" role="alert"><x-admiro::icon name="alert-triangle" /> {{ $message }}</p> @enderror
    <div class="ss-brand-assets">
        @foreach (\Simplysmart\Admiro\Enums\BrandAsset::cases() as $asset)
            @php($label = text('layout.organization_brand.' . $asset->value))
            @php($isSet = $branding->has($organization, $asset))
            <div class="ss-brand-asset">
                <span class="ss-field__label">{{ $label }}</span>
                @if ($isSet)
                    <img src="{{ $url($asset, 'show') }}" alt="{{ $label }}" class="ss-brand-preview">
                @endif
                @if ($editable)
                    <form method="POST" action="{{ $url($asset, 'store') }}" enctype="multipart/form-data" data-ss-autosubmit>
                        @csrf
                        <label class="ss-drop-zone" for="brand-{{ $asset->value }}" data-ss-file-drop-zone>
                            <x-admiro::icon name="upload" class="ss-drop-zone__icon" />
                            <span class="ss-drop-zone__text">{{ text('layout.organization_brand.drop') }}</span>
                            <span class="ss-drop-zone__hint">{{ text('layout.organization_brand.formats', ['formats' => strtoupper(implode(', ', $asset->extensions())), 'size' => $asset->maxKilobytes()]) }}</span>
                            <input class="ss-drop-zone__input" id="brand-{{ $asset->value }}" name="file" type="file" accept="{{ '.' . implode(',.', $asset->extensions()) }}" required>
                        </label>
                        <button class="ss-button ss-button--sm" type="submit" data-ss-autosubmit-fallback><x-admiro::icon name="upload" class="ss-button__icon" />{{ text_action('save') }}</button>
                    </form>
                    @if ($isSet)
                        <form method="POST" action="{{ $url($asset, 'destroy') }}">
                            @csrf
                            @method('DELETE')
                            <button class="ss-button ss-button--sm" type="submit"><x-admiro::icon name="trash" class="ss-button__icon" />{{ text_action('delete') }}</button>
                        </form>
                    @endif
                @elseif (! $isSet)
                    <p class="ss-muted">{{ text('layout.organization_form.empty') }}</p>
                @endif
            </div>
        @endforeach
    </div>
</x-admiro::folder-card>

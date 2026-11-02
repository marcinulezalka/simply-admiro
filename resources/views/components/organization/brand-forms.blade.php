{{-- Formularze karty „Marka” x-admiro::organization.brand wstawionej z detached w inny formularz (np. między karty x-admiro::organization.fields): puste formularze o identyfikatorach `brand-<asset>-store` (multipart, form[data-ss-autosubmit]) i `brand-<asset>-destroy` (przy wgranym pliku), do których pola i przyciski karty wskazują atrybutem form; umieszczane poza tamtym formularzem. organization: Organization, url: Closure — jak w x-admiro::organization.brand. --}}
@props(['organization', 'url'])
@php($branding = app(\Simplysmart\Admiro\Organizations\OrganizationBranding::class))
@foreach (\Simplysmart\Admiro\Enums\BrandAsset::cases() as $asset)
    <form id="brand-{{ $asset->value }}-store" method="POST" action="{{ $url($asset, 'store') }}" enctype="multipart/form-data" data-ss-autosubmit>
        @csrf
    </form>
    @if ($branding->has($organization, $asset))
        <form id="brand-{{ $asset->value }}-destroy" method="POST" action="{{ $url($asset, 'destroy') }}">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endforeach

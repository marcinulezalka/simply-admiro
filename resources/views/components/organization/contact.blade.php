{{-- Dane kontaktowe organizacji (na wizytówce x-admiro::organization.card albo na odwrocie karty obracanej), każda dana w osobnym panelu — prawie niewidocznym, a po najechaniu z pełnym tłem, obwódką i ikoną w kolorze akcentu: adres w dwóch liniach (ulica, pod nią kod, miasto i kraj), telefon stacjonarny i komórkowy, e-mail i e-mail do rozliczeń z ikoną paragonu (puste pomijane; bez żadnej danej nic się nie renderuje). organization: Models\Organization. --}}
@props(['organization'])
@php
    $model = \Simplysmart\Admiro\Models\Organization::class;
    $locality = trim(implode(' ', array_filter([$organization->postal_code, $organization->city])));
    $address = array_values(array_filter([$organization->street, implode(', ', array_filter([$locality, $organization->country?->translatedName()], filled(...)))], filled(...)));
    $details = array_filter([
        ['icon' => 'map-pin', 'value' => $address, 'column' => null],
        ['icon' => 'phone', 'value' => $organization->phone, 'column' => $model::PHONE],
        ['icon' => 'smartphone', 'value' => $organization->mobile_phone, 'column' => $model::MOBILE_PHONE],
        ['icon' => 'mail', 'value' => $organization->email, 'column' => $model::EMAIL],
        ['icon' => 'receipt', 'value' => $organization->billing_email, 'column' => $model::BILLING_EMAIL],
    ], static fn (array $detail): bool => filled($detail['value']));
@endphp
@if ($details !== [])
    <span {{ $attributes->class(['ss-business-card__details']) }}>
        @foreach ($details as $detail)
            <span class="ss-business-card__detail" @if ($detail['column'] !== null) title="{{ text_data($organization, $detail['column']) }}" @endif><x-admiro::icon :name="$detail['icon']" class="ss-business-card__detail-icon" />@if (is_array($detail['value']))<span class="ss-business-card__lines">@foreach ($detail['value'] as $line)<span class="ss-business-card__line">{{ $line }}</span>@endforeach</span>@else{{ $detail['value'] }}@endif</span>
        @endforeach
    </span>
@endif

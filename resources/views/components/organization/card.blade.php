{{-- Wizytówka organizacji (karta jak papierowa wizytówka): u góry podpis (kicker, np. „Placówka”) i w rogu strzałka, nazwa i pod nią numery rejestrowe (NIP · REGON · KRS), pod linią adres, telefon, e-mail i e-mail do rozliczeń z etykietą (puste pomijane); tło z motywem (poświata i koncentryczne łuki w kolorze akcentu, .ss-business-card; strefa może podmienić motyw warstwą ::before). Z href cała karta jest odnośnikiem (np. do ustawień organizacji). organization: Models\Organization; href: ?string; kicker: ?string. --}}
@props(['organization', 'href' => null, 'kicker' => null])
@php
    $address = implode(', ', array_filter([$organization->street, trim(implode(' ', array_filter([$organization->postal_code, $organization->city])))], filled(...)));
    $registry = array_values(array_filter(array_map(
        static fn (string $column): ?string => filled($organization->{$column}) ? text_data($organization, $column) . ' ' . $organization->{$column} : null,
        ['nip', 'regon', 'krs'],
    )));
    $details = array_filter([
        ['icon' => 'map-pin', 'value' => $address, 'label' => null],
        ['icon' => 'phone', 'value' => $organization->phone, 'label' => null],
        ['icon' => 'mail', 'value' => $organization->email, 'label' => null],
        ['icon' => 'invoice', 'value' => $organization->billing_email, 'label' => text_data($organization, 'billing_email')],
    ], static fn (array $detail): bool => filled($detail['value']));
    $tag = $href !== null ? 'a' : 'div';
@endphp
<{{ $tag }} {{ $attributes->class(['ss-business-card', 'ss-business-card--link' => $href !== null]) }} @if ($href !== null) href="{{ $href }}" @endif>
    <span class="ss-business-card__top">
        @if (filled($kicker))
            <span class="ss-business-card__kicker">{{ $kicker }}</span>
        @endif
        @if ($href !== null)
            <x-admiro::icon name="arrow-right" class="ss-business-card__arrow" />
        @endif
    </span>
    <span class="ss-business-card__identity">
        <span class="ss-business-card__name">{{ $organization->name }}</span>
        @if ($registry !== [])
            <span class="ss-business-card__registry">{{ implode(' · ', $registry) }}</span>
        @endif
    </span>
    @if ($details !== [])
        <span class="ss-business-card__details">
            @foreach ($details as $detail)
                <span class="ss-business-card__detail"><x-admiro::icon :name="$detail['icon']" class="ss-business-card__detail-icon" />{{ $detail['value'] }}@if ($detail['label'] !== null)<span class="ss-business-card__detail-label">{{ $detail['label'] }}</span>@endif</span>
            @endforeach
        </span>
    @endif
</{{ $tag }}>

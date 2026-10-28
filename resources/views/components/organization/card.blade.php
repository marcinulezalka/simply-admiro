{{-- Wizytówka organizacji (karta jak papierowa wizytówka): monogram z inicjałów, podpis nad nazwą (kicker, np. „Placówka”), nazwa i nazwa skrócona, pod linią adres, telefon, e-mail i NIP (puste pomijane), w rogu strzałka; tło z motywem (poświata i koncentryczne łuki w kolorze akcentu, .ss-business-card). Z href cała karta jest odnośnikiem (np. do ustawień organizacji). organization: Models\Organization; href: ?string; kicker: ?string. --}}
@props(['organization', 'href' => null, 'kicker' => null])
@php
    $address = implode(', ', array_filter([$organization->street, trim(implode(' ', array_filter([$organization->postal_code, $organization->city])))], filled(...)));
    $details = array_filter([
        'map-pin' => $address,
        'phone' => $organization->phone,
        'mail' => $organization->email,
        'id-card' => filled($organization->nip) ? text_data($organization, 'nip') . ' ' . $organization->nip : null,
    ], filled(...));
    $tag = $href !== null ? 'a' : 'div';
@endphp
<{{ $tag }} {{ $attributes->class(['ss-business-card', 'ss-business-card--link' => $href !== null]) }} @if ($href !== null) href="{{ $href }}" @endif>
    <span class="ss-business-card__top">
        <span class="ss-business-card__monogram" aria-hidden="true">{{ \Simplysmart\Admiro\View\Initials::of((string) ($organization->short_name ?: $organization->name)) }}</span>
        @if ($href !== null)
            <x-admiro::icon name="arrow-right" class="ss-business-card__arrow" />
        @endif
    </span>
    <span class="ss-business-card__identity">
        @if (filled($kicker))
            <span class="ss-business-card__kicker">{{ $kicker }}</span>
        @endif
        <span class="ss-business-card__name">{{ $organization->name }}</span>
        @if (filled($organization->short_name) && $organization->short_name !== $organization->name)
            <span class="ss-business-card__short">{{ $organization->short_name }}</span>
        @endif
    </span>
    @if ($details !== [])
        <span class="ss-business-card__details">
            @foreach ($details as $icon => $value)
                <span class="ss-business-card__detail"><x-admiro::icon :name="$icon" class="ss-business-card__detail-icon" />{{ $value }}</span>
            @endforeach
        </span>
    @endif
</{{ $tag }}>

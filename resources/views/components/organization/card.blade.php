{{-- Wizytówka organizacji (karta jak papierowa wizytówka): u góry podpis krojem mono (kicker, np. „Organizacja”) w jednym rzędzie z ikoną linku, pod nim nazwa skrócona (bez niej pełna) krojem nagłówków i numery rejestrowe krojem mono (NIP zawsze — pusty jako „—”, REGON i KRS, gdy są), dosunięte do dołu karty adres z krajem, telefon stacjonarny i komórkowy, e-mail i e-mail do rozliczeń z ikoną paragonu (puste pomijane), w rogu ikona linku (pod kursorem przesuwa się tylko ona, karta stoi); tło z motywem (poświata i koncentryczne łuki w kolorze akcentu, .ss-business-card; strefa może podmienić motyw warstwą ::before). Z href cała karta jest odnośnikiem (np. do ustawień organizacji). organization: Models\Organization; href: ?string; kicker: ?string. --}}
@props(['organization', 'href' => null, 'kicker' => null])
@php
    $model = \Simplysmart\Admiro\Models\Organization::class;
    $locality = trim(implode(' ', array_filter([$organization->postal_code, $organization->city])));
    $address = implode(', ', array_filter([$organization->street, $locality, $organization->country?->translatedName()], filled(...)));
    $registry = array_values(array_filter(array_map(
        static fn (string $column): ?string => filled($organization->{$column}) || $column === $model::NIP ? text_data($organization, $column) . ' ' . ($organization->{$column} ?: '—') : null,
        [$model::NIP, $model::REGON, $model::KRS],
    )));
    $details = array_filter([
        ['icon' => 'map-pin', 'value' => $address, 'column' => null],
        ['icon' => 'phone', 'value' => $organization->phone, 'column' => $model::PHONE],
        ['icon' => 'smartphone', 'value' => $organization->mobile_phone, 'column' => $model::MOBILE_PHONE],
        ['icon' => 'mail', 'value' => $organization->email, 'column' => $model::EMAIL],
        ['icon' => 'receipt', 'value' => $organization->billing_email, 'column' => $model::BILLING_EMAIL],
    ], static fn (array $detail): bool => filled($detail['value']));
    $tag = $href !== null ? 'a' : 'div';
@endphp
<{{ $tag }} {{ $attributes->class(['ss-business-card', 'ss-business-card--link' => $href !== null]) }} @if ($href !== null) href="{{ $href }}" @endif>
    <span class="ss-business-card__identity">
        @if (filled($kicker) || $href !== null)
            <span class="ss-business-card__top">
                @if (filled($kicker))
                    <span class="ss-business-card__kicker">{{ $kicker }}</span>
                @endif
                @if ($href !== null)
                    <x-admiro::icon name="link" class="ss-business-card__arrow" />
                @endif
            </span>
        @endif
        <span class="ss-business-card__name">{{ $organization->shortName() }}</span>
        <span class="ss-business-card__registry">{{ implode(' · ', $registry) }}</span>
    </span>
    @if ($details !== [])
        <span class="ss-business-card__details">
            @foreach ($details as $detail)
                <span class="ss-business-card__detail" @if ($detail['column'] !== null) title="{{ text_data($organization, $detail['column']) }}" @endif><x-admiro::icon :name="$detail['icon']" class="ss-business-card__detail-icon" />{{ $detail['value'] }}</span>
            @endforeach
        </span>
    @endif
</{{ $tag }}>

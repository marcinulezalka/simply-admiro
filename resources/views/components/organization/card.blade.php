{{-- Wizytówka organizacji (karta jak papierowa wizytówka): u góry podpis krojem mono (kicker, np. „Organizacja”) w jednym rzędzie z ikoną odnośnika (external-link; pod kursorem jej strzałka wysuwa się z ramki), pod nim nazwa skrócona (bez niej pełna) krojem nagłówków i numery rejestrowe krojem mono, każdy w osobnej linii (NIP zawsze — pusty jako „—”, REGON i KRS, gdy są), dosunięte do dołu karty dane kontaktowe (x-admiro::organization.contact; contact: false — bez nich, np. gdy są na odwrocie karty obracanej), w rogu ikona linku (pod kursorem przesuwa się tylko ona, karta stoi); tło z motywem (poświata i koncentryczne łuki w kolorze akcentu, .ss-business-card; strefa może podmienić motyw warstwą ::before). Z href cała karta jest odnośnikiem (np. do ustawień organizacji); icon: ikona w kafelku przed nazwą (np. building). organization: Models\Organization; href: ?string; kicker: ?string. --}}
@props(['organization', 'href' => null, 'kicker' => null, 'icon' => null, 'contact' => true])
@php
    $model = \Simplysmart\Admiro\Models\Organization::class;
    $registry = array_values(array_filter(array_map(
        static fn (string $column): ?string => filled($organization->{$column}) || $column === $model::NIP ? text_data($organization, $column) . ' ' . ($organization->{$column} ?: '—') : null,
        [$model::NIP, $model::REGON, $model::KRS],
    )));
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
                    <x-admiro::icon name="external-link" class="ss-business-card__arrow" />
                @endif
            </span>
        @endif
        @if (filled($icon))
            <span class="ss-business-card__heading"><x-admiro::icon :name="$icon" class="ss-business-card__icon" /><span class="ss-business-card__name">{{ $organization->shortName() }}</span></span>
        @else
            <span class="ss-business-card__name">{{ $organization->shortName() }}</span>
        @endif
        <span class="ss-business-card__registry">@foreach ($registry as $number)<span class="ss-business-card__number">{{ $number }}</span>@endforeach</span>
    </span>
    @if ($contact)
        <x-admiro::organization.contact :organization="$organization" />
    @endif
</{{ $tag }}>

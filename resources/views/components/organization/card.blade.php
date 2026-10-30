{{-- Wizytówka organizacji (karta jak papierowa wizytówka): u góry podpis krojem mono (kicker, np. „Organizacja”) w jednym rzędzie z ikoną odnośnika (external-link; pod kursorem jej strzałka wysuwa się z ramki), pod nim nazwa skrócona (bez niej pełna) krojem nagłówków i numery rejestrowe krojem mono, każdy w osobnej linii (NIP zawsze — pusty jako „—”, REGON i KRS, gdy są), dosunięte do dołu karty dane kontaktowe (x-admiro::organization.contact; contact: false — bez nich, np. gdy są na odwrocie karty obracanej), w rogu ikona linku (pod kursorem przesuwa się tylko ona, karta stoi); tło z motywem (poświata i koncentryczne łuki w kolorze akcentu, .ss-business-card; strefa może podmienić motyw warstwą ::before). Z href cała karta jest odnośnikiem (np. do ustawień organizacji); slot actions — odnośniki na dole karty (np. główna akcja „+”), a z href kartę obejmuje wtedy rozciągnięty odnośnik ikony strzałki (__link), bo odnośniki nie mogą się zagnieżdżać; icon: ikona w kafelku po lewej, obok podpisu i nazwy wyrównanych do niej (np. building). organization: Models\Organization; href: ?string; kicker: ?string. --}}
@props(['organization', 'href' => null, 'kicker' => null, 'icon' => null, 'contact' => true])
@php
    $model = \Simplysmart\Admiro\Models\Organization::class;
    $registry = array_values(array_filter(array_map(
        static fn (string $column): ?string => filled($organization->{$column}) || $column === $model::NIP ? text_data($organization, $column) . ' ' . ($organization->{$column} ?: '—') : null,
        [$model::NIP, $model::REGON, $model::KRS],
    )));
    $stretched = $href !== null && isset($actions);
    $tag = $href !== null && ! $stretched ? 'a' : 'div';
@endphp
<{{ $tag }} {{ $attributes->class(['ss-business-card', 'ss-business-card--link' => $href !== null]) }} @if ($tag === 'a') href="{{ $href }}" @endif>
    <span class="ss-business-card__identity">
        <span class="ss-business-card__heading">
            @if (filled($icon))
                <x-admiro::icon :name="$icon" class="ss-business-card__icon" />
            @endif
            <span class="ss-business-card__titles">
                @if (filled($kicker) || $href !== null)
                    <span class="ss-business-card__top">
                        @if (filled($kicker))
                            <span class="ss-business-card__kicker">{{ $kicker }}</span>
                        @endif
                        @if ($stretched)
                            <a class="ss-business-card__link" href="{{ $href }}"><x-admiro::icon name="external-link" class="ss-business-card__arrow" /><span class="ss-sr-only">{{ $organization->shortName() }}</span></a>
                        @elseif ($href !== null)
                            <x-admiro::icon name="external-link" class="ss-business-card__arrow" />
                        @endif
                    </span>
                @endif
                <span class="ss-business-card__name">{{ $organization->shortName() }}</span>
            </span>
        </span>
        <span class="ss-business-card__registry">@foreach ($registry as $number)<span class="ss-business-card__number">{{ $number }}</span>@endforeach</span>
    </span>
    @if ($contact)
        <x-admiro::organization.contact :organization="$organization" />
    @endif
    @isset($actions)
        <span class="ss-business-card__actions">{{ $actions }}</span>
    @endisset
</{{ $tag }}>

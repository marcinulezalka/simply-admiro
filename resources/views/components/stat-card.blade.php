{{-- Karta statystyki pulpitu (.ss-stat-card; docs/design-system.md §7 „Karty statystyk”): u góry ikona na kafelku w kolorze tonu i nazwa (z href — odnośnik ze strzałką), po prawej opcjonalne akcje (slot actions, np. okrągły „+”); pod nimi panel w karcie z delikatnym odcieniem tonu: duża liczba (cyfry tabelaryczne) z jednostką (unit, mniejsza, wyciszona) i pigułką trendu (trend, np. „+12%”, w kolorze tonu), pod nią opis (note), a dalej opcjonalnie miernik od niskiego do wysokiego (meter: wypełniona część w gradiencie tonu w ukośne paski z wartością, za kreską reszta z maksimum; role="meter") albo słupki (bars: wyciszone, bieżący w gradiencie tonu z poświatą; role="img") i dowolna treść (slot). Szerokość i wysokość wypełnienia daje View\StatLevel w `data-ss-level` (bez stylów inline). filled: panel wypełniony gradientem tonu z białym tekstem (tylko ton z kontrastem dla białego: accent, success, info, danger). label, value: string; icon: ?string; unit, trend, note: ?string; tone: accent|success|warning|danger|info (domyślnie accent); meter: ?array{value: int|float, max: int|float, low?: string, high?: string, label?: string, valueText?: string, maxText?: string}; bars: ?array{values: list<int|float>, labels?: list<string>, current?: int, label: string}; href: ?string; filled: bool. --}}
@props(['label', 'value', 'icon' => null, 'unit' => null, 'trend' => null, 'note' => null, 'tone' => 'accent', 'meter' => null, 'bars' => null, 'href' => null, 'filled' => false])
@php
    $tone = in_array($tone, ['accent', 'success', 'warning', 'danger', 'info'], true) ? $tone : 'accent';
    $barValues = $bars['values'] ?? [];
    $barMax = $barValues === [] ? 0 : max($barValues);
    $currentBar = $bars['current'] ?? array_key_last($barValues);
@endphp
<section {{ $attributes->class(['ss-stat-card', 'ss-stat-card--' . $tone, 'ss-stat-card--filled' => $filled]) }}>
    <header class="ss-stat-card__head">
        @if ($icon !== null)
            <span class="ss-stat-card__icon-tile" aria-hidden="true"><x-admiro::icon :name="$icon" class="ss-stat-card__icon" /></span>
        @endif
        <h3 class="ss-stat-card__label">
            @if ($href !== null)
                <a class="ss-stat-card__link" href="{{ $href }}">{{ $label }}<x-admiro::icon name="arrow-right" class="ss-stat-card__link-icon" /></a>
            @else
                {{ $label }}
            @endif
        </h3>
        @isset($actions)
            <span class="ss-stat-card__actions">{{ $actions }}</span>
        @endisset
    </header>
    <div class="ss-stat-card__panel">
        <p class="ss-stat-card__figure">
            <span class="ss-stat-card__value">{{ $value }}</span>
            @if (filled($unit))
                <span class="ss-stat-card__unit">{{ $unit }}</span>
            @endif
            @if (filled($trend))
                <span class="ss-stat-card__trend">{{ $trend }}</span>
            @endif
        </p>
        @if (filled($note))
            <p class="ss-stat-card__note">{{ $note }}</p>
        @endif
        @if ($meter !== null)
            <div class="ss-stat-card__meter">
                @if (isset($meter['low']) || isset($meter['high']))
                    <p class="ss-stat-card__meter-scale" aria-hidden="true"><span>{{ $meter['low'] ?? '' }}</span><span>{{ $meter['high'] ?? '' }}</span></p>
                @endif
                <div class="ss-stat-card__meter-track" role="meter" aria-valuemin="0" aria-valuemax="{{ $meter['max'] }}" aria-valuenow="{{ $meter['value'] }}" aria-label="{{ $meter['label'] ?? $label }}" data-ss-level="{{ \Simplysmart\Admiro\View\StatLevel::of($meter['value'], $meter['max']) }}">
                    <span class="ss-stat-card__meter-fill"><span class="ss-stat-card__meter-text">{{ $meter['valueText'] ?? $meter['value'] }}</span></span>
                    <span class="ss-stat-card__meter-rest"><span class="ss-stat-card__meter-text">{{ $meter['maxText'] ?? $meter['max'] }}</span></span>
                </div>
            </div>
        @endif
        @if ($barValues !== [])
            <ol class="ss-stat-card__bars" role="img" aria-label="{{ $bars['label'] }}">
                @foreach ($barValues as $index => $barValue)
                    <li @class(['ss-stat-card__bar', 'ss-stat-card__bar--current' => $index === $currentBar]) data-ss-level="{{ \Simplysmart\Admiro\View\StatLevel::of($barValue, $barMax) }}" title="{{ isset($bars['labels'][$index]) ? $bars['labels'][$index] . ': ' : '' }}{{ local_number($barValue) }}"></li>
                @endforeach
            </ol>
        @endif
        {{ $slot }}
    </div>
</section>

{{-- Karta-odnośnik postępu listy zadań (.ss-onboarding-card, wyróżniona .ss-card--feature: delikatne tło i cień) — np. onboarding na dashboardzie admiro albo konfiguracja organizacji na pulpicie strefy: duża, nieruchoma ikona w tle (icon, dekoracyjna), nagłówek z ikoną (po wszystkich zadaniach haczyk) i małą etykietą mono (eyebrow) nad tytułem (title, po wszystkich zadaniach completeTitle), opis (lead / completeLead), postęp (progressLabel z :done i :total, procent i pasek) i wezwanie do działania ze strzałką — następne niezrobione zadanie (nextLabel z :name), a po wszystkich reviewLabel. href: adres listy zadań; done, total: int (total > 0); next: ?string (nazwa następnego zadania, null po wszystkich); ring: procent w okrągłym wskaźniku po prawej od paska (zamiast liczby przy etykiecie); slot: warstwa dekoracji pod treścią (np. ilustracja strefy, aria-hidden), klasa z atrybutów trafia na kartę (np. motyw strefy). --}}
@props(['href', 'icon' => 'rocket', 'eyebrow', 'title', 'completeTitle', 'lead', 'completeLead', 'progressLabel', 'nextLabel', 'reviewLabel', 'done', 'total', 'next' => null, 'ring' => false])
@php($complete = $total > 0 && $done === $total)
@php($labelId = 'ss-checklist-card-' . md5($href))
@php($percent = (int) round($done / max($total, 1) * 100))
@php($circumference = 2 * M_PI * 19)
<a {{ $attributes->class(['ss-card', 'ss-card--feature', 'ss-onboarding-card', 'ss-onboarding-card--complete' => $complete]) }} href="{{ $href }}">
    <x-admiro::icon :name="$icon" class="ss-onboarding-card__watermark" />
    {{ $slot }}
    <span class="ss-onboarding-card__head">
        <x-admiro::icon :name="$complete ? 'check-circle' : $icon" class="ss-onboarding-card__icon" />
        <span class="ss-onboarding-card__heading">
            <span class="ss-onboarding-card__eyebrow">{{ $eyebrow }}</span>
            <span class="ss-onboarding-card__title">{{ $complete ? $completeTitle : $title }}</span>
        </span>
    </span>
    <span class="ss-onboarding-card__lead">{{ $complete ? $completeLead : $lead }}</span>
    <span @class(['ss-onboarding-card__progress', 'ss-onboarding-card__progress--ring' => $ring])>
        <span class="ss-onboarding-card__progress-label">
            <span id="{{ $labelId }}">{{ str_replace([':done', ':total'], [(string) $done, (string) $total], $progressLabel) }}</span>
            @unless ($ring)
                <span class="ss-onboarding-card__percent">{{ $percent }}%</span>
            @endunless
        </span>
        <progress class="ss-progress" value="{{ $done }}" max="{{ $total }}" aria-labelledby="{{ $labelId }}">{{ $done }}/{{ $total }}</progress>
        @if ($ring)
            <span class="ss-onboarding-card__ring" aria-hidden="true">
                <svg viewBox="0 0 44 44" focusable="false"><circle class="ss-onboarding-card__ring-track" cx="22" cy="22" r="19" /><circle class="ss-onboarding-card__ring-value" cx="22" cy="22" r="19" stroke-dasharray="{{ round($circumference * $percent / 100, 2) }} {{ round($circumference, 2) }}" transform="rotate(-90 22 22)" /></svg>
                <span class="ss-onboarding-card__ring-label">{{ $percent }}%</span>
            </span>
        @endif
    </span>
    <span class="ss-onboarding-card__action">{{ $next === null ? $reviewLabel : str_replace(':name', $next, $nextLabel) }}<x-admiro::icon name="arrow-right" class="ss-onboarding-card__arrow" /></span>
</a>

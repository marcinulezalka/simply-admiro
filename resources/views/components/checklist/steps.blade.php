{{-- Lista numerowana zadań bez karty, na tle strony (.ss-onboarding__steps w siatce): każde zadanie to panel-odnośnik z dużym przygaszonym numerem w tle, etykietą nad nazwą (eyebrow, np. „Zadanie N · stan”), nazwą z ikoną (haczyk dla zrobionych) i opisem; zrobione wyszarzone (.ss-card--muted) i oznaczone [data-ss-done] dla przełącznika ukrywania. items: list<View\Checklist\ChecklistItem>; id: id listy (cel przełącznika z x-admiro::checklist.progress); label: nazwa listy dla czytników ekranu. --}}
@props(['items', 'id', 'label'])
<ol class="ss-onboarding__steps ss-grid ss-grid--3" id="{{ $id }}" aria-label="{{ $label }}">
    @foreach ($items as $item)
        <li class="ss-onboarding__step" value="{{ $item->number }}"{!! $item->done ? ' data-ss-done' : '' !!}>
            <a class="ss-card ss-onboarding__link{{ $item->done ? ' ss-card--muted' : '' }}" href="{{ $item->url }}">
                <span class="ss-onboarding__number" aria-hidden="true">{{ str_pad((string) $item->number, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="ss-onboarding__eyebrow">{{ $item->eyebrow }}</span>
                <span class="ss-onboarding__name"><x-admiro::icon :name="$item->done ? 'check-circle' : $item->icon" class="ss-onboarding__name-icon" />{{ $item->title }}</span>
                <span class="ss-onboarding__text">{{ $item->lead }}</span>
            </a>
        </li>
    @endforeach
</ol>

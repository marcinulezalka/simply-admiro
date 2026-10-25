{{-- Kroki procesu (.ss-stepper): kroki przed bieżącym z haczykiem, bieżący z aria-current="step", stan każdego kroku także tekstem dla czytników (layout.steps.*). steps: list<string> (etykiety po kolei); current: indeks bieżącego kroku (null = żaden, np. proces przerwany; liczba ≥ count = wszystkie zakończone). --}}
@props(['steps', 'current' => null])
<ol {{ $attributes->merge(['class' => 'ss-stepper']) }}>
    @foreach (array_values($steps) as $index => $label)
        @php($state = $current !== null && $index < $current ? 'done' : ($index === $current ? 'current' : 'next'))
        <li {{ new \Illuminate\View\ComponentAttributeBag(array_filter(['class' => 'ss-stepper__step ss-stepper__step--' . $state, 'aria-current' => $state === 'current' ? 'step' : null])) }}>
            <span class="ss-stepper__marker" aria-hidden="true">
                @if ($state === 'done')
                    <x-mainboard::icon name="check" class="ss-stepper__icon" />
                @else
                    {{ $index + 1 }}
                @endif
            </span>
            <span class="ss-stepper__label">{{ $label }}<span class="ss-sr-only"> ({{ text('layout.steps.' . $state) }})</span></span>
        </li>
    @endforeach
</ol>

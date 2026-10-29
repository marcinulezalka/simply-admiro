{{-- Zakładka postępu listy zadań do slotu actions nagłówka strony (klasa slotu ss-context__actions--progress): ciemny przycisk z ikoną celu `goal`, obok etykieta (label, np. „Zrobione X z Y”) i pod nią pasek, rozwija listę zadań — na telefonie w panelu wysuwanym z prawej krawędzi okna, z nagłówkiem i przyciskiem zamknięcia — (numer albo haczyk, nazwa, plakietka stanu; odnośniki zadań), a pod separatorem przełącznik ukrywania zrobionych zadań (hideDoneLabel; js/hide-done.js chowa karty [data-ss-done] listy listId, wybór zapamiętany w przeglądarce pod storageKey; bez skryptu ukryty). items: list<View\Checklist\ChecklistItem>. --}}
@props(['items', 'label', 'hideDoneLabel', 'listId', 'storageKey'])
@php
    $doneCount = count(array_filter($items, static fn (\Simplysmart\Admiro\View\Checklist\ChecklistItem $item): bool => $item->done));
    $total = count($items);
@endphp
<details class="ss-onboarding__progress-menu" data-ss-dropdown>
    <summary class="ss-onboarding__progress">
        <span class="ss-onboarding__progress-button" aria-hidden="true"><x-admiro::icon name="goal" class="ss-onboarding__progress-icon" /></span>
        <span class="ss-onboarding__progress-text">
            <span class="ss-onboarding__progress-label" id="ss-onboarding-progress-label">{{ $label }}<x-admiro::icon name="chevron-down" class="ss-onboarding__progress-chevron" /></span>
            <progress class="ss-progress" id="ss-onboarding-progress" value="{{ $doneCount }}" max="{{ $total }}" aria-labelledby="ss-onboarding-progress-label">{{ $doneCount }}/{{ $total }}</progress>
        </span>
    </summary>
    <div class="ss-account-menu__panel ss-onboarding__progress-panel">
        <div class="ss-onboarding__progress-head">
            <p class="ss-onboarding__progress-title">{{ $label }}</p>
            <button class="ss-icon-button" type="button" title="{{ text('layout.close') }}" data-ss-dropdown-close>
                <x-admiro::icon name="close" class="ss-icon-button__icon" />
                <span class="ss-sr-only">{{ text('layout.close') }}</span>
            </button>
        </div>
        <ol class="ss-onboarding__progress-steps">
            @foreach ($items as $item)
                <li>
                    <a class="ss-account-menu__link ss-onboarding__progress-step" href="{{ $item->url }}">
                        <span @class(['ss-onboarding__progress-marker', 'ss-onboarding__progress-marker--done' => $item->done]) aria-hidden="true">
                            @if ($item->done)
                                <x-admiro::icon name="check" class="ss-onboarding__progress-marker-icon" />
                            @else
                                {{ $item->number }}
                            @endif
                        </span>
                        <span class="ss-onboarding__progress-step-name">{{ $item->title }}</span>
                        <span class="ss-badge ss-badge--outline ss-badge--{{ $item->tone }}">{{ $item->state }}</span>
                    </a>
                </li>
            @endforeach
        </ol>
        <div class="ss-onboarding__progress-setting" data-ss-hide-done-control hidden>
            <x-admiro::switch :id="$storageKey" :label="$hideDoneLabel" :data-ss-hide-done="$listId" :data-ss-hide-done-key="$storageKey" />
        </div>
    </div>
</details>
<x-admiro::scripts :files="['js/hide-done.js']" />

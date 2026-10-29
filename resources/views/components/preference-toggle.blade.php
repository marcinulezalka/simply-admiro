{{-- Przełącznik wyboru wyglądu (js/theme.js) w formie przełącznika segmentowego jak x-admiro::theme-toggle: preference — Enums\AppearancePreference (rozmiar tekstu, szerokość treści, krój), options — list<View\PreferenceOption> (View\AppearanceChoices); przycisk z ikoną albo znakiem (próbka kroju w data-ss-font-sample), wybrany ma aria-pressed="true"; labels: etykiety widoczne obok znaku (domyślnie tylko w title i dla czytników ekranu). Ukryty bez skryptu i przy pustej liście. --}}
@props(['preference', 'options' => [], 'labels' => false])
@if ($options !== [])
    <div {{ $attributes->merge(['class' => 'ss-theme-toggle ss-preference-toggle']) }} role="group" aria-label="{{ $preference->label() }}" data-ss-preference="{{ $preference->value }}" hidden>
        @foreach ($options as $option)
            <button @class(['ss-theme-toggle__option', 'ss-preference-toggle__option', 'ss-preference-toggle__option--labelled' => $labels]) type="button" aria-pressed="false" title="{{ $option->label }}" data-ss-preference-value="{{ $option->value }}">
                @if ($option->icon !== null)
                    <x-admiro::icon :name="$option->icon" class="ss-theme-toggle__icon" />
                @else
                    <span class="ss-preference-toggle__glyph" aria-hidden="true"@if ($option->sample !== null) data-ss-font-sample="{{ $option->sample }}"@endif>{{ $option->glyph }}</span>
                @endif
                @if ($labels)
                    <span>{{ $option->label }}</span>
                @else
                    <span class="ss-sr-only">{{ $option->label }}</span>
                @endif
            </button>
        @endforeach
    </div>
@endif

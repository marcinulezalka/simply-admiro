{{-- Przełącznik motywu (js/theme.js) w formie przełącznika segmentowego: jasny, ciemny, kontrast WCAG (czerń na bieli) i duży kontrast WCAG (żółty na czarnym) — Enums\Theme; wybrany przycisk ma aria-pressed="true", etykiety w title i dla czytników ekranu. Ukryty bez skryptu (działa wtedy motyw systemu). themes: list<Enums\Theme> — pokazywane motywy (domyślnie wszystkie). --}}
@props(['themes' => \Simplysmart\Admiro\Enums\Theme::cases()])
<div {{ $attributes->merge(['class' => 'ss-theme-toggle']) }} role="group" aria-label="{{ text('layout.theme') }}" data-ss-theme-toggle hidden>
    @foreach ($themes as $theme)
        <button class="ss-theme-toggle__option" type="button" aria-pressed="false" title="{{ $theme->label() }}" data-ss-theme="{{ $theme->value }}">
            <x-admiro::icon :name="$theme->icon()" class="ss-theme-toggle__icon" />
            <span class="ss-sr-only">{{ $theme->label() }}</span>
        </button>
    @endforeach
</div>

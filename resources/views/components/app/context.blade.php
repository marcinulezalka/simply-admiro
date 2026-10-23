{{-- Nagłówek kontekstowy (docs/design-system.md §5): powrót (back: adres albo null), metadane (meta), duży tytuł i akcje po prawej (slot actions, najwyżej jedna główna); na telefonie akcje pod tytułem. --}}
@props(['title', 'meta' => null, 'back' => null])
<div class="ss-context">
    <div class="ss-context__text">
        @if ($back !== null)
            <a class="ss-context__back ss-link" href="{{ $back }}"><x-mainboard::icon name="arrow-left" class="ss-context__back-icon" />{{ text_action('back') }}</a>
        @endif
        @if (filled($meta))
            <p class="ss-context__meta">{{ $meta }}</p>
        @endif
        <h1 class="ss-context__title">{{ $title }}</h1>
    </div>
    @isset($actions)
        <div class="ss-context__actions">{{ $actions }}</div>
    @endisset
</div>

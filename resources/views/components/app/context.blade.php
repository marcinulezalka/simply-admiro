{{-- Nagłówek kontekstowy strony (docs/design-system.md §5): opcjonalna ikona w kafelku (icon), powrót (back: adres albo null), duży tytuł, pod nim mniejszy opis krojem mono (meta) i — gdy strona je ma — panel akcji po prawej (slot actions, najwyżej jedna główna; .ss-context__actions to panel z ramką); na telefonie panel akcji pod tytułem. --}}
@props(['title', 'meta' => null, 'back' => null, 'icon' => null])
<div class="ss-context">
    <div class="ss-context__heading">
        @if (filled($icon))
            <x-admiro::icon :name="$icon" class="ss-context__icon" />
        @endif
        <div class="ss-context__text">
            @if ($back !== null)
                <a class="ss-context__back ss-link" href="{{ $back }}"><x-admiro::icon name="arrow-left" class="ss-context__back-icon" />{{ text_action('back') }}</a>
            @endif
            <h1 class="ss-context__title">{{ $title }}</h1>
            @if (filled($meta))
                <p class="ss-context__meta">{{ $meta }}</p>
            @endif
        </div>
    </div>
    @isset($actions)
        <div class="ss-context__actions" role="group" aria-label="{{ text('layout.page_actions') }}">{{ $actions }}</div>
    @endisset
</div>

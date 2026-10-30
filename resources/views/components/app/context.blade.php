{{-- Nagłówek kontekstowy strony (docs/design-system.md §5): opcjonalna ikona w kafelku (icon), odnośnik nad tytułem (back: adres albo null; domyślnie „Wstecz” ze strzałką, backLabel i backIcon zmieniają etykietę i ikonę — np. „Przełącz kontekst” z kotwicą przełącznika; backSignOut: adres wylogowania — wtedy odnośnik jest przyciskiem formularza POST w tym samym wyglądzie, np. „Wyloguj” na pulpicie konta z jednym kontekstem), mały prefiks nad tytułem (eyebrow, np. „Twój dzień”), duży tytuł, pod nim mniejszy opis krojem mono (meta) i — gdy strona je ma — panel akcji po prawej (slot actions, najwyżej jedna główna; .ss-context__actions to panel z ramką, klasa slotu — np. .ss-context__actions--progress — trafia na panel); na telefonie panel akcji pod tytułem. --}}
@props(['title', 'eyebrow' => null, 'meta' => null, 'back' => null, 'backLabel' => null, 'backIcon' => 'arrow-left', 'backSignOut' => null, 'icon' => null])
<div class="ss-context">
    <div class="ss-context__heading">
        @if (filled($icon))
            <x-admiro::icon :name="$icon" class="ss-context__icon" />
        @endif
        <div class="ss-context__text">
            @if ($backSignOut !== null)
                <form class="ss-context__back-form" method="POST" action="{{ $backSignOut }}">
                    @csrf
                    <button class="ss-context__back ss-link" type="submit"><x-admiro::icon :name="$backIcon" class="ss-context__back-icon" />{{ $backLabel ?? text('sign_in.sign_out') }}</button>
                </form>
            @elseif ($back !== null)
                <a class="ss-context__back ss-link" href="{{ $back }}"><x-admiro::icon :name="$backIcon" class="ss-context__back-icon" />{{ $backLabel ?? text_action('back') }}</a>
            @endif
            @if (filled($eyebrow))
                <p class="ss-context__eyebrow">{{ $eyebrow }}</p>
            @endif
            <h1 class="ss-context__title">{{ $title }}</h1>
            @if (filled($meta))
                <p class="ss-context__meta">{{ $meta }}</p>
            @endif
        </div>
    </div>
    @isset($actions)
        <div {{ $actions->attributes->class(['ss-context__actions'])->only('class') }} role="group" aria-label="{{ text('layout.page_actions') }}">{{ $actions }}</div>
    @endisset
</div>

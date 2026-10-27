{{-- Wpis słownika pojęć (Wiki\Wiki) jako alert informacyjny (.ss-wiki-tip, role="note"): ikona pojęcia na kafelku, znacznik z prefiksem (np. wiki-organizacja, krojem mono — także id elementu, więc wpis ma kotwicę), słowo kluczowe, przy wpisie Q&A pytanie (definicja jest odpowiedzią), definicja i przykład w ramce. term: klucz wpisu bez prefiksu (np. organizacja); nieznany klucz — nic. --}}
@props(['term'])
@php($entry = app(\Simplysmart\Admiro\Wiki\Wiki::class)->entry($term))
@if ($entry !== null)
    <aside {{ $attributes->class(['ss-wiki-tip']) }} id="{{ $entry->id() }}" role="note" aria-labelledby="{{ $entry->id() }}-term">
        <x-admiro::icon :name="$entry->icon" class="ss-icon--tile ss-wiki-tip__icon" />
        <div class="ss-wiki-tip__body">
            <p class="ss-wiki-tip__head">
                <span class="ss-wiki-tip__tag"><x-admiro::icon name="info" class="ss-wiki-tip__tag-icon" />{{ $entry->id() }}</span>
                <strong class="ss-wiki-tip__term" id="{{ $entry->id() }}-term">{{ $entry->term }}</strong>
            </p>
            @if ($entry->question !== null)
                <p class="ss-wiki-tip__question"><span class="ss-wiki-tip__qa" aria-hidden="true">Q</span>{{ $entry->question }}</p>
            @endif
            <p class="ss-wiki-tip__definition">@if ($entry->question !== null)<span class="ss-wiki-tip__qa ss-wiki-tip__qa--answer" aria-hidden="true">A</span>@endif{{ $entry->definition }}</p>
            @if ($entry->example !== null)
                <p class="ss-wiki-tip__example"><span class="ss-wiki-tip__example-label">{{ text('wiki.example') }}</span>{{ $entry->example }}</p>
            @endif
        </div>
    </aside>
@endif

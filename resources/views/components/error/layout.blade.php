{{-- Strona błędu HTTP (resources/views/app/errors/<kod>.blade.php, szkielet nadpisuje plikiem resources/views/errors/<kod>.blade.php): rama z kartą marki z logo i dużym kodem (odwrócona, wyniesiona) obok karty z tytułem, opisem i akcjami. Bez sesji, bazy i skryptów, żeby działała także przy awarii; nieindeksowana. code: kod HTTP; retry: zamiast powrotu akcja „Spróbuj ponownie” na bieżący adres. --}}
@props(['code', 'retry' => false])
@php
    $title = text('errors.' . $code . '.title');
    $previous = url()->previous();
@endphp
<x-admiro::page :title="$title" robots="noindex, nofollow">
    <x-slot:head>
        <x-admiro::styles />
    </x-slot:head>

    <div class="ss-auth ss-auth--split">
        <main class="ss-auth__cards ss-frame ss-frame--row">
            <aside class="ss-card ss-card--inverse ss-card--elevated ss-auth__brand ss-error__brand">
                <x-admiro::logo class="ss-auth__brand-logo" />
                <p class="ss-error__code" aria-hidden="true">{{ $code }}</p>
            </aside>

            <div class="ss-card ss-card--tinted ss-auth__card">
                <div class="ss-auth__header">
                    <p class="ss-error__label">{{ text('errors.code', ['code' => $code]) }}</p>
                    <h1 class="ss-auth__title">{{ $title }}</h1>
                    <p class="ss-auth__lead">{{ text('errors.' . $code . '.message') }}</p>
                </div>

                <div class="ss-error__actions">
                    <a class="ss-button ss-button--primary ss-button--pill" href="{{ url('/') }}"><x-admiro::icon name="home" class="ss-button__icon" />{{ text('errors.home') }}</a>
                    @if ($retry)
                        <a class="ss-button ss-button--pill" href="{{ url()->current() }}"><x-admiro::icon name="refresh" class="ss-button__icon" />{{ text('errors.retry') }}</a>
                    @elseif ($previous !== url()->current())
                        <a class="ss-button ss-button--pill" href="{{ $previous }}"><x-admiro::icon name="arrow-left" class="ss-button__icon" />{{ text('errors.back') }}</a>
                    @endif
                </div>
            </div>
        </main>
    </div>
</x-admiro::page>

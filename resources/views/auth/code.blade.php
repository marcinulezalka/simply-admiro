{{-- Kod z e-maila (teksty przez $zone->signInText(), paczka strefy może je podmienić): osobne pole na każdą cyfrę (x-admiro::code-field), adres e-mail we wstępie pogrubiony (wstawiany po escapowaniu tekstu). $rememberDeviceDays: ?int — dni zaufania urządzenia; null = strefa bez zaufanych urządzeń (bez checkboxa). --}}
@php
    $emailMarker = "\u{E000}";
    $intro = new \Illuminate\Support\HtmlString(str_replace(
        $emailMarker,
        '<strong>' . e($email) . '</strong>',
        e($zone->signInText('code_intro', ['email' => $emailMarker, 'length' => $codeLength, 'minutes' => $validForMinutes])),
    ));
@endphp
<x-admiro::auth.layout :title="$zone->signInText('code_title')" :lead="$intro" :note="$zone->signInText('code_note')">
    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.code.store')) }}">
        @csrf
        <x-admiro::code-field id="code" name="code" :label="$zone->signInText('code')" :length="$codeLength" icon="shield-check" :error="$errors->first('code')" autofocus />
        @if ($rememberDeviceDays !== null)
            <x-admiro::switch id="remember_device" name="remember_device" value="1" :label="$zone->signInText('remember_device', ['days' => $rememberDeviceDays])" />
        @endif
        <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ $zone->signInText('continue') }}<x-admiro::icon name="arrow-right" class="ss-button__icon" /></button>
    </form>

    <x-slot:actions>
        <a class="ss-button" href="{{ route($zone->routeName('auth.login')) }}"><x-admiro::icon name="mail" class="ss-button__icon" />{{ $zone->signInText('change_email') }}</a>
        <form method="POST" action="{{ route($zone->routeName('auth.code.resend')) }}">
            @csrf
            <button class="ss-button" type="submit" @if ($resendIn > 0) title="{{ $zone->signInText('resend_wait', ['seconds' => $resendIn]) }}" @endif><x-admiro::icon name="refresh" class="ss-button__icon" />{{ $zone->signInText('resend') }}</button>
        </form>
    </x-slot:actions>
</x-admiro::auth.layout>

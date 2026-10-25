{{-- Kod z e-maila (teksty przez $zone->signInText(), paczka strefy może je podmienić). $rememberDeviceDays: ?int — dni zaufania urządzenia; null = strefa bez zaufanych urządzeń (bez checkboxa). --}}
<x-admiro::auth.layout :title="$zone->signInText('code_title')" :lead="$zone->signInText('code_intro', ['email' => $email, 'length' => $codeLength, 'minutes' => $validForMinutes])" :note="$zone->signInText('code_note')">
    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.code.store')) }}">
        @csrf
        <div class="ss-field">
            <label class="ss-field__label" for="code">{{ $zone->signInText('code') }}</label>
            <div class="ss-input-wrap ss-input-wrap--icon">
                <x-admiro::icon name="shield-check" class="ss-input-wrap__icon" />
                <input class="ss-input ss-input--code" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="{{ $codeLength }}" required autofocus @error('code') aria-invalid="true" aria-describedby="code-error" @enderror>
            </div>
            @error('code') <p class="ss-field__error" id="code-error" role="alert">{{ $message }}</p> @enderror
        </div>
        @if ($rememberDeviceDays !== null)
            <x-admiro::switch id="remember_device" name="remember_device" value="1" :label="$zone->signInText('remember_device', ['days' => $rememberDeviceDays])" />
        @endif
        <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ $zone->signInText('continue') }}<x-admiro::icon name="arrow-right" class="ss-button__icon" /></button>
    </form>

    <x-slot:actions>
        <a class="ss-link" href="{{ route($zone->routeName('auth.login')) }}">{{ $zone->signInText('change_email') }}</a>
        <form method="POST" action="{{ route($zone->routeName('auth.code.resend')) }}">
            @csrf
            <button class="ss-button" type="submit" @if ($resendIn > 0) title="{{ $zone->signInText('resend_wait', ['seconds' => $resendIn]) }}" @endif><x-admiro::icon name="refresh" class="ss-button__icon" />{{ $zone->signInText('resend') }}</button>
        </form>
    </x-slot:actions>
</x-admiro::auth.layout>

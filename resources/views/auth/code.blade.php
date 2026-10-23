{{-- $rememberDeviceDays: ?int — dni zaufania urządzenia; null = strefa bez zaufanych urządzeń (bez checkboxa). --}}
<x-mainboard::auth.layout :title="text('sign_in.code_title')" :lead="text('sign_in.code_intro', ['email' => $email, 'length' => $codeLength, 'minutes' => $validForMinutes])">
    <form class="ss-form" method="POST" action="{{ route($zone->routeName('auth.code.store')) }}">
        @csrf
        <div class="ss-field">
            <label class="ss-field__label" for="code">{{ text('sign_in.code') }}</label>
            <input class="ss-input ss-input--code" id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="{{ $codeLength }}" required autofocus @error('code') aria-invalid="true" aria-describedby="code-error" @enderror>
            @error('code') <p class="ss-field__error" id="code-error" role="alert">{{ $message }}</p> @enderror
        </div>
        @if ($rememberDeviceDays !== null)
            <label class="ss-check" for="remember_device">
                <input id="remember_device" name="remember_device" type="checkbox" value="1">
                <span>{{ text('sign_in.remember_device', ['days' => $rememberDeviceDays]) }}</span>
            </label>
        @endif
        <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ text('sign_in.continue') }}</button>
    </form>

    <x-slot:actions>
        <a class="ss-link" href="{{ route($zone->routeName('auth.login')) }}">{{ text('sign_in.change_email') }}</a>
        <form method="POST" action="{{ route($zone->routeName('auth.code.resend')) }}">
            @csrf
            <button class="ss-button" type="submit" @if ($resendIn > 0) title="{{ text('sign_in.resend_wait', ['seconds' => $resendIn]) }}" @endif>{{ text('sign_in.resend') }}</button>
        </form>
    </x-slot:actions>
</x-mainboard::auth.layout>

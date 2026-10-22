{{-- $rememberDeviceDays: ?int — dni zaufania urządzenia; null = strefa bez zaufanych urządzeń (bez checkboxa). --}}
<x-mainboard::auth.layout :title="text('sign_in.code_title')">
    <p>{{ text('sign_in.code_intro', ['email' => $email, 'length' => $codeLength, 'minutes' => $validForMinutes]) }}</p>

    <form method="POST" action="{{ route($zone->routeName('auth.code.store')) }}">
        @csrf
        <label for="code">{{ text('sign_in.code') }}</label>
        <input id="code" name="code" inputmode="numeric" autocomplete="one-time-code" maxlength="{{ $codeLength }}" required autofocus>
        @error('code') <p role="alert">{{ $message }}</p> @enderror
        @if ($rememberDeviceDays !== null)
            <label for="remember_device">
                <input id="remember_device" name="remember_device" type="checkbox" value="1">
                {{ text('sign_in.remember_device', ['days' => $rememberDeviceDays]) }}
            </label>
        @endif
        <button type="submit">{{ text('sign_in.continue') }}</button>
    </form>

    <form method="POST" action="{{ route($zone->routeName('auth.code.resend')) }}">
        @csrf
        <button type="submit" @if ($resendIn > 0) title="{{ text('sign_in.resend_wait', ['seconds' => $resendIn]) }}" @endif>{{ text('sign_in.resend') }}</button>
    </form>

    <a href="{{ route($zone->routeName('auth.login')) }}">{{ text('sign_in.change_email') }}</a>
</x-mainboard::auth.layout>

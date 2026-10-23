{{-- Fałszywe logowanie pod adresami pułapek: wygląda jak logowanie strefy (ten sam układ i klasy co auth/login), wysyła na ten sam adres. $failed: bool, $email: ?string. Bez sesji, więc bez @csrf. --}}
<x-mainboard::auth.layout :title="text('sign_in.title')">
    <form class="ss-form" method="POST" action="{{ url()->current() }}">
        <div class="ss-field">
            <label class="ss-field__label" for="email">{{ text('sign_in.email') }}</label>
            <input class="ss-input" id="email" name="email" type="email" value="{{ $email }}" autocomplete="email" required autofocus @if ($failed) aria-invalid="true" aria-describedby="email-error" @endif>
            @if ($failed)
                <p class="ss-field__error" id="email-error" role="alert">{{ text('sign_in.failure') }}</p>
            @endif
        </div>
        <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ text('sign_in.continue') }}</button>
    </form>
</x-mainboard::auth.layout>

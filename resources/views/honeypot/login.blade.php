{{-- Fałszywe logowanie pod adresami pułapek: wygląda jak logowanie strefy, wysyła na ten sam adres. $failed: bool, $email: ?string. Bez sesji, więc bez @csrf. --}}
<x-mainboard::auth.layout :title="text('sign_in.title')">
    <form method="POST" action="{{ url()->current() }}">
        <label for="email">{{ text('sign_in.email') }}</label>
        <input id="email" name="email" type="email" value="{{ $email }}" autocomplete="email" required autofocus>
        @if ($failed)
            <p role="alert">{{ text('sign_in.failure') }}</p>
        @endif
        <button type="submit">{{ text('sign_in.continue') }}</button>
    </form>
</x-mainboard::auth.layout>

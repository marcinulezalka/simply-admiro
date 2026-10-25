{{-- Fałszywe logowanie pod adresami pułapek: wygląda jak logowanie strefy (ten sam układ i klasy co auth/login), wysyła na ten sam adres. $failed: bool, $email: ?string. Bez sesji, więc bez @csrf. --}}
<x-admiro::auth.layout :title="text('sign_in.title')">
    <form class="ss-form" method="POST" action="{{ url()->current() }}">
        <x-admiro::input id="email" :label="text('sign_in.email')" icon="mail" name="email" type="email" :value="$email" autocomplete="email" :placeholder="text('placeholders.email')" required autofocus :error="$failed ? text('sign_in.failure') : null" />
        <button class="ss-button ss-button--primary ss-button--block" type="submit">{{ text('sign_in.continue') }}<x-admiro::icon name="arrow-right" class="ss-button__icon" /></button>
    </form>
</x-admiro::auth.layout>

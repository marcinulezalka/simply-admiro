{{-- Profil zalogowanego konta: imię i nazwisko, adres e-mail i wybór koloru awatara (podgląd awatara w każdym kolorze, bieżący zaznaczony). $profile: ZoneAccount, $colors: list<AvatarColor>; $zone daje Core. --}}
<x-mainboard::zone.layout :title="text('account.profile_title')">
    <p class="ss-person">
        <x-mainboard::avatar :name="$profile->displayName()" :color="$profile->avatarColor()" size="lg" />
        <span class="ss-person__text"><strong>{{ $profile->displayName() }}</strong><span class="ss-muted">{{ $profile->emailAddress() }}</span></span>
    </p>

    <form method="POST" action="{{ route($zone->routeName('account.profile.update')) }}">
        @csrf
        @method('PUT')
        <x-mainboard::avatar-colors :name="$profile->displayName()" :current="$profile->avatarColor()" />
        @error('avatar_color') <p class="ss-field__error" role="alert">{{ $message }}</p> @enderror
        <button class="ss-button ss-button--primary" type="submit">{{ text_action('save') }}</button>
    </form>
</x-mainboard::zone.layout>

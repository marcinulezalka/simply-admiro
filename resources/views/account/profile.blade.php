{{-- Profil zalogowanego konta (ProfileController): dane konta (imię i nazwisko, adres e-mail tylko do odczytu, język interfejsu, gdy włączonych jest kilka) i wygląd (kolor awatara) w jednym formularzu; w strefie z `teams` organizacje konta z rolami i przełącznikiem. $profile: ZoneAccount, $colors: list<AvatarColor>, $locales: list<string> (puste = bez wyboru), $currentLocale: ?string, $organizations: list<array{organization: Organization, roles: list<string>, current: bool}>; $zone daje Core. --}}
@php($controller = \Simplysmart\Admiro\Http\Controllers\Account\ProfileController::class)
<x-admiro::zone.layout :title="text('account.profile_title')">
    <div class="ss-form-layout">
        <form class="ss-card" method="POST" action="{{ route($zone->routeName(\Simplysmart\Admiro\Http\AccountRoutes::PROFILE_UPDATE)) }}">
            @csrf
            @method('PUT')
            <h2 class="ss-card__title">{{ text('account.profile_details') }}</h2>
            <div class="ss-person ss-card__lead">
                <x-admiro::avatar :name="$profile->displayName()" :color="$profile->avatarColor()" size="lg" />
                <span class="ss-person__text">
                    <strong>{{ $profile->displayName() }}</strong>
                    <span class="ss-muted">{{ $profile->emailAddress() }}</span>
                </span>
            </div>
            <div class="ss-form">
                <x-admiro::input id="profile-name" :name="$controller::NAME_FIELD" :label="text('account.name')" icon="user" :value="old($controller::NAME_FIELD, $profile->displayName())" autocomplete="name" :placeholder="text('placeholders.full_name')" :error="$errors->first($controller::NAME_FIELD)" required />
                <x-admiro::input id="profile-email" :label="text('account.email')" icon="mail" type="email" :value="$profile->emailAddress()" :hint="text('account.email_hint')" readonly />
                @if ($locales !== [])
                    <div class="ss-field">
                        <label class="ss-field__label" for="profile-locale">{{ text('account.language') }}</label>
                        <select class="ss-input" id="profile-locale" name="{{ $controller::LOCALE_FIELD }}" aria-describedby="profile-locale-hint">
                            <option value="">{{ text('account.language_auto') }}</option>
                            @foreach ($locales as $locale)
                                <option value="{{ $locale }}" @selected(old($controller::LOCALE_FIELD, $currentLocale) === $locale)>{{ \Locale::getDisplayLanguage($locale, $locale) }}</option>
                            @endforeach
                        </select>
                        <p class="ss-field__hint" id="profile-locale-hint">{{ text('account.language_hint') }}</p>
                        @error($controller::LOCALE_FIELD) <p class="ss-field__error" role="alert">{{ $message }}</p> @enderror
                    </div>
                @endif

                <x-admiro::avatar-colors :name="$profile->displayName()" :current="$profile->avatarColor()" :field="$controller::COLOR_FIELD" />
                @error($controller::COLOR_FIELD) <p class="ss-field__error" role="alert">{{ $message }}</p> @enderror

                <div class="ss-form-actions">
                    <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="check" class="ss-button__icon" />{{ text_action('save') }}</button>
                </div>
            </div>
        </form>

        @if ($organizations !== [])
            <section class="ss-card">
                <h2 class="ss-card__title">{{ text('account.organizations_title') }}</h2>
                <ul class="ss-list">
                    @foreach ($organizations as $membership)
                        <li class="ss-list__item">
                            <span class="ss-list__main">
                                <span>{{ $membership['organization']->name }}</span>
                                <span class="ss-muted">{{ $membership['roles'] !== [] ? implode(' · ', $membership['roles']) : text('account.organization_no_roles') }}</span>
                            </span>
                            @if ($membership['current'])
                                <span class="ss-badge"><x-admiro::icon name="check" class="ss-badge__icon" />{{ text('account.organization_current') }}</span>
                            @elseif (count($organizations) > 1)
                                <form method="POST" action="{{ route($zone->routeName(\Simplysmart\Admiro\Http\AccountRoutes::ORGANIZATION_SWITCH)) }}">
                                    @csrf
                                    <input type="hidden" name="{{ \Simplysmart\Admiro\Http\Controllers\Account\OrganizationSwitchController::FIELD }}" value="{{ $membership['organization']->token }}">
                                    <button class="ss-button" type="submit"><x-admiro::icon name="switch" class="ss-button__icon" />{{ text('account.organization_switch') }}</button>
                                </form>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
</x-admiro::zone.layout>

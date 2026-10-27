{{-- Założenie właściciela strefy: strefa (z ZoneOwnerProvisioner), organizacja klienta (albo operatora), imię i nazwisko i adres e-mail na karcie formularza. $zones: list<ZoneDefinition>, $organizations: Collection<Organization> (aktywne), $selectedOrganization: string (token organizacji wybranej z góry, `?organization=`, np. z kreatora onboardingu; pusty — bez wyboru). --}}
<x-admiro::zone.layout :title="text('admiro::admin.owner_title')">
    @if ($zones === [])
        <p class="ss-notice">{{ text('admiro::admin.no_provisioners') }}</p>
    @else
        <form class="ss-card" method="POST" action="{{ route('admiro.owners.store') }}">
            @csrf
            <p class="ss-card__lead ss-muted">{{ text('admiro::admin.owner_intro') }}</p>
            <div class="ss-form">
                <div class="ss-form-row">
                    <div class="ss-field ss-form-row__grow">
                        <label class="ss-field__label" for="zone">{{ text('admiro::admin.zone') }}</label>
                        <select class="ss-input" id="zone" name="zone" required>
                            @foreach ($zones as $ownerZone)
                                <option value="{{ $ownerZone->name }}" @selected(old('zone') === $ownerZone->name)>{{ $ownerZone->name }}{{ $ownerZone->teams ? ' (' . text('admiro::admin.zone_teams') . ')' : '' }}</option>
                            @endforeach
                        </select>
                        @error('zone') <p class="ss-field__error" role="alert">{{ $message }}</p> @enderror
                    </div>
                    <div class="ss-field ss-form-row__grow">
                        <label class="ss-field__label" for="organization">{{ text('admiro::admin.organization') }}</label>
                        <select class="ss-input" id="organization" name="organization">
                            <option value="">{{ text('admiro::admin.organization_operator') }}</option>
                            @foreach ($organizations as $organization)
                                <option value="{{ $organization->token }}" @selected(old('organization', $selectedOrganization) === $organization->token)>{{ $organization->name }}</option>
                            @endforeach
                        </select>
                        @error('organization') <p class="ss-field__error" role="alert">{{ $message }}</p> @enderror
                    </div>
                </div>
                <x-admiro::input id="name" name="name" :label="text('admiro::admin.account_name')" icon="user" :value="old('name')" :placeholder="text('placeholders.full_name')" :error="$errors->first('name')" required maxlength="255" />
                <x-admiro::input id="email" name="email" type="email" :label="text('admiro::admin.account_email')" icon="mail" :value="old('email')" :placeholder="text('placeholders.email')" :error="$errors->first('email')" required maxlength="255" />
                <div class="ss-form-actions">
                    <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="user-plus" class="ss-button__icon" />{{ text_action('create') }}</button>
                </div>
            </div>
        </form>
    @endif
</x-admiro::zone.layout>

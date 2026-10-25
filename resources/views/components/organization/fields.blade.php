{{-- Pola danych organizacji (Organizations\OrganizationForm) w trzech kartach: dane organizacji (nazwa, nazwa skrócona, NIP, REGON, KRS; slot na dodatkowe pola, np. organizację nadrzędną), dane kontaktowe (telefon, e-mail, e-mail do rozliczeń) i dane adresowe. Bez przycisku i znacznika <form>. organization: ?Organization (null = nowa); disabled: bool (tylko do odczytu); Etykiety z text_data(Organization), wartości z old() albo z organizacji. --}}
@props(['organization' => null, 'disabled' => false])
@php
    $model = \Simplysmart\Admiro\Models\Organization::class;
    $value = static fn (string $field): mixed => old($field, $organization?->getAttribute($field));
    $label = static fn (string $field): string => text_data($model, $field);
    $length = \Simplysmart\Admiro\Models\Organization::DETAIL_LENGTHS;
@endphp
<section class="ss-card" aria-labelledby="organization-details-title">
    <h2 class="ss-card__title" id="organization-details-title">{{ text('layout.organization_form.details') }}</h2>
    <fieldset class="ss-form ss-fieldset" @disabled($disabled)>
        <x-admiro::input id="name" name="name" :label="$label('name')" icon="building" :value="$value('name')" :placeholder="text('placeholders.company')" :error="$errors->first('name')" required maxlength="255" />
        <x-admiro::input id="short_name" name="short_name" :label="$label('short_name')" :hint="text('layout.organization_form.short_name_hint')" :value="$value('short_name')" :error="$errors->first('short_name')" :maxlength="\Simplysmart\Admiro\Models\Organization::SHORT_NAME_MAX_LENGTH" />
        <div class="ss-form-row">
            <x-admiro::input id="nip" name="nip" field-class="ss-form-row__grow" :label="$label('nip')" :mask="\Simplysmart\Admiro\Enums\InputMask::TaxId" :value="$value('nip')" :error="$errors->first('nip')" autocomplete="off" />
            <x-admiro::input id="regon" name="regon" field-class="ss-form-row__grow" :label="$label('regon')" :value="$value('regon')" :error="$errors->first('regon')" :maxlength="$length['regon']" inputmode="numeric" autocomplete="off" />
            <x-admiro::input id="krs" name="krs" field-class="ss-form-row__grow" :label="$label('krs')" :value="$value('krs')" :error="$errors->first('krs')" :maxlength="$length['krs']" inputmode="numeric" autocomplete="off" />
        </div>
        {{ $slot }}
    </fieldset>
</section>

<section class="ss-card" aria-labelledby="organization-contact-title">
    <h2 class="ss-card__title" id="organization-contact-title">{{ text('layout.organization_form.contact') }}</h2>
    <fieldset class="ss-form ss-fieldset" @disabled($disabled)>
        <x-admiro::input id="phone" name="phone" type="tel" :label="$label('phone')" :mask="\Simplysmart\Admiro\Enums\InputMask::Phone" :value="$value('phone')" :error="$errors->first('phone')" />
        <div class="ss-form-row">
            <x-admiro::input id="email" name="email" type="email" field-class="ss-form-row__grow" :label="$label('email')" icon="mail" :value="$value('email')" :placeholder="text('placeholders.email')" :error="$errors->first('email')" :maxlength="$length['email']" />
            <x-admiro::input id="billing_email" name="billing_email" type="email" field-class="ss-form-row__grow" :label="$label('billing_email')" icon="mail" :hint="text('layout.organization_form.billing_email_hint')" :value="$value('billing_email')" :placeholder="text('placeholders.email')" :error="$errors->first('billing_email')" :maxlength="$length['billing_email']" />
        </div>
    </fieldset>
</section>

<section class="ss-card" aria-labelledby="organization-address-title">
    <h2 class="ss-card__title" id="organization-address-title">{{ text('layout.organization_form.address') }}</h2>
    <fieldset class="ss-form ss-fieldset" @disabled($disabled)>
        <x-admiro::input id="street" name="street" :label="$label('street')" icon="map-pin" :value="$value('street')" :error="$errors->first('street')" :maxlength="$length['street']" />
        <div class="ss-form-row">
            <x-admiro::input id="postal_code" name="postal_code" :label="$label('postal_code')" :mask="\Simplysmart\Admiro\Enums\InputMask::PostalCode" :value="$value('postal_code')" :error="$errors->first('postal_code')" />
            <x-admiro::input id="city" name="city" field-class="ss-form-row__grow" :label="$label('city')" :value="$value('city')" :error="$errors->first('city')" :maxlength="$length['city']" />
        </div>
    </fieldset>
</section>

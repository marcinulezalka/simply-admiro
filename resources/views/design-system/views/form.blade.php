{{-- Gotowy widok: formularz edycji rekordu w kartach (sekcje pól), komunikat stanu, jedna akcja główna i anulowanie. Przykład nic nie wysyła (bez <form>, przyciski type="button"). $view: DesignSystemView. --}}
@php
    use Simplysmart\Mainboard\View\Demo\DemoInvoices;
    $client = DemoInvoices::featured()->client;
    $back = route('mainboard.design-system.view', \Simplysmart\Mainboard\Enums\DesignSystemView::Record);
@endphp
<x-mainboard::design-system.shell :view="$view" module="clients" :title="text('design_system.demo.edit_client')" :meta="$client" :back="$back">
    <p class="ss-notice" role="status">{{ text('design_system.demo.saved') }}</p>

    <div class="ss-form-layout">
        <section class="ss-card">
            <h2 class="ss-card__title">{{ text('design_system.demo.basics') }}</h2>
            <p class="ss-card__lead ss-muted">{{ text('design_system.demo.form_lead') }}</p>
            <div class="ss-form">
                <x-mainboard::input id="demo-name" :label="text('design_system.demo.client_name')" icon="building" :value="$client" />
                <x-mainboard::input id="demo-tax" :label="text('design_system.demo.tax_id')" :mask="\Simplysmart\Mainboard\Enums\InputMask::TaxId" :value="substr(DemoInvoices::CLIENT_TAX_ID, 0, -1)" :error="text('design_system.sample.error')" />
                <div class="ss-field">
                    <label class="ss-field__label" for="demo-country">{{ text('design_system.demo.country') }}</label>
                    <select class="ss-input" id="demo-country">
                        @foreach (DemoInvoices::COUNTRIES as $country)
                            <option value="{{ $country }}">{{ \Locale::getDisplayRegion('-' . $country, app()->getLocale()) }}</option>
                        @endforeach
                    </select>
                </div>
                <x-mainboard::switch id="demo-active" :label="text('design_system.demo.active')" :checked="true" />
            </div>
        </section>

        <section class="ss-card">
            <h2 class="ss-card__title">{{ text('design_system.demo.contact') }}</h2>
            <div class="ss-form">
                <x-mainboard::input id="demo-email" :label="text('design_system.demo.email')" icon="mail" type="email" :value="DemoInvoices::CLIENT_EMAIL" />
                <x-mainboard::input id="demo-phone" :label="text('design_system.sample.masks.phone')" :mask="\Simplysmart\Mainboard\Enums\InputMask::Phone" />
                <x-mainboard::input id="demo-address" :label="text('design_system.demo.address')" icon="map-pin" :value="text('design_system.demo.address_value')" />
                <div class="ss-field">
                    <label class="ss-field__label" for="demo-notes">{{ text('design_system.demo.notes') }}</label>
                    <textarea class="ss-input" id="demo-notes" rows="3" aria-describedby="demo-notes-hint"></textarea>
                    <p class="ss-field__hint" id="demo-notes-hint">{{ text('design_system.demo.notes_hint') }}</p>
                </div>
            </div>
        </section>
    </div>

    <div class="ss-form-actions">
        <a class="ss-button" href="{{ $back }}">{{ text_action('cancel') }}</a>
        <button class="ss-button ss-button--primary" type="button">{{ text_action('save') }}</button>
    </div>
</x-mainboard::design-system.shell>

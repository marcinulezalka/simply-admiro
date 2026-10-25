{{-- Gotowy widok: szczegóły pojedynczego rekordu (§5): nagłówek kontekstowy z powrotem, metadanymi i akcjami, stepper z przypadków enuma statusu, siatka kart z parami etykieta → wartość. $view: DesignSystemView. --}}
@php
    use Simplysmart\Admiro\Enums\DesignSystemView;
    use Simplysmart\Admiro\View\Demo\DemoInvoices;
    use Simplysmart\Admiro\View\Demo\DemoInvoiceStatus;
    $invoice = DemoInvoices::featured();
    $money = static fn (float $amount): string => local_money($amount, DemoInvoices::CURRENCY);
@endphp
<x-admiro::design-system.shell :view="$view" module="invoices" :title="text('design_system.demo.invoice', ['number' => $invoice->number])" :meta="$invoice->client . ' · ' . local_date($invoice->issued)" :back="route('simplysmart.design-system.view', DesignSystemView::List)">
    <x-slot:actions>
        <button class="ss-button" type="button">{{ text('design_system.demo.export_pdf') }}</button>
        <a class="ss-button ss-button--primary" href="{{ route('simplysmart.design-system.view', DesignSystemView::Form) }}">{{ text_action('edit') }}</a>
    </x-slot:actions>

    <x-admiro::stepper :steps="array_map(static fn (DemoInvoiceStatus $step): string => $step->label(), DemoInvoiceStatus::cases())" :current="$invoice->status->position()" />

    <div class="ss-grid ss-grid--3">
        <section class="ss-card">
            <h2 class="ss-card__title">{{ text('design_system.demo.details') }}</h2>
            <dl class="ss-pairs">
                <div class="ss-pairs__row"><dt>{{ text('design_system.demo.number') }}</dt><dd>{{ $invoice->number }}</dd></div>
                <div class="ss-pairs__row"><dt>{{ text('design_system.demo.issued') }}</dt><dd>{{ local_date($invoice->issued) }}</dd></div>
                <div class="ss-pairs__row"><dt>{{ text('design_system.demo.due') }}</dt><dd>{{ local_date($invoice->due) }}</dd></div>
                <div class="ss-pairs__row"><dt>{{ text('design_system.demo.status') }}</dt><dd><span class="ss-badge">{{ $invoice->status->label() }}</span></dd></div>
            </dl>
        </section>

        <section class="ss-card">
            <h2 class="ss-card__title">{{ text('design_system.demo.buyer') }}</h2>
            <dl class="ss-pairs">
                <div class="ss-pairs__row"><dt>{{ text('design_system.demo.client_name') }}</dt><dd>{{ $invoice->client }}</dd></div>
                <div class="ss-pairs__row"><dt>{{ text('design_system.demo.tax_id') }}</dt><dd>{{ DemoInvoices::CLIENT_TAX_ID }}</dd></div>
                <div class="ss-pairs__row"><dt>{{ text('design_system.demo.address') }}</dt><dd>{{ text('design_system.demo.address_value') }}</dd></div>
            </dl>
        </section>

        <section class="ss-card">
            <h2 class="ss-card__title">{{ text('design_system.demo.payment') }}</h2>
            <dl class="ss-pairs">
                <div class="ss-pairs__row"><dt>{{ text('design_system.demo.net') }}</dt><dd>{{ $money($invoice->net) }}</dd></div>
                <div class="ss-pairs__row"><dt>{{ text('design_system.demo.vat', ['rate' => $invoice->vatRate]) }}</dt><dd>{{ $money($invoice->vat()) }}</dd></div>
                <div class="ss-pairs__row ss-pairs__row--total"><dt>{{ text('design_system.demo.gross') }}</dt><dd>{{ $money($invoice->gross()) }}</dd></div>
                <div class="ss-pairs__row"><dt>{{ text('design_system.demo.method') }}</dt><dd>{{ text('design_system.demo.method_value') }}</dd></div>
            </dl>
        </section>
    </div>
</x-admiro::design-system.shell>

{{-- Gotowy widok: lista wielu rekordów (tabela tylko dla wielu rekordów, §4) z wyszukiwaniem i akcją główną. $view: DesignSystemView, $invoices: list<DemoInvoice>. --}}
@php
    use Simplysmart\Mainboard\Enums\DesignSystemView;
    use Simplysmart\Mainboard\View\Demo\DemoInvoices;
@endphp
<x-mainboard::design-system.shell :view="$view" module="invoices" :title="text('design_system.demo.invoices_title')" :meta="text('design_system.demo.invoices_meta', ['count' => local_number(count($invoices))])">
    <x-slot:actions>
        <button class="ss-button" type="button">{{ text_action('filter') }}</button>
        <a class="ss-button ss-button--primary" href="{{ route('mainboard.design-system.view', DesignSystemView::Form) }}">{{ text('design_system.demo.new_invoice') }}</a>
    </x-slot:actions>

    <section class="ss-card ss-card--flush">
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th scope="col">{{ text('design_system.demo.number') }}</th>
                        <th scope="col">{{ text('design_system.demo.client') }}</th>
                        <th scope="col">{{ text('design_system.demo.issued') }}</th>
                        <th scope="col">{{ text('design_system.demo.due') }}</th>
                        <th scope="col" class="ss-table__number">{{ text('design_system.demo.amount') }}</th>
                        <th scope="col">{{ text('design_system.demo.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $invoice)
                        <tr>
                            <th scope="row"><a class="ss-link" href="{{ route('mainboard.design-system.view', DesignSystemView::Record) }}">{{ $invoice->number }}</a></th>
                            <td>{{ $invoice->client }}</td>
                            <td>{{ local_date($invoice->issued) }}</td>
                            <td>{{ local_date($invoice->due) }}</td>
                            <td class="ss-table__number">{{ local_money($invoice->gross(), DemoInvoices::CURRENCY) }}</td>
                            <td><span class="ss-badge">{{ $invoice->status->label() }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-mainboard::design-system.shell>

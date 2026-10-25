{{-- Gotowy widok: layout aplikacji z menu bocznym (x-admiro::app.layout) — menu zwijane do ikon, globalna wyszukiwarka i powiadomienia w top barze, nagłówek kontekstowy i treść; przykładowe dane faktur. $view: DesignSystemView, $invoices: list<DemoInvoice>. --}}
@php
    use Simplysmart\Admiro\Enums\DesignSystemView;
    use Simplysmart\Admiro\View\Demo\DemoInvoices;
    use Simplysmart\Admiro\View\Demo\DemoNavigation;
    use Simplysmart\Admiro\View\Demo\DemoNotifications;
    $link = static fn (DesignSystemView $target): string => route('simplysmart.design-system.view', $target);
@endphp
<x-admiro::app.layout
    :title="text('design_system.demo.invoices_title')"
    :meta="text('design_system.demo.invoices_meta', ['count' => local_number(count($invoices))])"
    :navigation="DemoNavigation::groups($view)"
    :search="DemoNavigation::search()"
    :account="DemoNavigation::account()"
    :contexts="DemoNavigation::contexts($view)"
    :notifications="DemoNotifications::panel($link(DesignSystemView::Notifications))"
    :stylesheets="['css/design-system.css']"
>
    <x-slot:before>
        <x-admiro::design-system.strip :current="$view->value" />
    </x-slot:before>

    <x-slot:actions>
        <a class="ss-button ss-button--primary" href="{{ $link(DesignSystemView::Form) }}"><x-admiro::icon name="invoice" class="ss-button__icon" />{{ text('design_system.demo.new_invoice') }}</a>
    </x-slot:actions>

    <section class="ss-card ss-card--flush">
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th scope="col">{{ text('design_system.demo.number') }}</th>
                        <th scope="col">{{ text('design_system.demo.client') }}</th>
                        <th scope="col">{{ text('design_system.demo.due') }}</th>
                        <th scope="col" class="ss-table__number">{{ text('design_system.demo.amount') }}</th>
                        <th scope="col">{{ text('design_system.demo.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $invoice)
                        <tr>
                            <th scope="row"><a class="ss-link" href="{{ $link(DesignSystemView::Record) }}">{{ $invoice->number }}</a></th>
                            <td>{{ $invoice->client }}</td>
                            <td>{{ local_date($invoice->due) }}</td>
                            <td class="ss-table__number">{{ local_money($invoice->gross(), DemoInvoices::CURRENCY) }}</td>
                            <td><span class="ss-badge">{{ $invoice->status->label() }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-admiro::app.layout>

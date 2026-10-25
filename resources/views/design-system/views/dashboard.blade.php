{{-- Gotowy widok: pulpit modułu (statystyki w kartach, ostatnie rekordy, zadania). $view: DesignSystemView, $invoices: list<DemoInvoice>. --}}
@php
    use Simplysmart\Mainboard\View\Demo\DemoInvoices;
    use Simplysmart\Mainboard\View\Demo\DemoInvoiceStatus;
    $open = array_filter($invoices, static fn ($invoice): bool => in_array($invoice->status, [DemoInvoiceStatus::Sent, DemoInvoiceStatus::Accepted], true));
    $overdue = DemoInvoices::overdue();
    $month = array_filter($invoices, static fn ($invoice): bool => str_starts_with($invoice->issued, substr(DemoInvoices::TODAY, 0, 7)));
    $stats = [
        'open' => local_money(array_sum(array_map(static fn ($invoice): float => $invoice->gross(), $open)), DemoInvoices::CURRENCY),
        'overdue' => local_number(count($overdue)),
        'month' => local_money(array_sum(array_map(static fn ($invoice): float => $invoice->net, $month)), DemoInvoices::CURRENCY),
    ];
@endphp
<x-mainboard::design-system.shell :view="$view" module="dashboard" :title="text('design_system.demo.greeting')" :meta="text('design_system.demo.today', ['date' => local_date(DemoInvoices::TODAY)])">
    <x-slot:actions>
        <a class="ss-button ss-button--primary" href="{{ route('mainboard.design-system.view', \Simplysmart\Mainboard\Enums\DesignSystemView::Form) }}">{{ text('design_system.demo.new_invoice') }}</a>
    </x-slot:actions>

    <div class="ss-grid ss-grid--3">
        @foreach ($stats as $key => $value)
            <section class="ss-card ss-stat">
                <h2 class="ss-stat__label">{{ text('design_system.demo.stats.' . $key) }}</h2>
                <p class="ss-stat__value">{{ $value }}</p>
                <p class="ss-stat__note">{{ text('design_system.demo.stat_note.' . $key) }}</p>
            </section>
        @endforeach
    </div>

    <div class="ss-grid ss-grid--wide">
        <section class="ss-card">
            <h2 class="ss-card__title">{{ text('design_system.demo.recent') }}</h2>
            <ul class="ss-list">
                @foreach (array_slice($invoices, 0, 4) as $invoice)
                    <li class="ss-list__item">
                        <span class="ss-list__main">
                            <a class="ss-link" href="{{ route('mainboard.design-system.view', \Simplysmart\Mainboard\Enums\DesignSystemView::Record) }}">{{ $invoice->number }}</a>
                            <span class="ss-muted">{{ $invoice->client }}</span>
                        </span>
                        <span class="ss-list__side">
                            <span class="ss-list__value">{{ local_money($invoice->gross(), DemoInvoices::CURRENCY) }}</span>
                            <span class="ss-badge">{{ $invoice->status->label() }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </section>

        <section class="ss-card">
            <h2 class="ss-card__title">{{ text('design_system.demo.tasks') }}</h2>
            <ul class="ss-list">
                @foreach (['reminder', 'correction', 'tax_id'] as $task)
                    <li class="ss-list__item"><span>{{ text('design_system.demo.task_items.' . $task) }}</span></li>
                @endforeach
            </ul>
        </section>
    </div>
</x-mainboard::design-system.shell>

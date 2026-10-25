{{-- Kursy walut w złotych: filtr waluty i okresu, tabela (dzień, waluta, kurs, źródło z numerem tabeli NBP) ze stronicowaniem i usunięciem kursu ręcznego; karty pobrania z NBP za okres i kursu ręcznego (settings.manage). $rates: LengthAwarePaginator<ExchangeRate> (z walutą), $currencies: Collection<Currency> (aktywne bez PLN), $currency: ?Currency, $from, $to: string, $canManage: bool. --}}
@php($column = static fn (string $name): string => text_data(\Simplysmart\Mainboard\Models\Dictionaries\ExchangeRate::class, $name))
<x-mainboard::zone.layout :title="text('admiro::admin.exchange_rates_title')">
    <p class="ss-notice"><x-mainboard::icon name="info" /> {{ text('admiro::admin.exchange_rates_lead') }}</p>
    @foreach (['rate', 'rate_currency', 'sync_from'] as $field)
        @error($field) <p class="ss-notice" role="alert"><x-mainboard::icon name="alert-triangle" /> {{ $message }}</p> @enderror
    @endforeach

    <form class="ss-card" method="GET" action="{{ route('admiro.exchange-rates.index') }}">
        <div class="ss-form-row">
            <div class="ss-field ss-form-row__grow">
                <label class="ss-field__label" for="currency">{{ $column('currency_id') }}</label>
                <select class="ss-input" id="currency" name="currency">
                    <option value="">{{ text('admiro::admin.exchange_rates_all') }}</option>
                    @foreach ($currencies as $option)
                        <option value="{{ $option->alpha_code_3 }}" @selected($currency?->id === $option->id)>{{ $option->alpha_code_3 }} · {{ $option->translatedName() }}</option>
                    @endforeach
                </select>
            </div>
            <x-mainboard::input id="from" name="from" type="date" :label="text('admiro::admin.exchange_rates_from')" :value="$from" />
            <x-mainboard::input id="to" name="to" type="date" :label="text('admiro::admin.exchange_rates_to')" :value="$to" />
            <button class="ss-button ss-button--primary" type="submit"><x-mainboard::icon name="search" class="ss-button__icon" />{{ text_action('search') }}</button>
        </div>
    </form>

    <section class="ss-card ss-card--flush">
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th scope="col">{{ $column('rate_date') }}</th>
                        <th scope="col">{{ $column('currency_id') }}</th>
                        <th scope="col">{{ $column('rate') }}</th>
                        <th scope="col">{{ $column('source') }}</th>
                        @if ($canManage)
                            <th scope="col"><span class="ss-sr-only">{{ text('admiro::admin.account_actions') }}</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rates as $rate)
                        <tr>
                            <td class="ss-table__number">{{ local_date($rate->rate_date) }}</td>
                            <th scope="row">{{ $rate->currency->alpha_code_3 }}</th>
                            <td class="ss-table__number">{{ $rate->rate }}</td>
                            <td><span class="ss-badge">{{ $rate->source->label() }}</span>@if ($rate->table_number !== null) <span class="ss-muted">{{ $rate->table_number }}</span>@endif</td>
                            @if ($canManage)
                                <td>
                                    @if ($rate->source === \Simplysmart\Mainboard\Enums\ExchangeRateSource::Manual)
                                        <form method="POST" action="{{ route('admiro.exchange-rates.destroy', ['rate' => $rate->token]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="ss-button" type="submit">{{ text('admiro::admin.exchange_rate_remove') }}</button>
                                        </form>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="5" class="ss-muted">{{ text('admiro::admin.exchange_rates_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <x-mainboard::pagination :paginator="$rates" />

    @if ($canManage)
        <div class="ss-form-layout">
            <form class="ss-card ss-card--tinted" method="POST" action="{{ route('admiro.exchange-rates.sync') }}">
                @csrf
                <h2 class="ss-card__title">{{ text('admiro::admin.exchange_rates_sync') }}</h2>
                <p class="ss-card__lead ss-muted">{{ text('admiro::admin.exchange_rates_sync_lead') }}</p>
                <div class="ss-form">
                    <div class="ss-form-row">
                        <x-mainboard::input id="sync_from" name="sync_from" type="date" field-class="ss-form-row__grow" :label="text('admiro::admin.exchange_rates_from')" :value="old('sync_from', now()->subDays(7)->toDateString())" required />
                        <x-mainboard::input id="sync_to" name="sync_to" type="date" field-class="ss-form-row__grow" :label="text('admiro::admin.exchange_rates_to')" :value="old('sync_to', now()->toDateString())" required />
                    </div>
                    <div class="ss-form-actions">
                        <button class="ss-button ss-button--primary" type="submit"><x-mainboard::icon name="refresh" class="ss-button__icon" />{{ text('admiro::admin.exchange_rates_sync_action') }}</button>
                    </div>
                </div>
            </form>

            <form class="ss-card" method="POST" action="{{ route('admiro.exchange-rates.store') }}">
                @csrf
                <h2 class="ss-card__title">{{ text('admiro::admin.exchange_rate_add') }}</h2>
                <p class="ss-card__lead ss-muted">{{ text('admiro::admin.exchange_rate_add_lead') }}</p>
                <div class="ss-form">
                    <div class="ss-form-row">
                        <div class="ss-field ss-form-row__grow">
                            <label class="ss-field__label" for="rate_currency">{{ $column('currency_id') }}</label>
                            <select class="ss-input" id="rate_currency" name="rate_currency">
                                @foreach ($currencies as $option)
                                    <option value="{{ $option->alpha_code_3 }}" @selected(old('rate_currency') === $option->alpha_code_3)>{{ $option->alpha_code_3 }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-mainboard::input id="rate_date" name="rate_date" type="date" field-class="ss-form-row__grow" :label="$column('rate_date')" icon="calendar" :value="old('rate_date')" required />
                        <x-mainboard::input id="rate" name="rate" field-class="ss-form-row__grow" :label="$column('rate')" inputmode="decimal" :value="old('rate')" :placeholder="text('admiro::admin.exchange_rate_placeholder')" required />
                    </div>
                    <div class="ss-form-actions">
                        <button class="ss-button" type="submit"><x-mainboard::icon name="check" class="ss-button__icon" />{{ text_action('create') }}</button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</x-mainboard::zone.layout>

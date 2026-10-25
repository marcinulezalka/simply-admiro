{{-- Stawki VAT kraju: tabela (rodzaj, stawka, od, do) z zakończeniem obowiązującej stawki i karta dodania stawki (settings.manage). $country: Country, $rates: Collection<TaxRate> (od najnowszych), $types: list<TaxRateType>, $canManage: bool. --}}
@php($column = static fn (string $name): string => text_data(\Simplysmart\Admiro\Models\Dictionaries\TaxRate::class, $name))
<x-admiro::zone.layout :title="text('admiro::admin.tax_rates_title') . ': ' . $country->translatedName()" :back="route('admiro.dictionaries.index', ['dictionary' => 'countries'])">
    @error('rate') <p class="ss-notice" role="alert"><x-admiro::icon name="alert-triangle" /> {{ $message }}</p> @enderror

    <section class="ss-card ss-card--flush">
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th scope="col">{{ $column('type') }}</th>
                        <th scope="col">{{ $column('rate') }}</th>
                        <th scope="col">{{ $column('valid_from') }}</th>
                        <th scope="col">{{ $column('valid_to') }}</th>
                        @if ($canManage)
                            <th scope="col"><span class="ss-sr-only">{{ text('admiro::admin.account_actions') }}</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rates as $rate)
                        <tr>
                            <td><span class="ss-badge">{{ $rate->type->label() }}</span></td>
                            <th scope="row" class="ss-table__number">{{ $rate->translatedName() }}</th>
                            <td class="ss-table__number">{{ local_date($rate->valid_from) }}</td>
                            <td class="ss-table__number">{{ $rate->valid_to ? local_date($rate->valid_to) : '—' }}</td>
                            @if ($canManage)
                                <td>
                                    @if ($rate->valid_to === null)
                                        <form class="ss-form-row" method="POST" action="{{ route('admiro.tax-rates.end', ['country' => $country->alpha_code_2, 'rate' => $rate->token]) }}">
                                            @csrf
                                            @method('PUT')
                                            <label class="ss-sr-only" for="valid-to-{{ $rate->token }}">{{ $column('valid_to') }}</label>
                                            <input class="ss-input" id="valid-to-{{ $rate->token }}" name="valid_to" type="date" required>
                                            <button class="ss-button" type="submit">{{ text('admiro::admin.tax_rate_end') }}</button>
                                        </form>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="5" class="ss-muted">{{ text('admiro::admin.tax_rates_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($canManage)
        <form class="ss-card ss-card--tinted" method="POST" action="{{ route('admiro.tax-rates.store', ['country' => $country->alpha_code_2]) }}">
            @csrf
            <h2 class="ss-card__title">{{ text('admiro::admin.tax_rate_add') }}</h2>
            <div class="ss-form">
                <div class="ss-form-row">
                    <div class="ss-field ss-form-row__grow">
                        <label class="ss-field__label" for="type">{{ $column('type') }}</label>
                        <select class="ss-input" id="type" name="type">
                            @foreach ($types as $type)
                                <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-admiro::input id="rate" name="rate" field-class="ss-form-row__grow" :label="$column('rate')" inputmode="decimal" :value="old('rate')" :error="$errors->first('rate')" />
                    <x-admiro::input id="valid_from" name="valid_from" type="date" field-class="ss-form-row__grow" :label="$column('valid_from')" icon="calendar" :value="old('valid_from')" :error="$errors->first('valid_from')" required />
                </div>
                <x-admiro::input id="note" name="note" :label="$column('note')" :value="old('note')" maxlength="255" />
                <div class="ss-form-actions">
                    <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="check" class="ss-button__icon" />{{ text_action('create') }}</button>
                </div>
            </div>
        </form>
    @endif
</x-admiro::zone.layout>

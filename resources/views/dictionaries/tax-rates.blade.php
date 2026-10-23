{{-- Stawki VAT kraju. $country: Country, $rates: Collection<TaxRate> (od najnowszych), $types: list<TaxRateType>, $canManage: bool (settings.manage). --}}
<x-mainboard::zone.layout :title="text('admiro::admin.tax_rates_title') . ': ' . $country->translatedName()">
    @error('rate') <p role="alert">{{ $message }}</p> @enderror

    <table>
        <thead>
            <tr>
                <th scope="col">{{ text_data(\Simplysmart\Mainboard\Models\Dictionaries\TaxRate::class, 'type') }}</th>
                <th scope="col">{{ text_data(\Simplysmart\Mainboard\Models\Dictionaries\TaxRate::class, 'rate') }}</th>
                <th scope="col">{{ text_data(\Simplysmart\Mainboard\Models\Dictionaries\TaxRate::class, 'valid_from') }}</th>
                <th scope="col">{{ text_data(\Simplysmart\Mainboard\Models\Dictionaries\TaxRate::class, 'valid_to') }}</th>
                @if ($canManage)
                    <th scope="col">{{ text('admiro::admin.account_actions') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($rates as $rate)
                <tr>
                    <td>{{ $rate->type->label() }}</td>
                    <td>{{ $rate->translatedName() }}</td>
                    <td>{{ local_date($rate->valid_from) }}</td>
                    <td>{{ $rate->valid_to ? local_date($rate->valid_to) : '—' }}</td>
                    @if ($canManage)
                        <td>
                            @if ($rate->valid_to === null)
                                <form method="POST" action="{{ route('admiro.tax-rates.end', ['country' => $country->alpha_code_2, 'rate' => $rate->token]) }}">
                                    @csrf
                                    @method('PUT')
                                    <label for="valid-to-{{ $rate->token }}">{{ text_data(\Simplysmart\Mainboard\Models\Dictionaries\TaxRate::class, 'valid_to') }}</label>
                                    <input id="valid-to-{{ $rate->token }}" name="valid_to" type="date" required>
                                    <button type="submit">{{ text('admiro::admin.tax_rate_end') }}</button>
                                </form>
                            @endif
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ $canManage ? 5 : 4 }}">{{ text('admiro::admin.tax_rates_empty') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($canManage)
        <h2>{{ text('admiro::admin.tax_rate_add') }}</h2>
        <form method="POST" action="{{ route('admiro.tax-rates.store', ['country' => $country->alpha_code_2]) }}">
            @csrf
            <label for="type">{{ text_data(\Simplysmart\Mainboard\Models\Dictionaries\TaxRate::class, 'type') }}</label>
            <select id="type" name="type">
                @foreach ($types as $type)
                    <option value="{{ $type->value }}" @selected(old('type') === $type->value)>{{ $type->label() }}</option>
                @endforeach
            </select>
            <label for="rate">{{ text_data(\Simplysmart\Mainboard\Models\Dictionaries\TaxRate::class, 'rate') }}</label>
            <input id="rate" name="rate" inputmode="decimal" value="{{ old('rate') }}">
            <label for="valid_from">{{ text_data(\Simplysmart\Mainboard\Models\Dictionaries\TaxRate::class, 'valid_from') }}</label>
            <input id="valid_from" name="valid_from" type="date" value="{{ old('valid_from') }}" required>
            <label for="note">{{ text_data(\Simplysmart\Mainboard\Models\Dictionaries\TaxRate::class, 'note') }}</label>
            <input id="note" name="note" value="{{ old('note') }}" maxlength="255">
            <button type="submit">{{ text_action('create') }}</button>
        </form>
    @endif
</x-mainboard::zone.layout>

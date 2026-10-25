{{-- Święta i dni wolne kraju w roku: wybór kraju i roku, tabela (dzień, nazwa, źródło) z usunięciem wpisu ręcznego; karty pobrania z kalendarza świąt i dnia wolnego ręcznego (settings.manage). $countries: Collection<Country>, $country: ?Country, $year: int, $holidays: Collection<Holiday>, $canManage: bool. --}}
@php($column = static fn (string $name): string => text_data(\Simplysmart\Admiro\Models\Dictionaries\Holiday::class, $name))
<x-admiro::zone.layout :title="text('admiro::admin.holidays_title')" :meta="$country?->translatedName()">
    <p class="ss-notice"><x-admiro::icon name="info" /> {{ text('admiro::admin.holidays_lead') }}</p>
    @error('holiday') <p class="ss-notice" role="alert"><x-admiro::icon name="alert-triangle" /> {{ $message }}</p> @enderror

    <form class="ss-card" method="GET" action="{{ route('admiro.holidays.index') }}">
        <div class="ss-form-row">
            <div class="ss-field ss-form-row__grow">
                <label class="ss-field__label" for="country">{{ $column('country_id') }}</label>
                <select class="ss-input" id="country" name="country">
                    @foreach ($countries as $option)
                        <option value="{{ $option->alpha_code_2 }}" @selected($country?->id === $option->id)>{{ $option->translatedName() }}</option>
                    @endforeach
                </select>
            </div>
            <x-admiro::input id="year" name="year" type="number" :label="text('admiro::admin.holidays_year')" :value="(string) $year" min="2000" max="2100" />
            <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="search" class="ss-button__icon" />{{ text_action('search') }}</button>
        </div>
    </form>

    <section class="ss-card ss-card--flush">
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th scope="col">{{ $column('holiday_date') }}</th>
                        <th scope="col">{{ $column('name') }}</th>
                        <th scope="col">{{ $column('source') }}</th>
                        @if ($canManage)
                            <th scope="col"><span class="ss-sr-only">{{ text('admiro::admin.account_actions') }}</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($holidays as $holiday)
                        <tr>
                            <td class="ss-table__number">{{ local_date($holiday->holiday_date) }} <span class="ss-muted">{{ local_day($holiday->holiday_date->dayOfWeekIso, 'dd') }}</span></td>
                            <th scope="row">{{ $holiday->name }}</th>
                            <td><span class="ss-badge">{{ $holiday->source->label() }}</span></td>
                            @if ($canManage)
                                <td>
                                    @if ($holiday->source === \Simplysmart\Admiro\Enums\HolidaySource::Manual)
                                        <form method="POST" action="{{ route('admiro.holidays.destroy', ['country' => $country?->alpha_code_2, 'holiday' => $holiday->token]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="ss-button" type="submit">{{ text('admiro::admin.holiday_remove') }}</button>
                                        </form>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="4" class="ss-muted">{{ text('admiro::admin.holidays_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($canManage && $country !== null)
        <div class="ss-form-layout">
            <form class="ss-card ss-card--tinted" method="POST" action="{{ route('admiro.holidays.sync') }}">
                @csrf
                <input type="hidden" name="country" value="{{ $country->alpha_code_2 }}">
                <input type="hidden" name="year" value="{{ $year }}">
                <h2 class="ss-card__title">{{ text('admiro::admin.holidays_sync') }}</h2>
                <p class="ss-card__lead ss-muted">{{ text('admiro::admin.holidays_sync_lead', ['country' => $country->translatedName(), 'year' => (string) $year]) }}</p>
                <div class="ss-form-actions">
                    <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="refresh" class="ss-button__icon" />{{ text('admiro::admin.holidays_sync_action') }}</button>
                </div>
            </form>

            <form class="ss-card" method="POST" action="{{ route('admiro.holidays.store') }}">
                @csrf
                <input type="hidden" name="country" value="{{ $country->alpha_code_2 }}">
                <h2 class="ss-card__title">{{ text('admiro::admin.holiday_add') }}</h2>
                <div class="ss-form">
                    <div class="ss-form-row">
                        <x-admiro::input id="holiday_date" name="holiday_date" type="date" field-class="ss-form-row__grow" :label="$column('holiday_date')" icon="calendar" :value="old('holiday_date')" required />
                        <x-admiro::input id="holiday_name" name="holiday_name" field-class="ss-form-row__grow" :label="$column('name')" :value="old('holiday_name')" :placeholder="text('admiro::admin.holiday_placeholder')" required maxlength="255" />
                    </div>
                    <div class="ss-form-actions">
                        <button class="ss-button" type="submit"><x-admiro::icon name="check" class="ss-button__icon" />{{ text_action('create') }}</button>
                    </div>
                </div>
            </form>
        </div>
    @endif
</x-admiro::zone.layout>

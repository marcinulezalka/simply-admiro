{{-- Słownik lokalizacji: zakładki ustawień i słowników, tabela wpisów (kod, nazwa, stan i wpis domyślny jako plakietki; przy krajach odnośnik do stawek VAT) z włączaniem i wyłączaniem (settings.manage). $dictionary: Dictionary (bieżący), $dictionaries: list<Dictionary>, $entries: Collection wpisów (także wyłączonych), $codeColumn: string, $canManage: bool. --}}
<x-mainboard::zone.layout :title="text('admiro::admin.dictionary.' . $dictionary->value)">
    @include('admiro::panel.dictionaries.tabs', ['current' => $dictionary])

    @error('dictionary') <p class="ss-notice" role="alert"><x-mainboard::icon name="alert-triangle" /> {{ $message }}</p> @enderror

    <section class="ss-card ss-card--flush">
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th scope="col">{{ text('admiro::admin.dictionary_code') }}</th>
                        <th scope="col">{{ text('admiro::admin.dictionary_name') }}</th>
                        <th scope="col">{{ text('admiro::admin.account_state') }}</th>
                        @if ($canManage)
                            <th scope="col"><span class="ss-sr-only">{{ text('admiro::admin.account_actions') }}</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                        <tr>
                            <td class="ss-table__number">{{ $entry->getAttribute($codeColumn) }}</td>
                            <th scope="row">
                                {{ $entry->translatedName() }}
                                @if ($dictionary === \Simplysmart\Mainboard\Localization\Dictionary::Countries)
                                    <span class="ss-table__sub"><a class="ss-link" href="{{ route('admiro.tax-rates.index', ['country' => $entry->getAttribute($codeColumn)]) }}">{{ text('admiro::admin.tax_rates_title') }}</a></span>
                                @endif
                            </th>
                            <td>
                                <span class="ss-badge">{{ $entry->is_active ? text('admiro::admin.state_active') : text('admiro::admin.state_inactive') }}</span>
                                @if ($entry->is_default)
                                    <span class="ss-badge"><x-mainboard::icon name="check" class="ss-badge__icon" />{{ text('admiro::admin.dictionary_default') }}</span>
                                @endif
                            </td>
                            @if ($canManage)
                                <td>
                                    <form method="POST" action="{{ route('admiro.dictionaries.toggle', ['dictionary' => $dictionary->value, 'code' => $entry->getAttribute($codeColumn)]) }}">
                                        @csrf
                                        <button class="ss-button" type="submit">{{ $entry->is_active ? text('admiro::admin.action.deactivate') : text('admiro::admin.organization_enable') }}</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-mainboard::zone.layout>

{{-- Słownik lokalizacji. $dictionary: Dictionary (bieżący), $dictionaries: list<Dictionary>, $entries: Collection wpisów (także wyłączonych), $codeColumn: string, $canManage: bool (settings.manage). --}}
<x-mainboard::zone.layout :title="text('admiro::admin.dictionary.' . $dictionary->value)">
    <nav aria-label="{{ text('admiro::admin.dictionaries_title') }}">
        @foreach ($dictionaries as $other)
            <a href="{{ route('admiro.dictionaries.index', ['dictionary' => $other->value]) }}" @if ($other === $dictionary) aria-current="page" @endif>{{ text('admiro::admin.dictionary.' . $other->value) }}</a>
        @endforeach
    </nav>

    @error('dictionary') <p role="alert">{{ $message }}</p> @enderror

    <table>
        <thead>
            <tr>
                <th scope="col">{{ text('admiro::admin.dictionary_code') }}</th>
                <th scope="col">{{ text('admiro::admin.dictionary_name') }}</th>
                <th scope="col">{{ text('admiro::admin.account_state') }}</th>
                @if ($canManage)
                    <th scope="col">{{ text('admiro::admin.account_actions') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($entries as $entry)
                <tr>
                    <td>{{ $entry->getAttribute($codeColumn) }}</td>
                    <td>
                        {{ $entry->translatedName() }}
                        @if ($dictionary === \Simplysmart\Mainboard\Localization\Dictionary::Countries)
                            (<a href="{{ route('admiro.tax-rates.index', ['country' => $entry->getAttribute($codeColumn)]) }}">{{ text('admiro::admin.tax_rates_title') }}</a>)
                        @endif
                    </td>
                    <td>{{ $entry->is_active ? text('admiro::admin.state_active') : text('admiro::admin.state_inactive') }}{{ $entry->is_default ? ', ' . text('admiro::admin.dictionary_default') : '' }}</td>
                    @if ($canManage)
                        <td>
                            <form method="POST" action="{{ route('admiro.dictionaries.toggle', ['dictionary' => $dictionary->value, 'code' => $entry->getAttribute($codeColumn)]) }}">
                                @csrf
                                <button type="submit">{{ $entry->is_active ? text('admiro::admin.action.deactivate') : text('admiro::admin.organization_enable') }}</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>
</x-mainboard::zone.layout>

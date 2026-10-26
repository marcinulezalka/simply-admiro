{{-- Słownik lokalizacji: zakładki ustawień i słowników, tabela wpisów (kod — przy językach z flagami krajów języka, przy krajach z flagą; nazwa; stan; wpis domyślny jako plakietka; przy krajach odnośnik do stawek VAT; wiersz z kotwicą #entry-<kod> dla globalnej wyszukiwarki) z przełącznikiem włącz/wyłącz wysyłanym od razu (settings.manage, data-ss-autosubmit; wpisu domyślnego i języka interfejsu nie da się wyłączyć). $dictionary: Dictionary (bieżący), $dictionaries: list<Dictionary>, $entries: Collection wpisów (także wyłączonych), $codeColumn: string, $canManage: bool. --}}
<x-admiro::zone.layout :title="text('admiro::admin.dictionary.' . $dictionary->value)">
    @include('admiro::panel.dictionaries.tabs', ['current' => $dictionary])

    @error('dictionary') <p class="ss-notice" role="alert"><x-admiro::icon name="alert-triangle" /> {{ $message }}</p> @enderror

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
                        @php($code = (string) $entry->getAttribute($codeColumn))
                        @php($flags = $entry instanceof \Simplysmart\Admiro\Models\Dictionaries\Language ? $entry->flagIcons() : array_values(array_filter([$entry->getAttribute('flag_icon')])))
                        @php($locked = $entry->is_default || ($entry instanceof \Simplysmart\Admiro\Models\Dictionaries\Language && $entry->is_interface))
                        <tr id="entry-{{ $code }}">
                            <td class="ss-table__number">
                                <span class="ss-dictionary-code">
                                    @if ($flags !== [])
                                        <span class="ss-dictionary-code__flags" aria-hidden="true">
                                            @foreach (array_slice($flags, 0, 4) as $flag)
                                                <x-admiro::flag :icon="$flag" />
                                            @endforeach
                                            @if (count($flags) > 4)
                                                <span class="ss-dictionary-code__more">+{{ count($flags) - 4 }}</span>
                                            @endif
                                        </span>
                                    @endif
                                    {{ $code }}
                                </span>
                            </td>
                            <th scope="row">
                                {{ $entry->translatedName() }}
                                @if ($dictionary === \Simplysmart\Admiro\Localization\Dictionary::Countries)
                                    <span class="ss-table__sub"><a class="ss-link" href="{{ route('admiro.tax-rates.index', ['country' => $entry->getAttribute($codeColumn)]) }}">{{ text('admiro::admin.tax_rates_title') }}</a></span>
                                @endif
                            </th>
                            <td>
                                <span class="ss-badge">{{ $entry->is_active ? text('admiro::admin.state_active') : text('admiro::admin.state_inactive') }}</span>
                                @if ($entry->is_default)
                                    <span class="ss-badge"><x-admiro::icon name="check" class="ss-badge__icon" />{{ text('admiro::admin.dictionary_default') }}</span>
                                @endif
                            </td>
                            @if ($canManage)
                                <td>
                                    <form method="POST" action="{{ route('admiro.dictionaries.toggle', ['dictionary' => $dictionary->value, 'code' => $code]) }}" data-ss-autosubmit>
                                        @csrf
                                        <x-admiro::switch :id="'entry-switch-' . $code" :label="text('admiro::admin.dictionary_switch', ['name' => $entry->translatedName()])" :checked="$entry->is_active" :disabled="$locked" hide-label />
                                        @unless ($locked)
                                            <button class="ss-button ss-button--sm" type="submit" data-ss-autosubmit-fallback>{{ $entry->is_active ? text('admiro::admin.action.deactivate') : text('admiro::admin.organization_enable') }}</button>
                                        @endunless
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
</x-admiro::zone.layout>

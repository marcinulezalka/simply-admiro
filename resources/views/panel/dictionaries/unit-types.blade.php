{{-- Słownik typów jednostek organizacyjnych (Models\OrganizationUnitType): tabela — nazwa z ikoną, prefiks numeru, struktura wewnętrzna, domyślnie w publicznym grafiku, stan — a z settings.manage w każdym wierszu rozwijana edycja i włączenie/wyłączenie oraz karta dodania typu. Strefy listują aktywne typy przy jednostkach (Queries\Organizations\OrganizationUnitTypeList). $types: Collection<OrganizationUnitType>, $icons: list<string> (Organization::ICONS), $canManage: bool. --}}
@php($column = static fn (string $name): string => text_data(\Simplysmart\Admiro\Models\OrganizationUnitType::class, $name))
<x-admiro::zone.layout :title="text('admiro::admin.unit_types.title')" :meta="text('admiro::admin.unit_types.lead')">
    <section class="ss-card ss-card--flush">
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th scope="col">{{ $column('name') }}</th>
                        <th scope="col">{{ $column('number_prefix') }}</th>
                        <th scope="col">{{ $column('is_internal') }}</th>
                        <th scope="col">{{ $column('is_public') }}</th>
                        <th scope="col">{{ $column('is_active') }}</th>
                        @if ($canManage)
                            <th scope="col"><span class="ss-sr-only">{{ text('admiro::admin.account_actions') }}</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($types as $type)
                        <tr>
                            <th scope="row"><span class="ss-preview__icon-line"><x-admiro::icon :name="$type->iconName()" class="ss-preview__icon" />{{ $type->name }}</span></th>
                            <td><code>{{ $type->number_prefix !== '' ? $type->number_prefix : '—' }}</code></td>
                            <td>{{ $type->is_internal ? text('admiro::admin.unit_types.yes') : text('admiro::admin.unit_types.no') }}</td>
                            <td>{{ $type->is_public ? text('admiro::admin.unit_types.yes') : text('admiro::admin.unit_types.no') }}</td>
                            <td>
                                @if ($canManage)
                                    <form method="POST" action="{{ route('admiro.unit-types.toggle', ['unitType' => $type->token]) }}" data-ss-autosubmit>
                                        @csrf
                                        <x-admiro::switch :id="'unit-type-active-' . $type->token" name="is_active" value="1" :label="$column('is_active')" hide-label :checked="(bool) $type->is_active" />
                                        <button class="ss-button ss-button--sm" type="submit" data-ss-autosubmit-fallback>{{ text_action('save') }}</button>
                                    </form>
                                @else
                                    {{ $type->is_active ? text('admiro::admin.unit_types.yes') : text('admiro::admin.unit_types.no') }}
                                @endif
                            </td>
                            @if ($canManage)
                                <td>
                                    <details>
                                        <summary class="ss-link">{{ text_action('edit') }}</summary>
                                        <form class="ss-form" method="POST" action="{{ route('admiro.unit-types.update', ['unitType' => $type->token]) }}">
                                            @csrf
                                            @method('PUT')
                                            @include('admiro::panel.dictionaries.unit-type-fields', ['prefix' => 'unit-type-' . $type->token, 'type' => $type])
                                            <div class="ss-form-actions">
                                                <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="check" class="ss-button__icon" />{{ text_action('save') }}</button>
                                            </div>
                                        </form>
                                    </details>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="6" class="ss-muted">{{ text('admiro::admin.unit_types.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if ($canManage)
        <form class="ss-card ss-card--tinted" method="POST" action="{{ route('admiro.unit-types.store') }}">
            @csrf
            <h2 class="ss-card__title">{{ text('admiro::admin.unit_types.add') }}</h2>
            <div class="ss-form">
                @include('admiro::panel.dictionaries.unit-type-fields', ['prefix' => 'unit-type-new', 'type' => null])
                <div class="ss-form-actions">
                    <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="check" class="ss-button__icon" />{{ text_action('create') }}</button>
                </div>
            </div>
        </form>
    @endif
</x-admiro::zone.layout>

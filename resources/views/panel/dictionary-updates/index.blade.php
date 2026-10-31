{{-- Prośby stref o aktualizację słowników (Panel\Http\Controllers\DictionaryUpdateController): wstęp, tabela próśb (słownik, strefa i organizacja proszącego, data prośby, stan z plakietką, decyzja, wynik zadania) z „Zatwierdź” (aktualizacja w kolejce) i „Odrzuć” przy prośbach czekających (settings.manage), pod nią zarejestrowane słowniki. $requests: Collection<Models\DictionaryUpdateRequest>, $organizations: array<int, string>, $dictionaries: list<Contracts\DictionaryUpdater>, $canManage: bool. --}}
@php($column = static fn (string $name): string => text_data(\Simplysmart\Admiro\Models\DictionaryUpdateRequest::class, $name))
<x-admiro::zone.layout :title="text('admiro::admin.dictionary_updates.title')">
    <p class="ss-notice"><x-admiro::icon name="info" /> {{ text('admiro::admin.dictionary_updates.lead') }}</p>
    @error('dictionary_update') <p class="ss-notice" role="alert"><x-admiro::icon name="alert-triangle" /> {{ $message }}</p> @enderror

    <section class="ss-card ss-card--flush">
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th scope="col">{{ $column('name') }}</th>
                        <th scope="col">{{ $column('zone') }}</th>
                        <th scope="col">{{ $column('organization_id') }}</th>
                        <th scope="col">{{ $column('created_at') }}</th>
                        <th scope="col">{{ $column('status') }}</th>
                        <th scope="col">{{ $column('result') }}</th>
                        @if ($canManage)
                            <th scope="col"><span class="ss-sr-only">{{ text('admiro::admin.account_actions') }}</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                        <tr>
                            <th scope="row">{{ $request->name }}</th>
                            <td><span class="ss-badge">{{ $request->zone }}</span></td>
                            <td>{{ $organizations[$request->organization_id] ?? '—' }}</td>
                            <td class="ss-table__number">{{ $request->created_at !== null ? local_date($request->created_at) : '—' }}</td>
                            <td><span class="ss-badge ss-badge--outline ss-badge--{{ $request->status->tone() }}">{{ $request->status->label() }}</span></td>
                            <td>{{ $request->result ?? '—' }}</td>
                            @if ($canManage)
                                <td class="ss-table__actions">
                                    @if ($request->status === \Simplysmart\Admiro\Enums\DictionaryUpdateStatus::Pending)
                                        <form method="POST" action="{{ route('admiro.dictionary-updates.approve', ['dictionaryRequest' => $request->token]) }}">
                                            @csrf
                                            <button class="ss-button ss-button--sm ss-button--primary" type="submit"><x-admiro::icon name="check" class="ss-button__icon" />{{ text('admiro::admin.dictionary_updates.approve') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('admiro.dictionary-updates.reject', ['dictionaryRequest' => $request->token]) }}">
                                            @csrf
                                            <button class="ss-button ss-button--sm" type="submit"><x-admiro::icon name="close" class="ss-button__icon" />{{ text('admiro::admin.dictionary_updates.reject') }}</button>
                                        </form>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $canManage ? 7 : 6 }}" class="ss-muted">{{ text('admiro::admin.dictionary_updates.empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="ss-card">
        <h2 class="ss-card__title">{{ text('admiro::admin.dictionary_updates.registered') }}</h2>
        @if ($dictionaries === [])
            <p class="ss-muted">{{ text('admiro::admin.dictionary_updates.none_registered') }}</p>
        @else
            <ul class="ss-record-rows">
                @foreach ($dictionaries as $dictionary)
                    <li class="ss-record-row"><span class="ss-record-row__main"><x-admiro::icon name="dictionaries" class="ss-icon--tile" /><span class="ss-record-row__text"><span class="ss-record-row__name">{{ $dictionary->label() }}</span><span class="ss-record-row__sub">{{ $dictionary->key() }}</span></span></span></li>
                @endforeach
            </ul>
        @endif
    </section>
</x-admiro::zone.layout>

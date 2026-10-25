{{-- Konta strefy: wyszukiwanie po adresie e-mail, tabela kont (awatar, nazwa, adres, stan jako plakietka, ostatnie logowanie) z akcjami administratora (accounts.manage) i stronicowanie. $managedZone: ZoneDefinition, $accounts: LengthAwarePaginator kont (CodeAndPinAccount), $email: string (szukany adres), $actions: list<Simplysmart\Admiro\Panel\Http\AccountAction> (puste bez accounts.manage). --}}
<x-admiro::zone.layout :title="text('admiro::admin.accounts_title', ['zone' => $managedZone->name])" :back="route('admiro.zones.index')">
    <form class="ss-card ss-form-row" method="GET" action="{{ route('admiro.zones.accounts', ['managedZone' => $managedZone->name]) }}" role="search">
        <x-admiro::input id="email" name="email" type="email" field-class="ss-form-row__grow" :label="text('admiro::admin.search_email')" icon="search" :value="$email" :placeholder="text('placeholders.email')" />
        <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="search" class="ss-button__icon" />{{ text_action('search') }}</button>
    </form>

    @error('account') <p class="ss-notice" role="alert"><x-admiro::icon name="alert-triangle" /> {{ $message }}</p> @enderror

    <section class="ss-card ss-card--flush">
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th scope="col">{{ text('admiro::admin.account_name') }}</th>
                        <th scope="col">{{ text('admiro::admin.account_state') }}</th>
                        <th scope="col">{{ text('admiro::admin.account_last_login') }}</th>
                        @if ($actions !== [])
                            <th scope="col"><span class="ss-sr-only">{{ text('admiro::admin.account_actions') }}</span></th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accounts as $managedAccount)
                        <tr>
                            <th scope="row">
                                <span class="ss-person">
                                    <x-admiro::avatar :name="$managedAccount->displayName()" size="sm" />
                                    <span class="ss-person__text"><span>{{ $managedAccount->displayName() }}</span><span class="ss-muted">{{ $managedAccount->emailAddress() }}</span></span>
                                </span>
                            </th>
                            <td>
                                <span class="ss-badge">
                                    @if (! $managedAccount->getAttribute('is_active'))
                                        {{ text('admiro::admin.state_inactive') }}
                                    @elseif ($managedAccount->isLocked())
                                        {{ text('admiro::admin.state_locked') }}
                                    @elseif (! $managedAccount->hasPin())
                                        {{ text('admiro::admin.state_no_pin') }}
                                    @else
                                        {{ text('admiro::admin.state_active') }}
                                    @endif
                                </span>
                            </td>
                            <td class="ss-table__number">{{ $managedAccount->getAttribute('last_login_at') ? local_date($managedAccount->getAttribute('last_login_at')) : '—' }}</td>
                            @if ($actions !== [])
                                <td>
                                    <div class="ss-context__actions">
                                        @foreach ($actions as $action)
                                            <form method="POST" action="{{ route('admiro.zones.accounts.action', ['managedZone' => $managedZone->name, 'account' => $managedAccount->getRouteKey(), 'action' => $action->value]) }}">
                                                @csrf
                                                <button class="ss-button" type="submit">{{ text('admiro::admin.action.' . $action->value) }}</button>
                                            </form>
                                        @endforeach
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="4" class="ss-muted">{{ text('admiro::admin.no_accounts') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <x-admiro::pagination :paginator="$accounts" />
</x-admiro::zone.layout>

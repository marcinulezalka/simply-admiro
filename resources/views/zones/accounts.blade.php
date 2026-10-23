{{-- Konta strefy. $managedZone: ZoneDefinition, $accounts: LengthAwarePaginator kont (CodeAndPinAccount), $email: string (szukany adres), $actions: list<Simplysmart\Admiro\Http\AccountAction> (puste bez accounts.manage). --}}
<x-mainboard::zone.layout :title="text('admiro::admin.accounts_title', ['zone' => $managedZone->name])">
    <form method="GET" action="{{ route('admiro.zones.accounts', ['managedZone' => $managedZone->name]) }}">
        <label for="email">{{ text('admiro::admin.search_email') }}</label>
        <input id="email" name="email" type="email" value="{{ $email }}">
        <button type="submit">{{ text_action('search') }}</button>
    </form>

    @error('account') <p role="alert">{{ $message }}</p> @enderror

    <table>
        <thead>
            <tr>
                <th scope="col">{{ text('admiro::admin.account_name') }}</th>
                <th scope="col">{{ text('admiro::admin.account_email') }}</th>
                <th scope="col">{{ text('admiro::admin.account_state') }}</th>
                <th scope="col">{{ text('admiro::admin.account_last_login') }}</th>
                @if ($actions !== [])
                    <th scope="col">{{ text('admiro::admin.account_actions') }}</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse ($accounts as $managedAccount)
                <tr>
                    <td>{{ $managedAccount->displayName() }}</td>
                    <td>{{ $managedAccount->emailAddress() }}</td>
                    <td>
                        @if (! $managedAccount->getAttribute('is_active'))
                            {{ text('admiro::admin.state_inactive') }}
                        @elseif ($managedAccount->isLocked())
                            {{ text('admiro::admin.state_locked') }}
                        @elseif (! $managedAccount->hasPin())
                            {{ text('admiro::admin.state_no_pin') }}
                        @else
                            {{ text('admiro::admin.state_active') }}
                        @endif
                    </td>
                    <td>{{ $managedAccount->getAttribute('last_login_at') ? local_date($managedAccount->getAttribute('last_login_at')) : '—' }}</td>
                    @if ($actions !== [])
                        <td>
                            @foreach ($actions as $action)
                                <form method="POST" action="{{ route('admiro.zones.accounts.action', ['managedZone' => $managedZone->name, 'account' => $managedAccount->getRouteKey(), 'action' => $action->value]) }}">
                                    @csrf
                                    <button type="submit">{{ text('admiro::admin.action.' . $action->value) }}</button>
                                </form>
                            @endforeach
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ $actions !== [] ? 5 : 4 }}">{{ text('admiro::admin.no_accounts') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $accounts->links() }}
</x-mainboard::zone.layout>

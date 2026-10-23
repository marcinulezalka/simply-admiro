{{-- Dziennik zdarzeń. $entries: LengthAwarePaginator<AuditEvent>, $events: list<string> (nazwy zdarzeń do filtra), $zones: list<ZoneDefinition>, $filters: array{zone: ?string, event: ?string, from: ?string, to: ?string}. --}}
<x-mainboard::zone.layout :title="text('admiro::admin.audit_title')">
    <form method="GET" action="{{ route('admiro.audit.index') }}">
        <label for="zone">{{ text_data(\Simplysmart\Mainboard\Models\AuditEvent::class, 'zone') }}</label>
        <select id="zone" name="zone">
            <option value="">—</option>
            @foreach ($zones as $auditZone)
                <option value="{{ $auditZone->name }}" @selected($filters['zone'] === $auditZone->name)>{{ $auditZone->name }}</option>
            @endforeach
        </select>

        <label for="event">{{ text_data(\Simplysmart\Mainboard\Models\AuditEvent::class, 'event') }}</label>
        <select id="event" name="event">
            <option value="">—</option>
            @foreach ($events as $event)
                <option value="{{ $event }}" @selected($filters['event'] === $event)>{{ $event }}</option>
            @endforeach
        </select>

        <label for="from">{{ text('admiro::admin.audit_from') }}</label>
        <input id="from" name="from" type="date" value="{{ $filters['from'] }}">
        <label for="to">{{ text('admiro::admin.audit_to') }}</label>
        <input id="to" name="to" type="date" value="{{ $filters['to'] }}">

        <button type="submit">{{ text_action('filter') }}</button>
    </form>

    <table>
        <thead>
            <tr>
                @foreach (['occurred_at', 'event', 'zone', 'account_id', 'actor_id', 'organization_id', 'ip', 'context'] as $column)
                    <th scope="col">{{ text_data(\Simplysmart\Mainboard\Models\AuditEvent::class, $column) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($entries as $entry)
                <tr>
                    <td>{{ local_date($entry->occurred_at) }} {{ $entry->occurred_at->format('H:i:s') }}</td>
                    <td>{{ $entry->event }}</td>
                    <td>{{ $entry->zone ?? '—' }}</td>
                    <td>{{ $entry->account_id ?? '—' }}</td>
                    <td>{{ $entry->actor_id === null ? '—' : $entry->actor_zone . ':' . $entry->actor_id }}</td>
                    <td>{{ $entry->organization_id ?? '—' }}</td>
                    <td>{{ $entry->ip ?? '—' }}</td>
                    <td>{{ $entry->context === null ? '—' : json_encode($entry->context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</td>
                </tr>
            @empty
                <tr><td colspan="8">{{ text('admiro::admin.audit_empty') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    {{ $entries->links() }}
</x-mainboard::zone.layout>

{{-- Dziennik zdarzeń systemu: filtry (strefa, zdarzenie z czytelną nazwą, zakres dat) i tabela (kiedy, zdarzenie, strefa, konto, kto, organizacja, IP, kontekst) ze stronicowaniem. $entries: LengthAwarePaginator<AuditEvent>, $events: list<string> (nazwy zdarzeń do filtra), $zones: list<ZoneDefinition>, $filters: array{zone: ?string, event: ?string, from: ?string, to: ?string}. --}}
@php
    use Simplysmart\Admiro\View\AuditEventLabel;
    $byName = collect($zones)->keyBy('name');
    $column = static fn (string $name): string => text_data(\Simplysmart\Admiro\Models\AuditEvent::class, $name);
@endphp
<x-admiro::zone.layout :title="text('admiro::admin.audit_title')">
    <form class="ss-card ss-form-row" method="GET" action="{{ route('admiro.audit.index') }}">
        <div class="ss-field">
            <label class="ss-field__label" for="zone">{{ $column('zone') }}</label>
            <select class="ss-input" id="zone" name="zone">
                <option value="">—</option>
                @foreach ($zones as $auditZone)
                    <option value="{{ $auditZone->name }}" @selected($filters['zone'] === $auditZone->name)>{{ $auditZone->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ss-field ss-form-row__grow">
            <label class="ss-field__label" for="event">{{ $column('event') }}</label>
            <select class="ss-input" id="event" name="event">
                <option value="">{{ text('audit.all_events') }}</option>
                @foreach ($events as $event)
                    <option value="{{ $event }}" @selected($filters['event'] === $event)>{{ AuditEventLabel::of($event) }}</option>
                @endforeach
            </select>
        </div>
        <x-admiro::input id="from" name="from" type="date" :label="text('admiro::admin.audit_from')" :value="$filters['from']" />
        <x-admiro::input id="to" name="to" type="date" :label="text('admiro::admin.audit_to')" :value="$filters['to']" />
        <button class="ss-button" type="submit">{{ text_action('filter') }}</button>
    </form>

    <section class="ss-card ss-card--flush">
        <div class="ss-table-wrap">
            <table class="ss-table">
                <thead>
                    <tr>
                        @foreach (['occurred_at', 'event', 'zone', 'account_id', 'actor_id', 'organization_id', 'ip', 'context'] as $name)
                            <th scope="col">{{ $column($name) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr>
                            <td class="ss-table__number">{{ local_date($entry->occurred_at) }} {{ $entry->occurred_at->format('H:i:s') }}</td>
                            <td><span class="ss-badge">{{ AuditEventLabel::of($entry->event, $entry->zone !== null ? $byName->get($entry->zone) : null) }}</span></td>
                            <td>{{ $entry->zone ?? '—' }}</td>
                            <td>{{ $entry->account_id ?? '—' }}</td>
                            <td>{{ $entry->actor_id === null ? '—' : $entry->actor_zone . ':' . $entry->actor_id }}</td>
                            <td>{{ $entry->organization_id ?? '—' }}</td>
                            <td class="ss-muted">{{ $entry->ip ?? '—' }}</td>
                            <td class="ss-muted">{{ $entry->context === null ? '—' : json_encode($entry->context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="ss-muted">{{ text('admiro::admin.audit_empty') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <x-admiro::pagination :paginator="$entries" />
</x-admiro::zone.layout>

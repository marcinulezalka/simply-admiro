{{-- Bezpieczeństwo zalogowanego konta (SecurityController): zmiana PIN-u (#pin: kod z e-maila, obecny i nowy PIN w zakresie strefy; pola PIN-u z klawiaturą ekranową x-admiro::pin-field), zapasowy adres e-mail do resetu PIN-u (#recovery: kod na nowy adres, potwierdzenie, usunięcie; $recoveryEmail, $pendingRecoveryEmail: ?string), zaufane urządzenia (#devices, gdy strefa je ma) z odwołaniem, wylogowanie ze wszystkich urządzeń i ostatnie zdarzenia konta (Queries\Audit\AccountHistory, bez adresów IP). $currentPinLength, $pinLength: Auth\PinLength; $codeLength: int; $devices: ?list<Auth\Devices\TrustedDeviceRecord> (null = strefa bez zaufanych urządzeń); $currentSelector: ?string; $history: Collection<int, Models\AuditEvent>; $zone daje Core. --}}
@php
    $routes = \Simplysmart\Admiro\Http\AccountRoutes::class;
    $codeField = static fn (string $id, string $name, string $label, string $type, string $autocomplete, int $min, int $max, bool $keypad = true) => compact('id', 'name', 'label', 'type', 'autocomplete', 'min', 'max', 'keypad');
    $pinFields = [
        $codeField('security-current-pin', 'current_pin', text('account.current_pin'), 'password', 'current-password', $currentPinLength->min, $currentPinLength->max),
        $codeField('security-code', 'code', text('sign_in.code'), 'text', 'one-time-code', $codeLength, $codeLength, false),
        $codeField('security-pin', 'pin', text('account.new_pin'), 'password', 'new-password', $pinLength->min, $pinLength->max),
        $codeField('security-pin-confirmation', 'pin_confirmation', text('sign_in.pin_confirmation'), 'password', 'new-password', $pinLength->min, $pinLength->max),
    ];
@endphp
<x-admiro::zone.layout :title="text('account.security_title')">
    <div class="ss-form-layout">
        <section class="ss-card" id="{{ $routes::SECTION_PIN }}" aria-labelledby="security-pin-title">
            <h2 class="ss-card__title" id="security-pin-title">{{ text('account.pin_title') }}</h2>
            <p class="ss-card__lead ss-muted">{{ text('account.pin_intro', ['min' => $pinLength->min, 'max' => $pinLength->max]) }}</p>
            <form method="POST" action="{{ route($zone->routeName($routes::PIN_CODE)) }}">
                @csrf
                <button class="ss-button" type="submit"><x-admiro::icon name="mail" class="ss-button__icon" />{{ text('account.send_code') }}</button>
            </form>
            <form class="ss-form ss-card__footer" method="POST" action="{{ route($zone->routeName($routes::PIN_UPDATE)) }}">
                @csrf
                @method('PUT')
                @foreach ($pinFields as $field)
                    <x-admiro::pin-field :id="$field['id']" :name="$field['name']" :label="$field['label']" :type="$field['type']" :autocomplete="$field['autocomplete']" :min="$field['min']" :max="$field['max']" :keypad="$field['keypad']" :error="$errors->first($field['name'])" />
                @endforeach
                <div class="ss-form-actions">
                    <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="lock" class="ss-button__icon" />{{ text_action('save') }}</button>
                </div>
            </form>
        </section>

        <div class="ss-grid">
            <section class="ss-card" id="{{ $routes::SECTION_RECOVERY }}" aria-labelledby="security-recovery-title">
                <h2 class="ss-card__title" id="security-recovery-title">{{ text('account.recovery.title') }}</h2>
                <p class="ss-card__lead ss-muted">{{ text('account.recovery.intro') }}</p>
                @if ($recoveryEmail !== null)
                    <div class="ss-list__item">
                        <span class="ss-list__main"><span>{{ $recoveryEmail }}</span><span class="ss-muted">{{ text('account.recovery.current') }}</span></span>
                        <form method="POST" action="{{ route($zone->routeName($routes::RECOVERY_EMAIL_DESTROY)) }}">
                            @csrf
                            @method('DELETE')
                            <button class="ss-button" type="submit"><x-admiro::icon name="trash" class="ss-button__icon" />{{ text_action('delete') }}</button>
                        </form>
                    </div>
                @endif
                <form class="ss-form ss-card__footer" method="POST" action="{{ route($zone->routeName($routes::RECOVERY_EMAIL_CODE)) }}">
                    @csrf
                    <x-admiro::input id="security-recovery-email" name="recovery_email" type="email" :label="$recoveryEmail === null ? text('account.recovery.email') : text('account.recovery.new_email')" icon="mail" :value="old('recovery_email', $pendingRecoveryEmail)" :placeholder="text('placeholders.email')" :error="$errors->first('recovery_email')" required maxlength="255" autocomplete="email" />
                    <div class="ss-form-actions">
                        <button class="ss-button" type="submit"><x-admiro::icon name="mail" class="ss-button__icon" />{{ text('account.send_code') }}</button>
                    </div>
                </form>
                @if ($pendingRecoveryEmail !== null)
                    <form class="ss-form ss-card__footer" method="POST" action="{{ route($zone->routeName($routes::RECOVERY_EMAIL_UPDATE)) }}">
                        @csrf
                        @method('PUT')
                        <p class="ss-muted">{{ text('account.recovery.code_hint', ['email' => $pendingRecoveryEmail]) }}</p>
                        <x-admiro::pin-field id="security-recovery-code" name="recovery_code" :label="text('sign_in.code')" type="text" autocomplete="one-time-code" :min="$codeLength" :max="$codeLength" :keypad="false" :error="$errors->first('recovery_code')" />
                        <div class="ss-form-actions">
                            <button class="ss-button ss-button--primary" type="submit"><x-admiro::icon name="check" class="ss-button__icon" />{{ text('account.recovery.confirm') }}</button>
                        </div>
                    </form>
                @elseif ($errors->has('recovery_code'))
                    <p class="ss-notice" role="alert"><x-admiro::icon name="alert-triangle" /> {{ $errors->first('recovery_code') }}</p>
                @endif
            </section>

            @if ($devices !== null)
                <section class="ss-card" id="{{ $routes::SECTION_DEVICES }}" aria-labelledby="security-devices-title">
                    <h2 class="ss-card__title" id="security-devices-title">{{ text('account.devices_title') }}</h2>
                    <p class="ss-card__lead ss-muted">{{ text('account.devices_hint') }}</p>
                    @if ($devices === [])
                        <p class="ss-muted">{{ text('account.devices_empty') }}</p>
                    @else
                        <ul class="ss-list">
                            @foreach ($devices as $device)
                                <li class="ss-list__item">
                                    <span class="ss-list__main">
                                        <span>{{ $device->name }} @if ($device->selector === $currentSelector)<span class="ss-badge">{{ text('account.this_device') }}</span>@endif</span>
                                        <span class="ss-muted">{{ text('account.device_last_used') }}: {{ local_date($device->lastUsedAt ?? $device->createdAt, 'L LT') }} · {{ text('account.device_expires') }}: {{ local_date($device->expiresAt) }}</span>
                                    </span>
                                    <form method="POST" action="{{ route($zone->routeName($routes::DEVICES_REVOKE), ['device' => $device->token]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="ss-button" type="submit">{{ text('account.device_revoke') }}</button>
                                    </form>
                                </li>
                            @endforeach
                        </ul>
                        <form class="ss-card__footer" method="POST" action="{{ route($zone->routeName($routes::DEVICES_REVOKE_ALL)) }}">
                            @csrf
                            @method('DELETE')
                            <button class="ss-button" type="submit">{{ text('account.devices_revoke_all') }}</button>
                        </form>
                    @endif
                </section>
            @endif

            <section class="ss-card ss-card--inverse" aria-labelledby="security-everywhere-title">
                <h2 class="ss-card__title" id="security-everywhere-title">{{ text('account.sign_out_everywhere') }}</h2>
                <p class="ss-muted">{{ text('account.sign_out_everywhere_hint') }}</p>
                <form class="ss-card__footer" method="POST" action="{{ route($zone->routeName($routes::SIGN_OUT_EVERYWHERE)) }}">
                    @csrf
                    @method('DELETE')
                    <button class="ss-button ss-button--primary ss-button--pill" type="submit"><x-admiro::icon name="log-out" class="ss-button__icon" />{{ text('account.sign_out_everywhere') }}</button>
                </form>
            </section>
        </div>
    </div>

    <section class="ss-card ss-card--flush" aria-labelledby="security-history-title">
        <h2 class="ss-card__title ss-card__title--inset" id="security-history-title">{{ text('account.history.title') }}</h2>
        <p class="ss-card__lead ss-card__title--inset ss-muted">{{ text('account.history.lead') }}</p>
        @if ($history->isEmpty())
            <p class="ss-card__title--inset ss-muted">{{ text('account.history.empty') }}</p>
        @else
            <div class="ss-table-wrap">
                <table class="ss-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ text('account.history.when') }}</th>
                            <th scope="col">{{ text('account.history.event') }}</th>
                            <th scope="col">{{ text('account.history.detail') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $entry)
                            <tr>
                                <td class="ss-table__number">{{ local_date($entry->occurred_at, 'L LT') }}</td>
                                <td><span class="ss-badge">{{ \Simplysmart\Admiro\Queries\Audit\AccountHistory::label($entry) }}</span></td>
                                <td>{{ \Simplysmart\Admiro\Queries\Audit\AccountHistory::detail($entry) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-admiro::zone.layout>

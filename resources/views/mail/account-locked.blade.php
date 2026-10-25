{{-- E-mail tekstowy o blokadzie konta. $accountName: string, $lockedUntil: CarbonInterface. Teksty z kaskady strefy ($mailZone, ZoneMailable). --}}{{ text_email($mailZone->textKey('account_locked.greeting'), ['name' => $accountName]) }}

{{ text_email($mailZone->textKey('account_locked.body'), ['until' => local_date($lockedUntil) . ' ' . $lockedUntil->format('H:i')]) }}

{{ text_email($mailZone->textKey('account_locked.warning')) }}

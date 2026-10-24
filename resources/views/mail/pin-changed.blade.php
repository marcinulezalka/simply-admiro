{{-- E-mail tekstowy o zmianie PIN-u. $accountName: string, $changedAt: CarbonInterface. Teksty z kaskady strefy ($mailZone, ZoneMailable). --}}{{ text_email($mailZone->textKey('pin_changed.greeting'), ['name' => $accountName]) }}

{{ text_email($mailZone->textKey('pin_changed.body'), ['at' => local_date($changedAt) . ' ' . $changedAt->format('H:i')]) }}

{{ text_email($mailZone->textKey('pin_changed.warning')) }}

{{-- E-mail tekstowy o użyciu skopiowanego tokenu urządzenia. $accountName: string. Teksty z kaskady strefy ($mailZone, ZoneMailable). --}}{{ text_email($mailZone->textKey('device_token_reused.greeting'), ['name' => $accountName]) }}

{{ text_email($mailZone->textKey('device_token_reused.body')) }}

{{ text_email($mailZone->textKey('device_token_reused.warning')) }}

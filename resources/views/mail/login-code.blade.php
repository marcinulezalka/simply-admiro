{{-- E-mail tekstowy z kodem logowania. $accountName, $code: string, $validForMinutes: int. Teksty z kaskady strefy ($mailZone, ZoneMailable). --}}{{ text_email($mailZone->textKey('login_code.greeting'), ['name' => $accountName]) }}

{{ text_email($mailZone->textKey('login_code.body'), ['code' => $code, 'minutes' => $validForMinutes]) }}

{{ text_email($mailZone->textKey('login_code.ignore')) }}

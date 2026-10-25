{{-- E-mail tekstowy z kodem bezpieczeństwa (Mail\SecurityCodeMail). $accountName, $code: string, $validForMinutes: int, $purpose: Auth\Recovery\SecurityCodePurpose. Teksty z kaskady strefy ($mailZone, ZoneMailable). --}}{{ text_email($mailZone->textKey('login_code.greeting'), ['name' => $accountName]) }}

{{ text_email($mailZone->textKey('security_code.' . $purpose->value . '.code_label')) }} {{ $code }}

{{ text_email($mailZone->textKey('login_code.valid'), ['minutes' => $validForMinutes]) }}

{{ text_email($mailZone->textKey('security_code.' . $purpose->value . '.ignore')) }}

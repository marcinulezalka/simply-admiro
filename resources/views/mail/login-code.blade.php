{{-- E-mail tekstowy z kodem logowania. $accountName, $code: string, $validForMinutes: int. --}}{{ text_email('login_code.greeting', ['name' => $accountName]) }}

{{ text_email('login_code.body', ['code' => $code, 'minutes' => $validForMinutes]) }}

{{ text_email('login_code.ignore') }}

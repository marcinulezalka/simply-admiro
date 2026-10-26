{{-- E-mail HTML z kodem logowania strefy admiro (ZoneMailable): powitanie, etykieta, kod w osobnych polach (admiro::mail.admiro.partials.code), ważność i ostrzeżenie w szarej ramce. $accountName, $code: string, $validForMinutes: int. --}}
@extends($mailLayout)

@section('content')
    <p style="margin: 0 0 20px; font-size: 18px; font-weight: 600;">{{ text_email($mailZone->textKey('login_code.greeting'), ['name' => $accountName]) }}</p>
    <p style="margin: 0 0 12px;">{{ text_email($mailZone->textKey('login_code.code_label')) }}</p>
    @include('admiro::mail.admiro.partials.code')
    <p style="margin: 0 0 24px; color: {{ $mailPalette->muted }}; font-size: 13px;">{{ text_email($mailZone->textKey('login_code.valid'), ['minutes' => $validForMinutes]) }}</p>
    <p style="margin: 0; padding: 12px 16px; border-left: 3px solid {{ $mailPalette->accent }}; background-color: {{ $mailPalette->bg }}; color: {{ $mailPalette->muted }}; font-size: 13px;">{{ text_email($mailZone->textKey('login_code.ignore')) }}</p>
@endsection

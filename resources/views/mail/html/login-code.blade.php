{{-- E-mail HTML z kodem logowania (ZoneMailable). $accountName, $code: string, $validForMinutes: int. --}}
@extends($mailLayout)

@section('content')
    <p style="margin: 0 0 16px;">{{ text_email($mailZone->textKey('login_code.greeting'), ['name' => $accountName]) }}</p>
    <p style="margin: 0 0 8px;">{{ text_email($mailZone->textKey('login_code.code_label')) }}</p>
    <p style="margin: 0 0 8px; font-size: 32px; font-weight: 700; letter-spacing: 6px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;">{{ $code }}</p>
    <p style="margin: 0 0 24px; color: {{ $mailPalette->muted }};">{{ text_email($mailZone->textKey('login_code.valid'), ['minutes' => $validForMinutes]) }}</p>
    <p style="margin: 0; color: {{ $mailPalette->muted }};">{{ text_email($mailZone->textKey('login_code.ignore')) }}</p>
@endsection

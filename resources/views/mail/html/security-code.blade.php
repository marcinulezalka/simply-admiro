{{-- E-mail HTML z kodem bezpieczeństwa (Mail\SecurityCodeMail): potwierdzenie zapasowego adresu albo reset PIN-u, teksty security_code.<cel>.*. $accountName, $code: string, $validForMinutes: int, $purpose: Auth\Recovery\SecurityCodePurpose. --}}
@extends($mailLayout)

@section('content')
    @php($key = static fn (string $text): string => $mailZone->textKey('security_code.' . $purpose->value . '.' . $text))
    <p style="margin: 0 0 16px;">{{ text_email($mailZone->textKey('login_code.greeting'), ['name' => $accountName]) }}</p>
    <p style="margin: 0 0 8px;">{{ text_email($key('code_label')) }}</p>
    <p style="margin: 0 0 8px; font-size: 32px; font-weight: 700; letter-spacing: 6px; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;">{{ $code }}</p>
    <p style="margin: 0 0 24px; color: {{ $mailPalette->muted }};">{{ text_email($mailZone->textKey('login_code.valid'), ['minutes' => $validForMinutes]) }}</p>
    <p style="margin: 0; color: {{ $mailPalette->muted }};">{{ text_email($key('ignore')) }}</p>
@endsection

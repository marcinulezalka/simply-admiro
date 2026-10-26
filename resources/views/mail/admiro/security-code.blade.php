{{-- E-mail HTML z kodem bezpieczeństwa strefy admiro (Mail\SecurityCodeMail): potwierdzenie zapasowego adresu albo reset PIN-u, teksty security_code.<cel>.*, kod w osobnych polach (admiro::mail.admiro.partials.code). $accountName, $code: string, $validForMinutes: int, $purpose: Auth\Recovery\SecurityCodePurpose. --}}
@extends($mailLayout)

@section('content')
    @php($key = static fn (string $text): string => $mailZone->textKey('security_code.' . $purpose->value . '.' . $text))
    <p style="margin: 0 0 20px; font-size: 18px; font-weight: 600;">{{ text_email($mailZone->textKey('login_code.greeting'), ['name' => $accountName]) }}</p>
    <p style="margin: 0 0 12px;">{{ text_email($key('code_label')) }}</p>
    @include('admiro::mail.admiro.partials.code')
    <p style="margin: 0 0 24px; color: {{ $mailPalette->muted }}; font-size: 13px;">{{ text_email($mailZone->textKey('login_code.valid'), ['minutes' => $validForMinutes]) }}</p>
    <p style="margin: 0; padding: 12px 16px; border-left: 3px solid {{ $mailPalette->accent }}; background-color: {{ $mailPalette->bg }}; color: {{ $mailPalette->muted }}; font-size: 13px;">{{ text_email($key('ignore')) }}</p>
@endsection

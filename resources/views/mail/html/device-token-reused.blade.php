{{-- E-mail HTML o użyciu skopiowanego tokenu urządzenia (ZoneMailable). $accountName: string. --}}
@extends($mailLayout)

@section('content')
    <p style="margin: 0 0 16px;">{{ text_email($mailZone->textKey('device_token_reused.greeting'), ['name' => $accountName]) }}</p>
    <p style="margin: 0 0 16px;">{{ text_email($mailZone->textKey('device_token_reused.body')) }}</p>
    <p style="margin: 0; color: {{ $mailPalette->muted }};">{{ text_email($mailZone->textKey('device_token_reused.warning')) }}</p>
@endsection

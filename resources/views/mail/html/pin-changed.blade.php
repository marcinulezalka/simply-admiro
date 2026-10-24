{{-- E-mail HTML o zmianie PIN-u (ZoneMailable). $accountName: string, $changedAt: CarbonInterface. --}}
@extends($mailLayout)

@section('content')
    <p style="margin: 0 0 16px;">{{ text_email($mailZone->textKey('pin_changed.greeting'), ['name' => $accountName]) }}</p>
    <p style="margin: 0 0 16px;">{{ text_email($mailZone->textKey('pin_changed.body'), ['at' => local_date($changedAt) . ' ' . $changedAt->format('H:i')]) }}</p>
    <p style="margin: 0; color: {{ $mailPalette->muted }};">{{ text_email($mailZone->textKey('pin_changed.warning')) }}</p>
@endsection

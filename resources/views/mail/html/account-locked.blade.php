{{-- E-mail HTML o blokadzie konta (ZoneMailable). $accountName: string, $lockedUntil: CarbonInterface. --}}
@extends($mailLayout)

@section('content')
    <p style="margin: 0 0 16px;">{{ text_email($mailZone->textKey('account_locked.greeting'), ['name' => $accountName]) }}</p>
    <p style="margin: 0 0 16px;">{{ text_email($mailZone->textKey('account_locked.body'), ['until' => local_date($lockedUntil) . ' ' . $lockedUntil->format('H:i')]) }}</p>
    <p style="margin: 0; color: {{ $mailPalette->muted }};">{{ text_email($mailZone->textKey('account_locked.warning')) }}</p>
@endsection

{{-- E-mail HTML o nowym zaufanym urządzeniu (ZoneMailable). $accountName, $deviceName: string, $registeredAt: CarbonInterface, $devicesUrl: string. --}}
@extends($mailLayout)

@section('content')
    <p style="margin: 0 0 16px;">{{ text_email($mailZone->textKey('new_trusted_device.greeting'), ['name' => $accountName]) }}</p>
    <p style="margin: 0 0 16px;">{{ text_email($mailZone->textKey('new_trusted_device.body'), ['device' => $deviceName, 'at' => local_date($registeredAt) . ' ' . $registeredAt->format('H:i')]) }}</p>
    <p style="margin: 0 0 24px; color: {{ $mailPalette->muted }};">{{ text_email($mailZone->textKey('new_trusted_device.manage_html')) }}</p>
    <table role="presentation" cellpadding="0" cellspacing="0"><tr><td style="border-radius: 8px; background-color: {{ $mailPalette->accent }};">
        <a href="{{ $devicesUrl }}" style="display: inline-block; padding: 12px 20px; color: {{ $mailPalette->onAccent }}; font-weight: 600; text-decoration: none;">{{ text_email($mailZone->textKey('new_trusted_device.manage_action')) }}</a>
    </td></tr></table>
@endsection

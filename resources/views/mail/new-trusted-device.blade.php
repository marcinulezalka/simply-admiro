{{-- E-mail tekstowy o nowym zaufanym urządzeniu. $accountName, $deviceName: string, $registeredAt: CarbonInterface, $devicesUrl: string. Teksty z kaskady strefy ($mailZone, ZoneMailable). --}}{{ text_email($mailZone->textKey('new_trusted_device.greeting'), ['name' => $accountName]) }}

{{ text_email($mailZone->textKey('new_trusted_device.body'), ['device' => $deviceName, 'at' => local_date($registeredAt) . ' ' . $registeredAt->format('H:i')]) }}

{{ text_email($mailZone->textKey('new_trusted_device.manage'), ['url' => $devicesUrl]) }}

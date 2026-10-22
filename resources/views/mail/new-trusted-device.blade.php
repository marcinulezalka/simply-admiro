{{ text_email('new_trusted_device.greeting', ['name' => $accountName]) }}

{{ text_email('new_trusted_device.body', ['device' => $deviceName, 'at' => local_date($registeredAt) . ' ' . $registeredAt->format('H:i')]) }}

{{ text_email('new_trusted_device.manage', ['url' => $devicesUrl]) }}

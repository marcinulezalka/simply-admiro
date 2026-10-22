{{ text_email('pin_changed.greeting', ['name' => $accountName]) }}

{{ text_email('pin_changed.body', ['at' => local_date($changedAt) . ' ' . $changedAt->format('H:i')]) }}

{{ text_email('pin_changed.warning') }}

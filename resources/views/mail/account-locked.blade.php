{{ text_email('account_locked.greeting', ['name' => $accountName]) }}

{{ text_email('account_locked.body', ['until' => local_date($lockedUntil) . ' ' . $lockedUntil->format('H:i')]) }}

{{ text_email('account_locked.warning') }}

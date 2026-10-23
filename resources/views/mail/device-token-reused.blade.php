{{-- E-mail tekstowy o użyciu skopiowanego tokenu urządzenia. $accountName: string. --}}{{ text_email('device_token_reused.greeting', ['name' => $accountName]) }}

{{ text_email('device_token_reused.body') }}

{{ text_email('device_token_reused.warning') }}

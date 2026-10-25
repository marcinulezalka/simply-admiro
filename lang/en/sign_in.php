<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/mainboard.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

return [

    'title' => 'Sign in',
    'page_title' => '',
    'lead' => '',
    'note' => '',
    'email_placeholder' => '',
    'submit' => 'Continue',
    'pin_lead' => '',
    'pin_note' => '',
    'pin_setup_note' => '',
    'pin_setup_submit' => 'Continue',
    'code_note' => '',
    'email' => 'E-mail address',
    'continue' => 'Continue',
    'code_title' => 'Enter the code from the e-mail',
    'code_intro' => 'If an account with the address :email exists, we have sent it a :length-digit code. It is valid for :minutes min.',
    'code' => 'Code',
    'resend' => 'Send the code again',
    'resend_wait' => 'You can send a new code in :seconds s.',
    'code_resent' => 'If the account exists, we have sent a new code.',
    'change_email' => 'Change address',
    'pin_title' => 'Enter your PIN',
    'pin' => 'PIN',
    'pin_setup_title' => 'Set your PIN',
    'pin_setup_intro' => 'The PIN has :min to :max digits. Avoid repeated and consecutive digits.',
    'pin_confirmation' => 'Repeat the PIN',
    'sign_out' => 'Sign out',
    'signed_in_as' => 'Signed in as :name.',
    'failure' => 'Could not sign in. Check your details and try again.',
    'expired' => 'The sign-in session has expired. Start again.',
    'page_expired' => 'The page has expired. Sign in again.',
    'too_many_attempts' => 'Too many attempts. Try again in :seconds s.',
    'weak_pin' => 'This PIN is too simple — avoid repeated and consecutive digits.',
    'remember_device' => 'Remember this device for :days days (no code next time, PIN still required)',
    'honeypot_field' => 'Website',
    'forgot_pin_link' => 'I forgot my PIN',
    'signed_out' => 'Signed out. See you soon!',

    'forgot_pin' => [
        'title' => 'Forgot your PIN?',
        'lead' => 'A PIN cannot be recovered — nobody knows it, not even the administrator. It can only be set again.',
        'steps_title' => 'Without a recovery e-mail',
        'recovery' => [
            'title' => 'Do you have a recovery e-mail?',
            'lead' => 'Enter your account address. If the account has a confirmed recovery address, we will send a code there to remove your PIN. You set a new PIN when you sign in with the code sent to your account address.',
            'send' => 'Send code to the recovery address',
            'code_sent' => 'If the account has a recovery e-mail, we have sent a code to it.',
            'code_lead' => 'Enter the code from the message sent to your recovery address. The reset signs you out on all devices.',
            'reset' => 'Remove PIN',
            'other_address' => 'Use another address',
            'done' => 'PIN removed. Sign in with your account e-mail and set a new PIN.',
        ],
        'steps' => [
            'admin' => 'Ask the administrator of this part of the application to reset your PIN.',
            'revoked' => 'The reset signs you out on all devices and revokes trusted devices.',
            'sign_in' => 'Sign in with your e-mail address and the code we send you.',
            'set' => 'Set a new PIN and repeat it to confirm.',
        ],
        'back' => 'Back to sign-in',
    ],

    'admiro' => [
        'title' => 'Control, automation and *precision* in every deployment',
        'page_title' => 'admiro',
    ],

];

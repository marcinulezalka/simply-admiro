<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/admiro.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

return [

    'code' => 'Error :code',
    'home' => 'Home page',
    'back' => 'Go back',
    'retry' => 'Try again',

    '401' => [
        'title' => 'Sign in to continue',
        'message' => 'This page requires signing in to the right part of the application.',
    ],
    '403' => [
        'title' => 'No access',
        'message' => 'Your account has no permission for this page. If this is a mistake, ask the administrator for access.',
    ],
    '404' => [
        'title' => 'Page not found',
        'message' => 'The address may have changed or the page was removed. Check the address or go back to the home page.',
    ],
    '419' => [
        'title' => 'Page expired',
        'message' => 'The form was open for too long. Go back, refresh the page and send it again.',
    ],
    '429' => [
        'title' => 'Too many requests',
        'message' => 'Too many requests were sent in a short time. Wait a moment and try again.',
    ],
    '500' => [
        'title' => 'Something went wrong',
        'message' => 'An unexpected error occurred. We have recorded it and are working on it. Please try again shortly.',
    ],
    '503' => [
        'title' => 'Maintenance in progress',
        'message' => 'The application is temporarily unavailable. Please come back in a few minutes.',
    ],

];

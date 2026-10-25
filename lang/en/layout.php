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

    'menu' => 'Application menu',
    'toggle_menu' => 'Collapse or expand the menu',
    'search' => 'Search the application',
    'search_shortcut' => 'Press / to search',
    'account' => 'Account: :name',
    'notifications' => 'Notifications',
    'notifications_unread' => ':count unread',
    'notifications_empty' => 'No notifications.',
    'notifications_all' => 'All notifications',
    'mark_all_read' => 'Mark all as read',
    'unread' => 'unread',
    'close' => 'Close',
    'toasts' => 'Application messages',
    'keypad' => [
        'open' => 'Type on the on-screen keypad',
        'digits' => 'Digits',
        'count' => 'Digits entered: :count',
        'clear' => 'Clear',
        'backspace' => 'Delete the last digit',
        'done' => 'Done',
    ],
    'settings' => 'Settings',
    'back_to_app' => 'Back to the application',

    'toast' => [
        'success' => 'Done',
        'info' => 'Information',
        'warning' => 'Warning',
        'danger' => 'Error',
    ],
    'mask_format' => 'Format: :format',
    'switch_context' => 'Switch context',
    'current' => 'current',
    'theme' => 'Theme',
    'legal' => 'Legal information',
    'footer_toggle' => 'simplysmart: legal information and cookies',
    'copyright' => '© :year simplysmart. All rights reserved.',
    'themes' => [
        'light' => 'Light',
        'dark' => 'Dark',
        'contrast' => 'WCAG contrast',
        'contrast-yellow' => 'WCAG high contrast',
    ],
    'pagination' => [
        'label' => 'List pages',
        'previous' => 'Previous page',
        'next' => 'Next page',
        'page' => 'Page :page of :pages',
        'total' => 'items: :count',
    ],
    'signature' => [
        'hint' => 'Sign with a mouse, stylus or finger inside the frame.',
        'noscript' => 'Signing requires JavaScript.',
        'clear' => 'Clear the signature',
    ],
    'steps' => [
        'done' => 'completed',
        'current' => 'current',
        'next' => 'upcoming',
    ],
    'content' => [
        'toc' => 'Contents',
        'see_also' => 'See also',
        'back' => 'Back',
    ],
    'palette' => 'Color scheme',
    'palettes' => [
        'default' => 'Default',
        'teal' => 'Teal',
        'indigo' => 'Indigo',
        'blue' => 'Blue',
        'emerald' => 'Emerald',
        'violet' => 'Violet',
    ],

    'organization_form' => [
        'details' => 'Organization details',
        'contact' => 'Contact details',
        'address' => 'Address',
        'short_name_hint' => 'A short name shown under the person’s name in the application menu. Without it the full name is shown.',
        'billing_email_hint' => 'Invoices and billing go to this address. Left empty when creating — the organization e-mail is used.',
    ],

];

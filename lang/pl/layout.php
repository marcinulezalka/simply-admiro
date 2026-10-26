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

    'menu' => 'Menu aplikacji',
    'toggle_menu' => 'Zwiń lub rozwiń menu',
    'search' => 'Szukaj w aplikacji',
    'search_shortcut' => 'Naciśnij /, aby szukać',
    'account' => 'Konto: :name',
    'notifications' => 'Powiadomienia',
    'notifications_unread' => ':count nieprzeczytane',
    'notifications_empty' => 'Brak powiadomień.',
    'notifications_all' => 'Wszystkie powiadomienia',
    'mark_all_read' => 'Oznacz wszystkie jako przeczytane',
    'unread' => 'nieprzeczytane',
    'close' => 'Zamknij',
    'home' => 'Strona główna',
    'code_digit' => 'Cyfra :position z :length',
    'toasts' => 'Komunikaty aplikacji',
    'keypad' => [
        'open' => 'Wpisz na klawiaturze ekranowej',
        'digits' => 'Cyfry',
        'count' => 'Wpisane cyfry: :count',
        'clear' => 'Wyczyść',
        'backspace' => 'Usuń ostatnią cyfrę',
        'done' => 'Zatwierdź PIN',
    ],
    'settings' => 'Ustawienia',
    'back_to_app' => 'Wróć do aplikacji',

    'toast' => [
        'success' => 'Gotowe',
        'info' => 'Informacja',
        'warning' => 'Uwaga',
        'danger' => 'Błąd',
    ],
    'mask_format' => 'Format: :format',
    'switch_context' => 'Przełącz kontekst',
    'current' => 'bieżący',
    'theme' => 'Motyw',
    'legal' => 'Informacje prawne',
    'footer_toggle' => 'simplysmart: informacje prawne i cookies',
    'copyright' => '© :year simplysmart. Wszelkie prawa zastrzeżone.',
    'themes' => [
        'light' => 'Jasny',
        'dark' => 'Ciemny',
        'contrast' => 'Kontrast WCAG',
        'contrast-yellow' => 'Duży kontrast WCAG',
    ],
    'pagination' => [
        'label' => 'Strony listy',
        'previous' => 'Poprzednia strona',
        'next' => 'Następna strona',
        'page' => 'Strona :page z :pages',
        'total' => 'pozycji: :count',
    ],
    'signature' => [
        'hint' => 'Podpisz myszą, rysikiem albo palcem w ramce.',
        'noscript' => 'Podpis wymaga włączonego JavaScriptu.',
        'clear' => 'Wyczyść podpis',
    ],
    'steps' => [
        'done' => 'zakończony',
        'current' => 'bieżący',
        'next' => 'przyszły',
    ],
    'content' => [
        'toc' => 'Spis treści',
        'see_also' => 'Zobacz także',
        'back' => 'Wstecz',
    ],
    'palette' => 'Kolorystyka',
    'palettes' => [
        'default' => 'Domyślna',
        'teal' => 'Morska',
        'indigo' => 'Indygo',
        'blue' => 'Niebieska',
        'emerald' => 'Szmaragdowa',
        'violet' => 'Fioletowa',
    ],

    'organization_form' => [
        'details' => 'Dane organizacji',
        'contact' => 'Dane kontaktowe',
        'address' => 'Dane adresowe',
        'short_name_hint' => 'Krótka nazwa pod imieniem i nazwiskiem w menu aplikacji. Bez niej wyświetlana jest pełna nazwa.',
        'billing_email_hint' => 'Na ten adres trafiają faktury i rozliczenia. Pusty przy zakładaniu — użyjemy adresu e-mail organizacji.',
    ],

];

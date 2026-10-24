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

    'organization' => 'Organizacja',
    'role' => 'Rola',
    'settings_group' => 'Konto',
    'appearance_title' => 'Wygląd',
    'appearance_lead' => 'Motyw i kolorystyka aplikacji na tym urządzeniu.',
    'theme_title' => 'Motyw',
    'theme_hint' => 'Jasny, ciemny albo wysoki kontrast. Wybór zapamiętuje ta przeglądarka.',
    'palette_hint' => 'Kolor akcentu aplikacji. Wybór zapamiętuje ta przeglądarka.',
    'organization_switch' => 'Przełącz',
    'menu' => 'Konto',
    'pin_title' => 'Zmień PIN',
    'pin_intro' => 'Wyślij kod na swój adres e-mail, a potem wpisz obecny PIN, kod i nowy PIN (od :min do :max cyfr).',
    'send_code' => 'Wyślij kod',
    'code_sent' => 'Wysłaliśmy kod na adres konta.',
    'current_pin' => 'Obecny PIN',
    'new_pin' => 'Nowy PIN',
    'pin_invalid' => 'Obecny PIN jest nieprawidłowy.',
    'code_invalid' => 'Kod jest nieprawidłowy albo wygasł. Wyślij nowy.',
    'pin_same' => 'Nowy PIN musi różnić się od obecnego.',
    'pin_changed' => 'PIN zmieniony. Pozostałe sesje zostały wylogowane, a zapamiętane urządzenia odwołane.',
    'devices_title' => 'Zaufane urządzenia',
    'devices_empty' => 'Nie masz zapamiętanych urządzeń.',
    'device' => 'Urządzenie',
    'device_last_used' => 'Ostatnio użyte',
    'device_expires' => 'Ważne do',
    'this_device' => 'to urządzenie',
    'device_revoke' => 'Odwołaj',
    'devices_revoke_all' => 'Odwołaj wszystkie',
    'devices_revoked' => 'Urządzenie nie jest już zaufane: logowanie na nim znowu wymaga kodu z e-maila.',
    'profile_title' => 'Mój profil',
    'avatar_color' => 'Kolor awatara',
    'profile_saved' => 'Profil zapisany.',
    'profile_details' => 'Dane konta',
    'name' => 'Imię i nazwisko',
    'email' => 'Adres e-mail',
    'email_hint' => 'Adres e-mail służy do logowania — zmienia go administrator.',
    'language' => 'Język interfejsu',
    'language_auto' => 'Jak w adresie strony',
    'language_hint' => 'Po zapisaniu i przy każdym logowaniu aplikacja otworzy się w tym języku.',
    'organizations_title' => 'Moje organizacje',
    'organization_current' => 'bieżąca',
    'organization_no_roles' => 'bez ról',
    'security_title' => 'Bezpieczeństwo',
    'security_lead' => 'PIN, zapamiętane urządzenia i ostatnie zdarzenia Twojego konta.',
    'devices_hint' => 'Na zaufanym urządzeniu logujesz się samym PIN-em. Odwołaj urządzenie, którego już nie używasz.',
    'sign_out_everywhere' => 'Wyloguj ze wszystkich urządzeń',
    'sign_out_everywhere_hint' => 'Kończy wszystkie sesje tego konta, także tę, i odwołuje zaufane urządzenia. Przy następnym logowaniu potrzebny będzie kod z e-maila.',
    'signed_out_everywhere' => 'Wylogowano ze wszystkich urządzeń.',
    'history' => [
        'title' => 'Ostatnie zdarzenia konta',
        'lead' => 'Jeśli czegoś nie rozpoznajesz, zmień PIN i wyloguj się ze wszystkich urządzeń.',
        'empty' => 'Brak zdarzeń.',
        'when' => 'Kiedy',
        'event' => 'Zdarzenie',
        'detail' => 'Szczegóły',
        'devices_count' => 'Liczba urządzeń: :count',
    ],
    'avatar_colors' => [
        'slate' => 'Grafitowy',
        'red' => 'Czerwony',
        'amber' => 'Bursztynowy',
        'green' => 'Zielony',
        'teal' => 'Morski',
        'blue' => 'Niebieski',
        'violet' => 'Fioletowy',
        'pink' => 'Różowy',
    ],

];

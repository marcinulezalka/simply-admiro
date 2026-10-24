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

    'banner' => [
        'title' => 'Ta strona używa plików cookies',
        'lead' => 'Niezbędne pliki cookies utrzymują logowanie i bezpieczeństwo. Za Twoją zgodą używamy też cookies do zapamiętania preferencji, analizy ruchu i marketingu. Wybór zmienisz w każdej chwili w ustawieniach cookies.',
    ],
    'settings' => [
        'title' => 'Ustawienia cookies',
        'lead' => 'Wybierz, na które kategorie plików cookies się zgadzasz. Niezbędne są zawsze włączone, bo bez nich strona nie działa.',
        'always_on' => 'Zawsze włączone',
        'show_cookies' => 'Pokaż pliki cookies',
    ],
    'actions' => [
        'accept' => 'Zezwól na wszystkie',
        'reject' => 'Odrzuć',
        'customize' => 'Dostosuj',
        'save' => 'Zapisz preferencje',
        'close' => 'Zamknij',
    ],
    'links' => [
        'settings' => 'Ustawienia cookies',
        'policy' => 'Polityka cookies',
    ],
    'categories' => [
        'necessary' => ['label' => 'Niezbędne', 'description' => 'Logowanie, bezpieczeństwo, zapamiętanie zgody i ustawień, które sam wybierasz.'],
        'preferences' => ['label' => 'Preferencje', 'description' => 'Zapamiętanie dodatkowych ustawień, np. języka czy regionu.'],
        'analytics' => ['label' => 'Analityczne', 'description' => 'Anonimowe statystyki odwiedzin, które pomagają ulepszać stronę.'],
        'marketing' => ['label' => 'Marketingowe', 'description' => 'Dopasowanie treści i reklam, także przez partnerów.'],
    ],
    'decisions' => [
        'accepted' => 'zgoda na wszystkie',
        'rejected' => 'odrzucenie',
        'customized' => 'wybór kategorii',
    ],
    'core' => [
        'session' => 'Sesja strefy aplikacji (logowanie, ochrona formularzy).',
        'device' => 'Zaufane urządzenie: krótsze logowanie bez kodu z e-maila, gdy je wybierzesz.',
        'consent' => 'Zapamiętanie Twojej decyzji w sprawie cookies.',
        'appearance' => 'Motyw, kolorystyka i zwinięte menu, które sam wybierasz (pamięć przeglądarki).',
    ],
    'lifetimes' => [
        'session' => 'do zamknięcia przeglądarki albo wylogowania',
        'device' => 'do 30 dni',
        'consent' => '12 miesięcy',
        'storage' => 'do usunięcia w przeglądarce',
    ],
    'policy' => [
        'draft_name' => 'Polityka cookies — szkic',
        'version_name' => 'Polityka cookies, wersja :version',
        'eyebrow' => 'Wersja :version z :date',
        'content' => 'Polityka',
        'list' => 'Pliki cookies na stronie',
        'name' => 'Nazwa',
        'category' => 'Kategoria',
        'purpose' => 'Cel',
        'lifetime' => 'Czas przechowywania',
    ],

];

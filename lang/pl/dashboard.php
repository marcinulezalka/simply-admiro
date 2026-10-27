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

    'title' => 'Dashboard',

    'onboarding_title' => 'Onboarding',
    'onboarding_card_title' => 'Skonfiguruj admiro',
    'onboarding_card_complete_title' => 'Konfiguracja zakończona',
    'onboarding_card_lead' => 'Przejdź przez zadania onboardingu, aby przygotować admiro do pracy z pierwszymi użytkownikami.',
    'onboarding_card_next' => 'Następne zadanie: :name',
    'onboarding_progress' => 'Zrobione :done z :total',
    'onboarding_hide_done' => 'Nie pokazuj zadań zrealizowanych',
    'onboarding_step' => 'Zadanie :number',
    'onboarding_steps' => 'Zadania onboardingu',
    'onboarding_remaining' => 'Pozostałe zadania',
    'onboarding_finished' => 'Zrobione',
    'onboarding_confirm' => 'Zatwierdź i dalej',
    'onboarding_confirm_last' => 'Zatwierdź i zakończ',
    'onboarding_open' => 'Przejdź do: :name',
    'onboarding_states' => [
        'done' => 'Zrobione',
        'in_progress' => 'W trakcie',
        'not_started' => 'Nierozpoczęte',
    ],
    'onboarding_confirmed' => 'Zadanie „:name” zatwierdzone.',
    'onboarding_complete' => 'Wszystkie zadania konfiguracji są zrobione.',
    'onboarding_review' => 'Przejrzyj zadania',
    'onboarding_languages_enabled' => 'Włączone języki',
    'onboarding_languages_enabled_empty' => 'Żaden język nie jest jeszcze włączony.',
    'onboarding_languages_search_title' => 'Znajdź język',
    'onboarding_languages_search_label' => 'Szukaj języka',
    'onboarding_languages_search_placeholder' => 'Nazwa języka albo kraju, np. niemiecki, Brazylia',
    'onboarding_languages_results' => 'Języki pasujące do „:term”',
    'onboarding_languages_none' => 'Brak języków pasujących do „:term”.',
    'onboarding_languages_hint' => 'Wpisz nazwę języka albo kraju — pasujące języki pojawią się od razu w panelach z przełącznikami, a włączony trafi do włączonych.',
    'onboarding_language_switch' => 'Język :name włączony',
    'onboarding_language_locked' => 'Język aplikacji albo interfejsu — zawsze włączony',
    'onboarding_language_default' => 'Domyślny',
    'onboarding_regional_title' => 'Ustawienia regionalne',
    'onboarding_regional_missing' => 'Kraj, waluta, podatki i święta nie są jeszcze ustawione — ustaw je w zadaniu „Ustawienia regionalne”.',
    'onboarding_regional_country' => 'Kraj',
    'onboarding_regional_currency' => 'Waluta',
    'onboarding_regional_taxes' => 'Podatki',
    'onboarding_regional_holidays' => 'Święta',
    'onboarding_regional_holidays_count' => 'Dni wolne w tym roku: :count',
    'onboarding_regional_set' => 'Ustaw',
    'onboarding_organizations_enabled' => 'Struktura organizacyjna',
    'onboarding_organizations_active_count' => 'Aktywne: :active z :all',
    'onboarding_organizations_enabled_empty' => 'Żadna organizacja nie jest jeszcze aktywna.',
    'onboarding_organizations_search_label' => 'Szukaj organizacji',
    'onboarding_organizations_search_placeholder' => 'Nazwa, nazwa skrócona albo NIP',
    'onboarding_organizations_results' => 'Organizacje pasujące do „:term”',
    'onboarding_organizations_none' => 'Brak organizacji pasujących do „:term”. Załóż nową przyciskiem +.',
    'onboarding_organization_switch' => 'Organizacja :name aktywna',
    'onboarding_organization_locked' => 'Organizacja operatora — zawsze aktywna',
    'onboarding_organization_default' => 'Domyślna',
    'onboarding_organization_zones' => 'Strefy',
    'onboarding_organization_no_zone' => 'Brak strefy',
    'onboarding_organization_owner' => 'Właściciel',
    'onboarding_organization_no_owner' => 'Brak właściciela',
    'onboarding_organization_add_owner' => 'Dodaj właściciela',
    'onboarding_organization_root_drop' => 'Upuść tutaj, aby przenieść organizację na najwyższy poziom',
    'onboarding_organization_drag_hint' => 'Przeciągnij do aktywnych organizacji, aby ją włączyć',
    'onboarding_installation_title' => 'Instalacja systemu i konto hero',
    'onboarding_installation_lead' => 'Pobierz system, skonfiguruj środowisko i załóż pierwsze konto administratora (hero). Na tym serwerze wdrożenie zrobiło to zadanie — instrukcja przyda się przy kolejnych instalacjach.',
    'onboarding_installation_guide_title' => 'Instrukcja instalacji',
    'onboarding_installation_guide' => [
        'download' => [
            'title' => 'Pobierz system',
            'text' => 'Sklonuj repozytorium aplikacji, zainstaluj zależności PHP i zbuduj zasoby interfejsu (PHP 8.4, Composer, Node.js).',
        ],
        'environment' => [
            'title' => 'Skonfiguruj środowisko',
            'text' => 'Utwórz plik .env z szablonu i wygeneruj klucz aplikacji. Uzupełnij adres aplikacji (APP_URL), połączenie z bazą (DB_*), skrzynkę wysyłającą e-maile (MAIL_*, z hasłem MAIL_PASSWORD) i języki (APP_LOCALES).',
        ],
        'database' => [
            'title' => 'Przygotuj bazę danych',
            'text' => 'Utwórz tabele i wczytaj dane startowe: słowniki, organizację operatora, role i uprawnienia oraz pierwsze wersje polityk. Polecenia można powtarzać.',
        ],
        'hero' => [
            'title' => 'Załóż konto hero',
            'text' => 'Pierwsze konto administratora z pełnymi uprawnieniami. PIN administrator ustawia przy pierwszym logowaniu kodem z e-maila.',
        ],
        'deploy' => [
            'title' => 'Wdróż na serwer',
            'text' => 'Jedno polecenie z komputera deweloperskiego wysyła aplikację na serwer, uruchamia migracje i zakłada konta z pliku infrastructure/deploy.onboarding.env. Najpierw sprawdź plan z --dry-run.',
        ],
    ],
    'onboarding_languages_title' => 'Języki',
    'onboarding_languages_lead' => 'Włącz języki, w których działa firma — tylko one pojawią się w wyborach. Domyślnie włączony jest tylko język aplikacji i języki interfejsu.',
    'onboarding_settings_title' => 'Ustawienia regionalne',
    'onboarding_settings_lead' => 'Wybierz kraj, walutę i język domyślny oraz języki interfejsu.',
    'onboarding_audit_title' => 'Dziennik zdarzeń',
    'onboarding_audit_lead' => 'Zajrzyj do dziennika: logowania, blokady, zmiany ról i akcje administratorów w jednym miejscu.',
    'onboarding_legal_title' => 'Dokumenty prawne',
    'onboarding_legal_lead' => 'Na koniec opublikuj politykę cookies, politykę prywatności i klauzulę RODO, zanim zaprosisz pierwszych użytkowników.',
    'onboarding_organizations_title' => 'Organizacje',
    'onboarding_organizations_lead' => 'Załóż organizacje klientów z danymi firmy, kontaktem i adresem.',
    'onboarding_owners_title' => 'Właściciele stref',
    'onboarding_owners_lead' => 'Załóż konta właścicieli stref, którzy dalej sami poprowadzą swoje zespoły.',
    'onboarding_administrators_title' => 'Profile i uprawnienia',
    'onboarding_administrators_lead' => 'Zdefiniuj role użytkowników i widoki, do których mają dostęp, a potem nadaj role administratorom.',

];

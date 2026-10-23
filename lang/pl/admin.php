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

    'zones_title' => 'Strefy i konta',
    'owner_title' => 'Właściciel strefy',
    'administrators_title' => 'Administratorzy',
    'no_sections' => 'Twoje konto nie ma jeszcze uprawnień do żadnej sekcji panelu.',
    'zone' => 'Strefa',
    'zone_slug' => 'Adres',
    'zone_teams' => 'organizacje klientów',
    'yes' => 'tak',
    'no' => 'nie',
    'accounts_title' => 'Konta strefy :zone',
    'search_email' => 'Adres e-mail',
    'account_name' => 'Imię i nazwisko',
    'account_email' => 'Adres e-mail',
    'account_state' => 'Stan',
    'account_last_login' => 'Ostatnie logowanie',
    'account_actions' => 'Akcje',
    'no_accounts' => 'Brak kont.',
    'state_active' => 'aktywne',
    'state_inactive' => 'wyłączone',
    'state_locked' => 'zablokowane',
    'state_no_pin' => 'bez PIN-u',
    'own_account' => 'Własnego konta nie zmienia się w panelu; użyj ustawień konta.',
    'owner_intro' => 'Konto właściciela powstaje w strefie docelowej (albo dostaje rolę, jeśli adres ma już konto). Właściciel ustawi PIN przy pierwszym logowaniu.',
    'organization' => 'Organizacja',
    'organization_operator' => '— organizacja operatora (strefy bez organizacji klientów) —',
    'owner_organization_required' => 'Ta strefa działa w organizacjach klientów: wybierz organizację.',
    'owner_created' => 'Właściciel założony.',
    'no_provisioners' => 'Żadna strefa nie udostępnia zakładania właścicieli.',
    'roles' => 'Role',
    'role' => 'Rola',
    'role_assign' => 'Nadaj rolę',
    'role_revoke' => 'Odbierz',
    'role_denied' => 'Nie możesz zmienić tej roli: daje więcej, niż masz sam.',
    'role_assigned' => 'Rola nadana.',
    'role_revoked' => 'Rola odebrana; sesje konta wylogowano.',
    'organizations_title' => 'Organizacje',
    'organization_create' => 'Nowa organizacja',
    'organization_parent' => 'Organizacja nadrzędna',
    'organization_enable' => 'Włącz',
    'members' => 'Członkowie',
    'members_title' => 'Członkowie organizacji :organization',
    'member_remove' => 'Usuń z organizacji',
    'member_removed' => 'Członek usunięty z organizacji; jego sesje wylogowano.',
    'no_members' => 'Brak członków.',
    'organization_created' => 'Organizacja założona.',
    'organization_updated' => 'Organizacja zapisana.',
    'organization_enabled' => 'Organizacja włączona.',
    'organization_disabled' => 'Organizacja wyłączona: jej członkowie nie mają w niej uprawnień.',
    'organization_rejected' => 'Nie można tak zmienić organizacji (cykl w drzewie, wyłączona organizacja nadrzędna albo organizacja operatora).',
    'audit_title' => 'Dziennik zdarzeń',
    'audit_from' => 'Od',
    'audit_to' => 'Do',
    'audit_empty' => 'Brak zdarzeń.',
    'settings_title' => 'Ustawienia systemu',
    'settings_country' => 'Kraj domyślny',
    'settings_currency' => 'Waluta domyślna',
    'settings_language' => 'Język domyślny',
    'settings_interface' => 'Języki interfejsu',
    'settings_saved' => 'Ustawienia zapisane.',
    'settings_rejected' => 'Nie można zapisać ustawień: wybierz co najmniej jeden język interfejsu, a język domyślny spośród nich.',
    'action' => [
        'reset-pin' => 'Resetuj PIN',
        'unlock' => 'Odblokuj',
        'deactivate' => 'Wyłącz',
    ],
    'action_done' => [
        'reset-pin' => 'PIN usunięty: właściciel ustawi nowy po kodzie z e-maila.',
        'unlock' => 'Konto odblokowane.',
        'deactivate' => 'Konto wyłączone; jego sesje wylogowano.',
    ],

];

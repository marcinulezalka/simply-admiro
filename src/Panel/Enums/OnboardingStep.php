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

namespace Simplysmart\Admiro\Panel\Enums;

use Simplysmart\Admiro\Enums\LegalDocumentType;
use Simplysmart\Admiro\Panel\Http\AdminContext;

/**
 * Kroki onboardingu strefy administratorów w kolejności wykonywania (pulpit i kreator `onboarding.show`). Krok jest
 * zrobiony, gdy zapisano wszystkie jego kamienie milowe (milestones(), Panel\Onboarding\OnboardingProgress) — krok
 * „Instalacja systemu i konto hero” jest zrobiony od początku, „Języki” oznacza zatwierdzenie w kreatorze, pozostałe Panel\Onboarding\RecordOnboardingProgress po akcji
 * administratora w panelu. Teksty: `admiro::dashboard.onboarding_<value>_title|lead`.
 */
enum OnboardingStep: string
{
    /** Akcja dziennika zdarzeń, którą kreator zatwierdza krok (Panel\Http\AdminAudit, RecordOnboardingProgress). */
    public const string CONFIRMED_ACTION = 'onboarding step confirmed';

    /** Kotwica sekcji przesyłania plików tłumaczeń w zadaniu „Języki” (menu „+”, powrót po imporcie). */
    public const string TRANSLATIONS_ANCHOR = 'translations-upload';

    /**
     * Kroki wykonywane w samym kreatorze (trasa onboarding.show z parametrem kroku).
     */
    public const array IN_WIZARD = [self::Languages, self::Countries, self::Currencies, self::Calendar, self::Taxes, self::Organizations];

    case Installation = 'installation';
    case Languages = 'languages';
    /** Kraje: włączone kraje (waluta, UE, dni wolne, VAT). */
    case Countries = 'countries';
    /** Waluty: włączone waluty i ich kursy NBP. */
    case Currencies = 'currencies';
    /** Kalendarz i święta: dni wolne włączonych krajów. */
    case Calendar = 'calendar';
    /** Stawki podatkowe włączonych krajów. */
    case Taxes = 'taxes';
    case Settings = 'settings';
    case Organizations = 'organizations';
    /** Profile i uprawnienia: role użytkowników i ich nadawanie (wartość zostaje, bo pod nią zapisany jest postęp). */
    case Administrators = 'administrators';
    case Owners = 'owners';
    case Audit = 'audit';
    case Legal = 'legal';

    /**
     * Kroki w kolejności wykonywania, do których administrator ma uprawnienie (pulpit i kreator onboardingu).
     *
     * @return list<self>
     */
    public static function permittedFor(AdminContext $context): array
    {
        return array_values(array_filter(self::cases(), static fn(self $step): bool => $context->can($step->permission())));
    }

    /**
     * Uprawnienie potrzebne do wykonania kroku (bez niego krok nie pojawia się na pulpicie).
     */
    public function permission(): Permission
    {
        return match ($this) {
            self::Installation, self::Languages, self::Countries, self::Currencies, self::Calendar, self::Taxes, self::Settings, self::Legal => Permission::SettingsManage,
            self::Organizations => Permission::OrganizationsManage,
            self::Owners => Permission::OwnersAssign,
            self::Administrators => Permission::UsersManage,
            self::Audit => Permission::AuditView,
        };
    }

    /**
     * Krótka nazwa trasy strefy, na której wykonuje się krok (języki, kraje, waluty, kalendarz, stawki podatkowe i organizacje
     * w samym kreatorze, pozostałe na swoich stronach).
     */
    public function route(): string
    {
        return match ($this) {
            self::Installation => 'dashboard',
            self::Languages, self::Countries, self::Currencies, self::Calendar, self::Taxes, self::Organizations => 'onboarding.show',
            self::Settings => 'settings.show',
            self::Legal => 'cookie-policy.index',
            self::Owners => 'owners.create',
            self::Administrators => 'administrators.index',
            self::Audit => 'audit.index',
        };
    }

    /**
     * @return array<string, string>
     */
    public function routeParameters(): array
    {
        return in_array($this, self::IN_WIZARD, true) ? ['step' => $this->value] : [];
    }

    public function icon(): string
    {
        return match ($this) {
            self::Installation => 'cpu',
            self::Languages => 'languages',
            self::Countries => 'flag',
            self::Currencies => 'credit-card',
            self::Calendar => 'calendar',
            self::Taxes => 'invoice',
            self::Settings => 'locale',
            self::Organizations => 'building',
            self::Administrators => 'id-card',
            self::Owners => 'user-plus',
            self::Audit => 'history',
            self::Legal => 'scale',
        };
    }

    /**
     * Kamienie milowe, które razem zamykają krok; dokumenty prawne wymagają publikacji polityki cookies i każdego
     * dokumentu z LegalDocumentType. Instalacja systemu z kontem hero nie ma żadnego: robi ją wdrożenie, zanim ktokolwiek
     * wejdzie do panelu, więc krok 1 jest zrobiony od początku. Dziennik zdarzeń zamyka pierwsze otwarcie dziennika
     * (AuditController).
     *
     * @return list<string>
     */
    public function milestones(): array
    {
        if ($this === self::Installation) {
            return [];
        }

        if ($this !== self::Legal) {
            return [$this->value];
        }

        return [self::legalMilestone('cookies'), ...array_map(static fn(LegalDocumentType $type): string => self::legalMilestone($type->value), LegalDocumentType::cases())];
    }

    /**
     * Krok po tym w podanej liście kroków albo null dla ostatniego.
     *
     * @param list<self> $steps
     */
    public function next(array $steps): ?self
    {
        $index = array_search($this, $steps, true);

        return $index === false ? null : ($steps[$index + 1] ?? null);
    }

    /**
     * Pojęcie słownika (Wiki\Wiki, bez prefiksu wiki-) wyjaśniane nad treścią zadania w kreatorze; null — bez wpisu.
     */
    public function wiki(): ?string
    {
        return match ($this) {
            self::Organizations => 'organizacja',
            self::Administrators => 'strefa',
            self::Owners => 'wlasciciel',
            default => null,
        };
    }

    public static function legalMilestone(string $document): string
    {
        return self::Legal->value . '.' . $document;
    }
}

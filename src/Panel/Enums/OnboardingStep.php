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

/**
 * Kroki onboardingu strefy administratorów w kolejności wykonywania (pulpit). Krok jest zrobiony, gdy zapisano
 * wszystkie jego kamienie milowe (milestones(), Panel\Onboarding\OnboardingProgress) — oznacza je
 * Panel\Onboarding\RecordOnboardingProgress po akcji administratora w panelu. Teksty:
 * `admiro::dashboard.onboarding_<value>_title|lead`.
 */
enum OnboardingStep: string
{
    case Dictionaries = 'dictionaries';
    case Settings = 'settings';
    case Legal = 'legal';
    case Organizations = 'organizations';
    case Owners = 'owners';
    case Administrators = 'administrators';

    /**
     * Uprawnienie potrzebne do wykonania kroku (bez niego krok nie pojawia się na pulpicie).
     */
    public function permission(): Permission
    {
        return match ($this) {
            self::Dictionaries, self::Settings, self::Legal => Permission::SettingsManage,
            self::Organizations => Permission::OrganizationsManage,
            self::Owners => Permission::OwnersAssign,
            self::Administrators => Permission::UsersManage,
        };
    }

    /**
     * Krótka nazwa trasy strefy, na której wykonuje się krok.
     */
    public function route(): string
    {
        return match ($this) {
            self::Dictionaries => 'dictionaries.index',
            self::Settings => 'settings.show',
            self::Legal => 'cookie-policy.index',
            self::Organizations => 'organizations.create',
            self::Owners => 'owners.create',
            self::Administrators => 'administrators.index',
        };
    }

    /**
     * @return array<string, string>
     */
    public function routeParameters(): array
    {
        return $this === self::Dictionaries ? ['dictionary' => 'countries'] : [];
    }

    public function icon(): string
    {
        return match ($this) {
            self::Dictionaries => 'dictionaries',
            self::Settings => 'locale',
            self::Legal => 'cookie',
            self::Organizations => 'building',
            self::Owners => 'user-plus',
            self::Administrators => 'shield-check',
        };
    }

    /**
     * Kamienie milowe, które razem zamykają krok; dokumenty prawne wymagają publikacji polityki cookies i każdego
     * dokumentu z LegalDocumentType.
     *
     * @return list<string>
     */
    public function milestones(): array
    {
        if ($this !== self::Legal) {
            return [$this->value];
        }

        return [self::legalMilestone('cookies'), ...array_map(static fn(LegalDocumentType $type): string => self::legalMilestone($type->value), LegalDocumentType::cases())];
    }

    public static function legalMilestone(string $document): string
    {
        return self::Legal->value . '.' . $document;
    }
}

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
 * Kroki onboardingu strefy administratorów w kolejności wykonywania (pulpit i kreator `onboarding.show`). Krok jest
 * zrobiony, gdy zapisano wszystkie jego kamienie milowe (milestones(), Panel\Onboarding\OnboardingProgress) — krok
 * „Języki” oznacza zatwierdzenie w kreatorze, pozostałe Panel\Onboarding\RecordOnboardingProgress po akcji
 * administratora w panelu. Teksty: `admiro::dashboard.onboarding_<value>_title|lead`.
 */
enum OnboardingStep: string
{
    case Languages = 'languages';
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
            self::Languages, self::Settings, self::Legal => Permission::SettingsManage,
            self::Organizations => Permission::OrganizationsManage,
            self::Owners => Permission::OwnersAssign,
            self::Administrators => Permission::UsersManage,
        };
    }

    /**
     * Krótka nazwa trasy strefy, na której wykonuje się krok (języki w samym kreatorze, pozostałe na swoich stronach).
     */
    public function route(): string
    {
        return match ($this) {
            self::Languages => 'onboarding.show',
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
        return $this === self::Languages ? ['step' => $this->value] : [];
    }

    public function icon(): string
    {
        return match ($this) {
            self::Languages => 'locale',
            self::Settings => 'settings',
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
     * Krok przed tym w podanej liście kroków albo null dla pierwszego.
     *
     * @param list<self> $steps
     */
    public function previous(array $steps): ?self
    {
        $index = array_search($this, $steps, true);

        return $index === false || $index === 0 ? null : $steps[$index - 1];
    }

    public static function legalMilestone(string $document): string
    {
        return self::Legal->value . '.' . $document;
    }
}

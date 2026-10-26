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

/**
 * Stan kroku onboardingu z jego kamieni milowych (Panel\Onboarding\OnboardingProgress::checklist()): zrobiony — są
 * wszystkie, w trakcie — jest część (np. „Dokumenty prawne” z opublikowaną tylko polityką cookies), nierozpoczęty —
 * nie ma żadnego.
 */
enum OnboardingStepState: string
{
    case Done = 'done';
    case InProgress = 'in_progress';
    case NotStarted = 'not_started';

    /**
     * @param list<string> $required kamienie milowe kroku
     * @param list<string> $recorded zapisane kamienie milowe
     */
    public static function of(array $required, array $recorded): self
    {
        $missing = array_diff($required, $recorded);

        return match (true) {
            $missing === [] => self::Done,
            count($missing) < count($required) => self::InProgress,
            default => self::NotStarted,
        };
    }

    /**
     * Kolor plakietki stanu (.ss-badge--<ton>): zrobiony — success, w trakcie — warning, nierozpoczęty — muted.
     */
    public function tone(): string
    {
        return match ($this) {
            self::Done => 'success',
            self::InProgress => 'warning',
            self::NotStarted => 'muted',
        };
    }

    public function label(): string
    {
        return text('admiro::dashboard.onboarding_states.' . $this->value);
    }
}

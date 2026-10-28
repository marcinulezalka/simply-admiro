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

namespace Simplysmart\Admiro\Cookies;

use Illuminate\Support\Carbon;
use Simplysmart\Admiro\Enums\ConsentDecision;
use Simplysmart\Admiro\Enums\CookieCategory;
use Simplysmart\Admiro\Models\CookieConsent;

/**
 * Rejestr zgód na cookies: zapisuje decyzję przeglądarki (losowy identyfikator UUID z ciasteczka, wersja polityki,
 * kategorie — niezbędne zawsze) bez IP i danych osobowych; nieznana wersja albo zły identyfikator są odrzucane.
 */
final readonly class ConsentRegistry
{
    private const string UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/';

    /**
     * @param list<CookieCategory> $categories
     *
     * @throws ConsentRejected Gdy identyfikator nie jest UUID albo wersja nie jest bieżąca.
     */
    public function record(string $consentId, int $policyVersion, int $currentVersion, ConsentDecision $decision, array $categories): CookieConsent
    {
        if (preg_match(self::UUID_PATTERN, $consentId) !== 1 || $policyVersion !== $currentVersion) {
            throw ConsentRejected::because('the consent has an invalid identifier or a stale policy version.');
        }

        $values = array_values(array_unique([CookieCategory::Necessary->value, ...array_map(static fn(CookieCategory $category): string => $category->value, $categories)]));

        $consent = new CookieConsent();
        $consent->forceFill([
            'recorded_at' => Carbon::now(),
            'consent_id' => $consentId,
            'policy_version' => $policyVersion,
            'decision' => $decision,
            'categories' => $values,
        ])->save();

        return $consent;
    }
}

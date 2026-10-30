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

namespace Simplysmart\Admiro\Auth;

use Illuminate\Contracts\Session\Session;

/**
 * Blokada ekranu zalogowanego konta (jak Win+L): znacznik w sesji strefy — konto zostaje zalogowane, ale każda strona
 * dla zalogowanych prowadzi na ekran PIN-u (Http\Middleware\EnsureScreenUnlocked) do czasu podania poprawnego PIN-u.
 * Adres strony sprzed blokady jest zapamiętany, żeby po odblokowaniu do niej wrócić.
 */
final readonly class ScreenLock
{
    public const string SESSION_KEY = 'admiro.screen_locked';

    public const string RETURN_KEY = 'admiro.screen_lock_return';

    public function lock(Session $session, ?string $returnTo = null): void
    {
        $session->put(self::SESSION_KEY, true);

        if ($returnTo !== null) {
            $session->put(self::RETURN_KEY, $returnTo);
        }
    }

    public function isLocked(Session $session): bool
    {
        return $session->get(self::SESSION_KEY) === true;
    }

    /**
     * Zdejmuje blokadę; zwraca adres strony sprzed blokady (null — brak).
     */
    public function unlock(Session $session): ?string
    {
        $session->forget(self::SESSION_KEY);
        $returnTo = $session->pull(self::RETURN_KEY);

        return is_string($returnTo) ? $returnTo : null;
    }
}

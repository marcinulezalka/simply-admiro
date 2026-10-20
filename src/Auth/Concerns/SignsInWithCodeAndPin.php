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

namespace Simplysmart\Mainboard\Auth\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Container\Container;
use Simplysmart\Mainboard\Auth\AccountColumns;
use Simplysmart\Mainboard\Auth\EmailAddress;
use Simplysmart\Mainboard\Contracts\BlindIndex;

/**
 * Implementacja Contracts\CodeAndPinAccount dla modelu konta strefy (kolumny z Database\ZoneTables).
 *
 * E-mail jest szyfrowany, a przy zapisie normalizowany i indeksowany w `email_hash`.
 * Laravelowe remember-me jest wyłączone (zastępują je zaufane urządzenia).
 *
 * @see \Simplysmart\Mainboard\Models\ZoneAccount
 */
trait SignsInWithCodeAndPin
{
    public static function bootSignsInWithCodeAndPin(): void
    {
        static::saving(static function (self $account): void {
            if (! $account->isDirty(AccountColumns::EMAIL)) {
                return;
            }

            $email = EmailAddress::normalize($account->emailAddress());
            $account->setAttribute(AccountColumns::EMAIL, $email);
            $account->setAttribute(AccountColumns::EMAIL_HASH, Container::getInstance()->make(BlindIndex::class)->hash($email));
        });
    }

    public function initializeSignsInWithCodeAndPin(): void
    {
        $this->mergeCasts([
            AccountColumns::EMAIL => 'encrypted',
            AccountColumns::PIN_SET_AT => 'datetime',
            AccountColumns::PIN_FAILED_ATTEMPTS => 'integer',
            AccountColumns::LOCK_LEVEL => 'integer',
            AccountColumns::LOCKED_UNTIL => 'datetime',
            AccountColumns::AUTH_EPOCH => 'integer',
            AccountColumns::IS_ACTIVE => 'boolean',
            AccountColumns::LAST_LOGIN_AT => 'datetime',
            AccountColumns::LAST_LOGIN_IP => 'encrypted',
        ]);

        $this->mergeHidden([AccountColumns::PIN_HASH, AccountColumns::EMAIL_HASH]);
    }

    public function emailAddress(): string
    {
        $email = $this->getAttribute(AccountColumns::EMAIL);

        return is_string($email) ? $email : '';
    }

    public function displayName(): string
    {
        $name = $this->getAttribute(AccountColumns::NAME);

        return is_string($name) ? $name : '';
    }

    public function canSignIn(): bool
    {
        return $this->getAttribute(AccountColumns::IS_ACTIVE) === true && ! $this->isLocked();
    }

    public function pinHash(): ?string
    {
        $hash = $this->getAttribute(AccountColumns::PIN_HASH);

        return is_string($hash) && $hash !== '' ? $hash : null;
    }

    public function hasPin(): bool
    {
        return $this->pinHash() !== null;
    }

    public function isLocked(): bool
    {
        $until = $this->getAttribute(AccountColumns::LOCKED_UNTIL);

        return $until instanceof CarbonInterface && $until->isFuture();
    }

    public function failedPinAttempts(): int
    {
        $attempts = $this->getAttribute(AccountColumns::PIN_FAILED_ATTEMPTS);

        return is_int($attempts) ? $attempts : 0;
    }

    public function lockLevel(): int
    {
        $level = $this->getAttribute(AccountColumns::LOCK_LEVEL);

        return is_int($level) ? $level : 0;
    }

    public function authEpoch(): int
    {
        $epoch = $this->getAttribute(AccountColumns::AUTH_EPOCH);

        return is_int($epoch) ? $epoch : 0;
    }

    public function getRememberTokenName(): string
    {
        return '';
    }
}

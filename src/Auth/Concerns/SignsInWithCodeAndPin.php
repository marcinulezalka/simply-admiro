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

namespace Simplysmart\Admiro\Auth\Concerns;

use Carbon\CarbonInterface;
use Illuminate\Container\Container;
use Simplysmart\Admiro\Auth\AccountColumns;
use Simplysmart\Admiro\Auth\EmailAddress;
use Simplysmart\Admiro\Contracts\BlindIndex;

/**
 * Implementacja Contracts\CodeAndPinAccount dla modelu konta strefy (kolumny z Database\ZoneTables).
 *
 * E-mail jest szyfrowany, a przy zapisie normalizowany i indeksowany w `email_hash`.
 * Laravelowe remember-me jest wyłączone (zastępują je zaufane urządzenia). Liczniki z wartością domyślną
 * w bazie mają ją też w nowym modelu (checksum, docs/baza-danych.md §5).
 *
 * @see \Simplysmart\Admiro\Models\ZoneAccount
 *
 * @method static void saving(\Closure|string $callback) Metoda modelu Eloquent.
 * @method bool isDirty(array<int, string>|string|null $attributes = null) Metoda modelu Eloquent.
 * @method $this setAttribute(string $key, mixed $value) Metoda modelu Eloquent.
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
        $this->attributes += [AccountColumns::PIN_FAILED_ATTEMPTS => 0, AccountColumns::LOCK_LEVEL => 0, AccountColumns::AUTH_EPOCH => 0];
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
            AccountColumns::RECOVERY_EMAIL => 'encrypted',
            AccountColumns::RECOVERY_EMAIL_SET_AT => 'datetime',
        ]);

        $this->mergeHidden([AccountColumns::PIN_HASH, AccountColumns::EMAIL_HASH]);
    }

    public function emailAddress(): string
    {
        $email = $this->getAttribute(AccountColumns::EMAIL);

        return is_string($email) ? $email : '';
    }

    public function recoveryEmail(): ?string
    {
        $email = $this->getAttribute(AccountColumns::RECOVERY_EMAIL);

        return is_string($email) && $email !== '' ? $email : null;
    }

    public function displayName(): string
    {
        $name = $this->getAttribute(AccountColumns::NAME);

        return is_string($name) ? $name : '';
    }

    public function canSignIn(): bool
    {
        return $this->getAttribute(AccountColumns::IS_ACTIVE) === true && $this->recordStatus()?->isUsable() !== false && ! $this->isLocked();
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

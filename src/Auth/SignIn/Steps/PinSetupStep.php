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

namespace Simplysmart\Mainboard\Auth\SignIn\Steps;

use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Http\Request;
use Simplysmart\Mainboard\Auth\SignIn\PendingSignIn;
use Simplysmart\Mainboard\Auth\SignIn\PendingSignInResolver;
use Simplysmart\Mainboard\Contracts\AccountStore;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\SignInStep;
use Simplysmart\Mainboard\Rules\StrongPin;

/**
 * Ustawienie PIN-u przy pierwszym logowaniu (konto bez PIN-u, po kroku kodu).
 */
final readonly class PinSetupStep implements SignInStep
{
    public const string KEY = 'pin-setup';

    public function __construct(
        private Hasher $hasher,
        private AccountStore $store,
        private PendingSignInResolver $pending,
        private CurrentZone $zone,
    ) {}

    public function key(): string
    {
        return self::KEY;
    }

    public function isRequired(PendingSignIn $signIn): bool
    {
        return $signIn->account !== null && ! $signIn->account->hasPin();
    }

    public function ttlMinutes(): int
    {
        return $this->zone->get()->auth()->pinStageTtlMinutes;
    }

    public function view(): string
    {
        return 'mainboard::auth.pin-setup';
    }

    public function viewData(PendingSignIn $signIn): array
    {
        return ['pinLength' => $this->zone->get()->auth()->pinLength];
    }

    public function rules(): array
    {
        return ['pin' => ['required', 'string', 'digits:' . $this->zone->get()->auth()->pinLength, 'confirmed', new StrongPin()]];
    }

    public function handle(PendingSignIn $signIn, array $input, Request $request): void
    {
        $account = $signIn->account;

        if ($account === null || $account->hasPin()) {
            $this->pending->abort();
        }

        $this->store->setPinHash($account, $this->hasher->make(is_string($input['pin'] ?? null) ? $input['pin'] : ''));
    }
}

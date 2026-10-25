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

namespace Simplysmart\Admiro\Auth\SignIn\Steps;

use Illuminate\Http\Request;
use Simplysmart\Admiro\Auth\PinLength;
use Simplysmart\Admiro\Auth\PinRules;
use Simplysmart\Admiro\Auth\SignIn\LoginAttemptLimiter;
use Simplysmart\Admiro\Auth\SignIn\PendingSignIn;
use Simplysmart\Admiro\Auth\SignIn\PendingSignInResolver;
use Simplysmart\Admiro\Auth\SignIn\PinCheckResult;
use Simplysmart\Admiro\Auth\SignIn\PinVerifier;
use Simplysmart\Admiro\Auth\SignIn\SignInFailure;
use Simplysmart\Admiro\Auth\SignInLimit;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Contracts\SignInStep;

/**
 * PIN (wiedza). Zablokowane konto wraca do początku z ogólnym komunikatem; o blokadzie
 * dowiaduje się tylko właściciel (e-mail).
 */
final readonly class PinStep implements SignInStep
{
    public const string KEY = 'pin';

    public function __construct(
        private PinVerifier $pins,
        private LoginAttemptLimiter $limiter,
        private PendingSignInResolver $pending,
        private CurrentZone $zone,
    ) {}

    public function key(): string
    {
        return self::KEY;
    }

    public function isRequired(PendingSignIn $signIn): bool
    {
        return $signIn->account === null || ($signIn->account->hasPin() && ! $signIn->hasCompleted(PinSetupStep::KEY));
    }

    public function ttlMinutes(): int
    {
        return $this->zone->get()->auth()->pinStageTtlMinutes;
    }

    public function view(): string
    {
        return 'admiro::auth.pin';
    }

    public function viewData(PendingSignIn $signIn): array
    {
        return ['pinLength' => PinLength::accepted()];
    }

    public function rules(): array
    {
        return ['pin' => PinRules::forEnteredPin()];
    }

    public function handle(PendingSignIn $signIn, array $input, Request $request): void
    {
        $this->limiter->attempt(SignInLimit::PinChecks, (string) $request->ip(), 'pin');

        $account = $signIn->account ?? $this->pending->abort();
        $pin = is_string($input['pin'] ?? null) ? $input['pin'] : '';

        match ($this->pins->verify($account, $pin, $request->ip())) {
            PinCheckResult::Valid => null,
            PinCheckResult::Invalid => throw SignInFailure::on('pin'),
            PinCheckResult::Locked => $this->pending->abort(),
        };
    }
}

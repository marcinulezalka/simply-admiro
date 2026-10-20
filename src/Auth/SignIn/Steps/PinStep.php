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

use Illuminate\Http\Request;
use Simplysmart\Mainboard\Auth\SignIn\LoginAttemptLimiter;
use Simplysmart\Mainboard\Auth\SignIn\PendingSignIn;
use Simplysmart\Mainboard\Auth\SignIn\PendingSignInResolver;
use Simplysmart\Mainboard\Auth\SignIn\PinCheckResult;
use Simplysmart\Mainboard\Auth\SignIn\PinVerifier;
use Simplysmart\Mainboard\Auth\SignIn\SignInFailure;
use Simplysmart\Mainboard\Auth\SignInLimit;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\SignInStep;

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
        return 'mainboard::auth.pin';
    }

    public function viewData(PendingSignIn $signIn): array
    {
        return ['pinLength' => $this->zone->get()->auth()->pinLength];
    }

    public function rules(): array
    {
        return ['pin' => ['required', 'string', 'digits:' . $this->zone->get()->auth()->pinLength]];
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

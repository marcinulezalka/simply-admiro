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
use Simplysmart\Mainboard\Auth\SignIn\CodeCheckResult;
use Simplysmart\Mainboard\Auth\SignIn\LoginAttemptLimiter;
use Simplysmart\Mainboard\Auth\SignIn\LoginChallengeService;
use Simplysmart\Mainboard\Auth\SignIn\PendingSignIn;
use Simplysmart\Mainboard\Auth\SignIn\SignInFailure;
use Simplysmart\Mainboard\Auth\SignIn\SignInFlowState;
use Simplysmart\Mainboard\Auth\SignInLimit;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\SignInStep;

/**
 * Kod z e-maila (posiadanie skrzynki). Bez konta (decoy) zachowuje się tak samo, tylko nigdy nie przechodzi.
 */
final readonly class EmailCodeStep implements SignInStep
{
    public const string KEY = 'code';

    public function __construct(
        private LoginChallengeService $challenges,
        private LoginAttemptLimiter $limiter,
        private SignInFlowState $state,
        private CurrentZone $zone,
    ) {}

    public function key(): string
    {
        return self::KEY;
    }

    public function isRequired(PendingSignIn $signIn): bool
    {
        return true;
    }

    public function ttlMinutes(): int
    {
        return $this->zone->get()->auth()->codeTtlMinutes;
    }

    public function view(): string
    {
        return 'mainboard::auth.code';
    }

    public function viewData(PendingSignIn $signIn): array
    {
        $settings = $this->zone->get()->auth();

        return [
            'email' => $signIn->email,
            'codeLength' => $settings->codeLength,
            'validForMinutes' => $settings->codeTtlMinutes,
            'resendIn' => $this->state->resendCooldownRemaining($settings->codeResendCooldownSeconds),
        ];
    }

    public function rules(): array
    {
        return ['code' => ['required', 'string', 'digits:' . $this->zone->get()->auth()->codeLength]];
    }

    public function handle(PendingSignIn $signIn, array $input, Request $request): void
    {
        $this->limiter->attempt(SignInLimit::CodeChecks, (string) $request->ip(), 'code');
        $this->limiter->attempt(SignInLimit::AccountCodeChecks, $signIn->email, 'code');

        $code = is_string($input['code'] ?? null) ? $input['code'] : '';

        if ($signIn->account === null) {
            $this->state->recordDecoyAttempt();

            throw SignInFailure::on('code');
        }

        if ($this->challenges->verify($signIn->account, $code) !== CodeCheckResult::Valid) {
            throw SignInFailure::on('code');
        }

        $request->session()->regenerate();
    }
}

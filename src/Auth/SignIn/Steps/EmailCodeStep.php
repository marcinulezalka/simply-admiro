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
use Simplysmart\Admiro\Auth\SignIn\CodeCheckResult;
use Simplysmart\Admiro\Auth\SignIn\LoginAttemptLimiter;
use Simplysmart\Admiro\Auth\SignIn\LoginChallengeService;
use Simplysmart\Admiro\Auth\SignIn\PendingSignIn;
use Simplysmart\Admiro\Auth\SignIn\SignInFailure;
use Simplysmart\Admiro\Auth\SignIn\SignInFlowState;
use Simplysmart\Admiro\Auth\SignInLimit;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Contracts\SignInStep;

/**
 * Kod z e-maila (posiadanie skrzynki). Bez konta (decoy) zachowuje się tak samo, tylko nigdy nie przechodzi.
 * Na zaufanym urządzeniu konta krok jest pomijany (czynnikiem posiadania jest urządzenie); tu konto może
 * poprosić o zapamiętanie przeglądarki.
 */
final readonly class EmailCodeStep implements SignInStep
{
    public const string KEY = 'code';

    public const string REMEMBER_FIELD = 'remember_device';

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
        return ! ($signIn->deviceTrusted && $signIn->account !== null);
    }

    public function ttlMinutes(): int
    {
        return $this->zone->get()->auth()->codeTtlMinutes;
    }

    public function view(): string
    {
        return 'admiro::auth.code';
    }

    public function viewData(PendingSignIn $signIn): array
    {
        $settings = $this->zone->get()->auth();

        return [
            'email' => $signIn->email,
            'codeLength' => $settings->codeLength,
            'validForMinutes' => $settings->codeTtlMinutes,
            'resendIn' => $this->state->resendCooldownRemaining($settings->codeResendCooldownSeconds),
            'rememberDeviceDays' => $settings->trustedDevicesEnabled ? $settings->trustedDeviceLifetimeDays : null,
        ];
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'digits:' . $this->zone->get()->auth()->codeLength],
            self::REMEMBER_FIELD => ['nullable', 'boolean'],
        ];
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

        if ($this->zone->get()->auth()->trustedDevicesEnabled && filter_var($input[self::REMEMBER_FIELD] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $this->state->rememberDevice();
        }
    }
}

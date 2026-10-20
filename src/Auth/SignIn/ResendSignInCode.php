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

namespace Simplysmart\Mainboard\Auth\SignIn;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Simplysmart\Mainboard\Auth\SignInLimit;
use Simplysmart\Mainboard\Contracts\CurrentZone;

/**
 * Nowy kod po upływie odstępu między wysyłkami (te same limity i ten sam job co start logowania).
 */
final readonly class ResendSignInCode
{
    public function __construct(
        private SignInFlowState $state,
        private LoginAttemptLimiter $limiter,
        private PendingSignInResolver $pending,
        private CurrentZone $zone,
        private Dispatcher $bus,
    ) {}

    /**
     * @throws ValidationException
     */
    public function handle(Request $request): void
    {
        $wait = $this->state->resendCooldownRemaining($this->zone->get()->auth()->codeResendCooldownSeconds);

        if ($wait > 0) {
            throw ValidationException::withMessages(['code' => text('sign_in.resend_wait', ['seconds' => $wait])]);
        }

        $email = $this->state->email() ?? $this->pending->abort();

        $this->limiter->attempt(SignInLimit::CodeRequests, $email . '|' . $request->ip(), 'code');
        $this->limiter->attempt(SignInLimit::AccountCodeRequests, $email, 'code');

        $this->bus->dispatch(new IssueSignInCode($this->zone->get()->name, $email, $request->ip(), $request->userAgent(), replaceRecent: true));
        $this->state->codeIssued();
    }
}

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
use Simplysmart\Mainboard\Auth\EmailAddress;
use Simplysmart\Mainboard\Auth\SignInLimit;
use Simplysmart\Mainboard\Contracts\CurrentZone;

/**
 * Pierwszy krok: adres e-mail. Ta sama ścieżka dla każdego adresu (limity, stan w sesji, job
 * IssueSignInCode), więc ani odpowiedź, ani jej czas nie mówią, czy konto istnieje.
 */
final readonly class StartSignIn
{
    public function __construct(
        private SignInFlowState $state,
        private LoginAttemptLimiter $limiter,
        private CurrentZone $zone,
        private Dispatcher $bus,
    ) {}

    /**
     * @throws ValidationException Gdy limit próśb o kod jest wyczerpany.
     */
    public function handle(string $email, Request $request): void
    {
        $email = EmailAddress::normalize($email);

        $this->limiter->attempt(SignInLimit::CodeRequests, $email . '|' . $request->ip(), 'email');
        $this->limiter->attempt(SignInLimit::AccountCodeRequests, $email, 'email');

        $request->session()->regenerate();
        $this->state->begin($email);

        $this->bus->dispatch(new IssueSignInCode($this->zone->get()->name, $email, $request->ip(), $request->userAgent()));
    }
}

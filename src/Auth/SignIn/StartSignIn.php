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

namespace Simplysmart\Admiro\Auth\SignIn;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Simplysmart\Admiro\Auth\Devices\RecognizeTrustedDevice;
use Simplysmart\Admiro\Auth\EmailAddress;
use Simplysmart\Admiro\Auth\SignInLimit;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Security\Honeypot\FormHoneypot;
use Simplysmart\Admiro\Security\Honeypot\HoneypotTracker;
use Simplysmart\Admiro\Security\Honeypot\HoneypotTrap;

/**
 * Pierwszy krok: adres e-mail. Ta sama ścieżka dla każdego adresu (limity, stan w sesji, job
 * IssueSignInCode), więc ani odpowiedź, ani jej czas nie mówią, czy konto istnieje.
 *
 * Wyjątek dotyczy tylko przeglądarki z ciasteczkiem zaufanego urządzenia tego konta: kod nie jest wysyłany,
 * a logowanie przechodzi od razu do PIN-u. Formularz wysłany przez bota (pułapka formularza) dostaje tę samą
 * odpowiedź, ale bez kodu, a jego adres IP jest banowany.
 */
final readonly class StartSignIn
{
    public function __construct(
        private SignInFlowState $state,
        private LoginAttemptLimiter $limiter,
        private CurrentZone $zone,
        private Dispatcher $bus,
        private RecognizeTrustedDevice $devices,
        private FormHoneypot $honeypot,
        private HoneypotTracker $tracker,
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

        if ($this->honeypot->isBot($request)) {
            $this->tracker->record($request, HoneypotTrap::Form);

            return;
        }

        if ($this->devices->handle($email, $request)) {
            $this->state->trustDevice();

            return;
        }

        $this->bus->dispatch(new IssueSignInCode($this->zone->get()->name, $email, $request->ip(), $request->userAgent()));
    }
}

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

use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Validation\ValidationException;
use Simplysmart\Mainboard\Contracts\AccountLocator;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Zones\ZoneRedirects;

/**
 * Odtwarza PendingSignIn z sesji i kończy logowanie od nowa, gdy trzeba (zawsze tym samym komunikatem).
 */
final readonly class PendingSignInResolver
{
    public function __construct(
        private SignInFlowState $state,
        private AccountLocator $accounts,
        private CurrentZone $zone,
        private UrlGenerator $url,
    ) {}

    public function resolve(): PendingSignIn
    {
        $email = $this->state->email() ?? $this->abort();
        $account = $this->accounts->findByEmail($email);

        return new PendingSignIn(
            email: $email,
            account: $account !== null && $account->canSignIn() ? $account : null,
            completed: $this->state->completed(),
        );
    }

    /**
     * Czyści logowanie i wraca do formularza adresu z ogólnym komunikatem.
     *
     * @throws ValidationException
     */
    public function abort(?string $message = null): never
    {
        $this->state->clear();

        throw ValidationException::withMessages(['email' => $message ?? SignInFailure::message()])
            ->redirectTo($this->url->route($this->zone->get()->routeName(ZoneRedirects::LOGIN_ROUTE)));
    }
}

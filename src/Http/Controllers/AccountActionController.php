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

namespace Simplysmart\Admiro\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Http\AccountAction;
use Simplysmart\Admiro\Http\AdminContext;
use Simplysmart\Admiro\Http\AdministratorGuard;
use Simplysmart\Admiro\Http\ManagedAccounts;
use Simplysmart\Mainboard\Auth\AccountAdministration;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Reset PIN-u, odblokowanie i dezaktywacja konta strefy (`accounts.manage`). Konto administratora wymaga
 * dodatkowo `users.manage` i tego, żeby działający miał wszystkie jego uprawnienia; własnego konta nie zmienia się tu.
 */
final readonly class AccountActionController
{
    public function __construct(
        private ManagedAccounts $managed,
        private AdminContext $context,
        private AdministratorGuard $guard,
        private AccountAdministration $administration,
        private Redirector $redirect,
    ) {}

    public function __invoke(string $managedZone, string $account, string $action): RedirectResponse
    {
        $zone = $this->managed->zone($managedZone);
        $target = $this->managed->account($zone, $account);
        $back = $this->redirect->route($this->context->zone()->routeName('zones.accounts'), ['managedZone' => $zone->name]);

        if ($this->guard->isOwnAccount($target)) {
            return $back->withErrors(['account' => text('admiro::admin.own_account')]);
        }

        if (! $this->guard->mayChange($zone, $target)) {
            throw new AccessDeniedHttpException();
        }

        $actor = $this->context->actor();

        match (AccountAction::from($action)) {
            AccountAction::ResetPin => $this->administration->resetPin($target, $actor),
            AccountAction::Unlock => $this->administration->unlock($target, $actor),
            AccountAction::Deactivate => $this->administration->deactivate($target, $actor),
        };

        return $back->with('status', text('admiro::admin.action_done.' . $action));
    }
}

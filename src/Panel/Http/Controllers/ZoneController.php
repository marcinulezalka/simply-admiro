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

namespace Simplysmart\Admiro\Panel\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\AccountAction;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\Panel\Http\ManagedAccounts;
use Simplysmart\Mainboard\Queries\Accounts\ZoneAccounts;

/**
 * Strefy z kontami i konta wybranej strefy (wyszukiwanie po dokładnym adresie e-mail).
 */
final readonly class ZoneController
{
    private const int PER_PAGE = 25;

    public function __construct(
        private ManagedAccounts $managed,
        private AdminContext $context,
    ) {}

    public function index(): View
    {
        return view('admiro::panel.zones.index', ['zones' => $this->managed->zones()]);
    }

    public function accounts(Request $request, string $managedZone, ZoneAccounts $accounts): View
    {
        $zone = $this->managed->zone($managedZone);
        $email = $request->query('email');

        return view('admiro::panel.zones.accounts', [
            'managedZone' => $zone,
            'accounts' => $accounts->page($zone, is_string($email) ? $email : null, self::PER_PAGE),
            'email' => is_string($email) ? $email : '',
            'actions' => $this->context->can(Permission::AccountsManage) ? AccountAction::cases() : [],
        ]);
    }
}

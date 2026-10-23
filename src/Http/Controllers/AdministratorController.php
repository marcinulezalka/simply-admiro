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

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Http\AdminContext;
use Simplysmart\Admiro\Http\ManagedAccounts;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Models\ZoneAccount;
use Simplysmart\Mainboard\Permissions\RoleAssignmentDenied;
use Simplysmart\Mainboard\Permissions\SystemRoleGrant;
use Simplysmart\Mainboard\Permissions\ZoneRoleAssignment;
use Simplysmart\Mainboard\Queries\Accounts\ZoneAccounts;
use Simplysmart\Mainboard\Queries\Permissions\ZoneRoleNames;

/**
 * Administratorzy i ich role (`users.manage`), w organizacji operatora: nadawanie i odbieranie ról przez
 * ZoneRoleAssignment (bez eskalacji: nie nada się roli szerszej niż własne uprawnienia); własnych ról nie
 * zmienia się tu.
 */
final readonly class AdministratorController
{
    private const int PER_PAGE = 25;

    public function __construct(
        private AdminContext $context,
        private ManagedAccounts $managed,
        private ZoneRoleNames $roles,
        private ZoneRoleAssignment $assignment,
        private Redirector $redirect,
        private SystemRoleGrant $grants,
    ) {}

    public function index(ZoneAccounts $accounts): View
    {
        $zone = $this->context->zone();
        $page = $accounts->page($zone, null, self::PER_PAGE);
        $roles = [];

        foreach ($page->items() as $account) {
            $token = $account->getRouteKey();

            if ($account instanceof ZoneAccount && is_string($token)) {
                $roles[$token] = $this->roles->of($account, Organization::OPERATOR_ID);
            }
        }

        return view('admiro::administrators.index', [
            'administrators' => $page,
            'roles' => $roles,
            'availableRoles' => $this->roles->available($zone),
            'self' => $this->context->admin(),
        ]);
    }

    public function assign(Request $request, string $account): RedirectResponse
    {
        $validated = $request->validate(['role' => ['required', 'string', 'max:255']]);

        return $this->change($account, is_string($validated['role']) ? $validated['role'] : '', assign: true);
    }

    public function revoke(string $account, string $role): RedirectResponse
    {
        return $this->change($account, $role, assign: false);
    }

    private function change(string $token, string $role, bool $assign): RedirectResponse
    {
        $target = $this->managed->account($this->context->zone(), $token);
        $back = $this->redirect->route($this->context->zone()->routeName('administrators.index'));
        $admin = $this->context->admin();

        if ($target->is($admin)) {
            return $back->withErrors(['role' => text('admiro::admin.own_account')]);
        }

        $operator = $this->grants->organizationFor($this->context->zone());

        try {
            $assign
                ? $this->assignment->assign($admin, $target, $role, $operator)
                : $this->assignment->revoke($admin, $target, $role, $operator);
        } catch (RoleAssignmentDenied) {
            return $back->withErrors(['role' => text('admiro::admin.role_denied')]);
        }

        return $back->with('status', text($assign ? 'admiro::admin.role_assigned' : 'admiro::admin.role_revoked'));
    }
}

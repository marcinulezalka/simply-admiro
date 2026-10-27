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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\Panel\Http\ManagedAccounts;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Permissions\RoleAssignmentDenied;
use Simplysmart\Admiro\Permissions\SystemRoleGrant;
use Simplysmart\Admiro\Permissions\ZoneRoleAssignment;
use Simplysmart\Admiro\Queries\Accounts\ZoneAccounts;
use Simplysmart\Admiro\Queries\Permissions\ZoneRoleNames;

/**
 * Administratorzy i ich role (`users.manage`), w organizacji operatora: nadawanie, odbieranie i przełączanie ról
 * przez ZoneRoleAssignment (bez eskalacji: nie nada się roli szerszej niż własne uprawnienia); własnych ról nie
 * zmienia się tu. Po zmianie wraca na stronę, z której przyszło żądanie (lista administratorów albo kreator).
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

        return view('admiro::panel.administrators.index', [
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

    /**
     * Przełącznik roli (np. w kreatorze onboardingu): nadaje rolę, której konto nie ma, a odbiera tę, którą ma;
     * te same zasady co assign() i revoke().
     */
    public function toggle(string $account, string $role): RedirectResponse
    {
        $target = $this->managed->account($this->context->zone(), $account);

        return $this->change($account, $role, assign: ! in_array($role, $this->roles->of($target, Organization::OPERATOR_ID), true));
    }

    private function change(string $token, string $role, bool $assign): RedirectResponse
    {
        $target = $this->managed->account($this->context->zone(), $token);
        $back = $this->redirect->back(fallback: route($this->context->zone()->routeName('administrators.index')));
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

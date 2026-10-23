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
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Enums\Permission;
use Simplysmart\Admiro\Http\AdminAudit;
use Simplysmart\Admiro\Http\AdminContext;
use Simplysmart\Admiro\Http\AdministratorGuard;
use Simplysmart\Admiro\Http\ManagedAccounts;
use Simplysmart\Mainboard\Auth\AccountKey;
use Simplysmart\Mainboard\Permissions\OrganizationMembership;
use Simplysmart\Mainboard\Queries\Organizations\OrganizationList;
use Simplysmart\Mainboard\Queries\Organizations\OrganizationMembers;
use Simplysmart\Mainboard\Queries\Permissions\ZoneRoleNames;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Członkowie organizacji we wszystkich strefach z ich rolami (`organizations.view`) i usuwanie członka
 * (`organizations.manage`, z ochroną innych administratorów); usunięcie trafia do dziennika.
 */
final readonly class OrganizationMemberController
{
    public function __construct(
        private OrganizationList $organizations,
        private AdminContext $context,
        private AdminAudit $audit,
        private Redirector $redirect,
    ) {}

    public function index(string $organization, OrganizationMembers $members, ZoneRoleNames $roles): View
    {
        $current = $this->organizations->find($organization) ?? throw new NotFoundHttpException();
        $list = [];

        foreach ($members->all($current) as $member) {
            $list[] = [...$member, 'roles' => $roles->of($member['account'], $current->id)];
        }

        return view('admiro::organizations.members', [
            'organization' => $current,
            'members' => $list,
            'canManage' => $this->context->can(Permission::OrganizationsManage),
        ]);
    }

    public function remove(
        string $organization,
        string $managedZone,
        string $account,
        ManagedAccounts $managed,
        OrganizationMembers $members,
        OrganizationMembership $membership,
        AdministratorGuard $guard,
    ): RedirectResponse {
        $current = $this->organizations->find($organization) ?? throw new NotFoundHttpException();
        $zone = $managed->zone($managedZone);
        $member = $members->find($current, $zone, $account) ?? throw new NotFoundHttpException();
        $back = $this->redirect->route($this->context->zone()->routeName('organizations.members'), ['organization' => $organization]);

        if ($guard->isOwnAccount($member)) {
            return $back->withErrors(['member' => text('admiro::admin.own_account')]);
        }

        if (! $guard->mayChange($zone, $member)) {
            throw new AccessDeniedHttpException();
        }

        $membership->remove($member, $current, $this->context->actor());
        $this->audit->record('member removed', $zone->name, AccountKey::of($member), $current->id);

        return $back->with('status', text('admiro::admin.member_removed'));
    }
}

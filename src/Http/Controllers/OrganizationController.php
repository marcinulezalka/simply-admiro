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
use Simplysmart\Admiro\Enums\Permission;
use Simplysmart\Admiro\Http\AdminAudit;
use Simplysmart\Admiro\Http\AdminContext;
use Simplysmart\Admiro\Http\AdministratorGuard;
use Simplysmart\Admiro\Http\ManagedAccounts;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Organizations\OrganizationChangeRejected;
use Simplysmart\Mainboard\Organizations\OrganizationManagement;
use Simplysmart\Mainboard\Permissions\OrganizationMembership;
use Simplysmart\Mainboard\Queries\Organizations\ActiveOrganizations;
use Simplysmart\Mainboard\Queries\Organizations\OrganizationList;
use Simplysmart\Mainboard\Queries\Organizations\OrganizationMembers;
use Simplysmart\Mainboard\Queries\Permissions\ZoneRoleNames;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Organizacje (firmy i oddziały): lista i członkowie we wszystkich strefach (`organizations.view`), zakładanie,
 * zmiany, włączanie i wyłączanie oraz usuwanie członków (`organizations.manage`). Każda zmiana trafia do dziennika.
 */
final readonly class OrganizationController
{
    public function __construct(
        private OrganizationList $organizations,
        private OrganizationManagement $management,
        private AdminContext $context,
        private AdminAudit $audit,
        private Redirector $redirect,
    ) {}

    public function index(): View
    {
        $organizations = $this->organizations->all();

        return view('admiro::organizations.index', [
            'organizations' => $organizations,
            'names' => $organizations->pluck(ColumnNames::NAME, ColumnNames::ID)->all(),
            'canManage' => $this->context->can(Permission::OrganizationsManage),
        ]);
    }

    public function create(ActiveOrganizations $active): View
    {
        return view('admiro::organizations.form', ['organization' => null, 'parents' => $active->all()]);
    }

    public function store(Request $request, ActiveOrganizations $active): RedirectResponse
    {
        [$name, $parent] = $this->validated($request, $active);

        try {
            $organization = $this->management->create($name, $parent);
        } catch (OrganizationChangeRejected) {
            return $this->rejected();
        }

        $this->audit->record('organization created', ['organization_id' => $organization->id]);

        return $this->toIndex('organization_created');
    }

    public function edit(string $organization, ActiveOrganizations $active): View
    {
        $current = $this->organization($organization);

        return view('admiro::organizations.form', [
            'organization' => $current,
            'parents' => $active->all()->reject(static fn(Organization $parent): bool => $parent->id === $current->id),
        ]);
    }

    public function update(Request $request, string $organization, ActiveOrganizations $active): RedirectResponse
    {
        $current = $this->organization($organization);
        [$name, $parent] = $this->validated($request, $active);

        try {
            $this->management->update($current, $name, $parent);
        } catch (OrganizationChangeRejected) {
            return $this->rejected();
        }

        $this->audit->record('organization updated', ['organization_id' => $current->id]);

        return $this->toIndex('organization_updated');
    }

    public function toggle(string $organization): RedirectResponse
    {
        $current = $this->organization($organization);
        $active = $current->getAttribute(ColumnNames::IS_ACTIVE) !== true;

        try {
            $this->management->setActive($current, $active);
        } catch (OrganizationChangeRejected) {
            return $this->rejected();
        }

        $this->audit->record($active ? 'organization enabled' : 'organization disabled', ['organization_id' => $current->id]);

        return $this->toIndex($active ? 'organization_enabled' : 'organization_disabled');
    }

    public function members(string $organization, OrganizationMembers $members, ZoneRoleNames $roles): View
    {
        $current = $this->organization($organization);
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

    public function removeMember(
        string $organization,
        string $managedZone,
        string $account,
        ManagedAccounts $managed,
        OrganizationMembers $members,
        OrganizationMembership $membership,
        AdministratorGuard $guard,
    ): RedirectResponse {
        $current = $this->organization($organization);
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
        $this->audit->record('member removed', ['organization_id' => $current->id, 'zone' => $zone->name, 'account_id' => $member->getKey()]);

        return $back->with('status', text('admiro::admin.member_removed'));
    }

    /**
     * @return array{string, Organization|null}
     */
    private function validated(Request $request, ActiveOrganizations $active): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'parent' => ['nullable', 'string', 'max:' . Organization::TOKEN_LENGTH],
        ]);
        $parent = is_string($validated['parent'] ?? null) ? $active->find($validated['parent']) : null;

        return [is_string($validated['name']) ? $validated['name'] : '', $parent];
    }

    private function organization(string $token): Organization
    {
        return $this->organizations->find($token) ?? throw new NotFoundHttpException();
    }

    private function rejected(): RedirectResponse
    {
        return $this->redirect->back()->withInput()->withErrors(['organization' => text('admiro::admin.organization_rejected')]);
    }

    private function toIndex(string $message): RedirectResponse
    {
        return $this->redirect->route($this->context->zone()->routeName('organizations.index'))->with('status', text('admiro::admin.' . $message));
    }
}

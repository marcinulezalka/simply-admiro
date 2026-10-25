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
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\AdminAudit;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Enums\BrandAsset;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Organizations\OrganizationBranding;
use Simplysmart\Mainboard\Organizations\OrganizationChangeRejected;
use Simplysmart\Mainboard\Organizations\OrganizationData;
use Simplysmart\Mainboard\Organizations\OrganizationForm;
use Simplysmart\Mainboard\Organizations\OrganizationManagement;
use Simplysmart\Mainboard\Queries\Organizations\ActiveOrganizations;
use Simplysmart\Mainboard\Queries\Organizations\OrganizationList;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Organizacje (firmy i oddziały): lista (`organizations.view`), zakładanie, zmiany, włączanie i wyłączanie
 * (`organizations.manage`). Każda zmiana trafia do dziennika; członków obsługuje OrganizationMemberController,
 * a markę (logo, sygnet, ikona strony; karta w formularzu edycji) OrganizationBrandController.
 */
final readonly class OrganizationController
{
    public function __construct(
        private OrganizationList $organizations,
        private OrganizationManagement $management,
        private OrganizationForm $form,
        private AdminContext $context,
        private AdminAudit $audit,
        private Redirector $redirect,
    ) {}

    public function index(): View
    {
        $organizations = $this->organizations->all();

        return view('admiro::panel.organizations.index', [
            'organizations' => $organizations,
            'names' => $organizations->pluck(ColumnNames::NAME, ColumnNames::ID)->all(),
            'canManage' => $this->context->can(Permission::OrganizationsManage),
        ]);
    }

    public function create(ActiveOrganizations $active): View
    {
        return view('admiro::panel.organizations.form', ['organization' => null, 'parents' => $active->all()]);
    }

    public function store(Request $request, ActiveOrganizations $active): RedirectResponse
    {
        [$data, $parent] = $this->validated($request, $active);

        try {
            $organization = $this->management->create($data, $parent);
        } catch (OrganizationChangeRejected) {
            return $this->rejected();
        }

        $this->audit->record('organization created', organizationId: $organization->id);

        return $this->toIndex('organization_created');
    }

    public function edit(string $organization, ActiveOrganizations $active, OrganizationBranding $branding): View
    {
        $current = $this->organization($organization);

        return view('admiro::panel.organizations.form', [
            'organization' => $current,
            'parents' => $active->all()->reject(static fn(Organization $parent): bool => $parent->id === $current->id),
            'brandAssets' => array_map(static fn(BrandAsset $asset): array => ['asset' => $asset, 'set' => $branding->has($current, $asset)], BrandAsset::cases()),
        ]);
    }

    public function update(Request $request, string $organization, ActiveOrganizations $active): RedirectResponse
    {
        $current = $this->organization($organization);
        [$data, $parent] = $this->validated($request, $active);

        try {
            $this->management->update($current, $data, $parent);
        } catch (OrganizationChangeRejected) {
            return $this->rejected();
        }

        $this->audit->record('organization updated', organizationId: $current->id);

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

        $this->audit->record($active ? 'organization enabled' : 'organization disabled', organizationId: $current->id);

        return $this->toIndex($active ? 'organization_enabled' : 'organization_disabled');
    }

    /**
     * Dane organizacji (Organizations\OrganizationForm) i organizacja nadrzędna w jednej walidacji.
     *
     * @return array{OrganizationData, Organization|null}
     */
    private function validated(Request $request, ActiveOrganizations $active): array
    {
        $validated = $request->validate([
            ...$this->form->rules(),
            'parent' => ['nullable', 'string', 'max:' . Organization::TOKEN_LENGTH],
        ]);
        $parent = is_string($validated['parent'] ?? null) ? $active->find($validated['parent']) : null;

        return [$this->form->data($validated), $parent];
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

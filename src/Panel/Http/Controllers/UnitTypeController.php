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
use Illuminate\Validation\Rule;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Models\OrganizationUnitType;
use Simplysmart\Admiro\Organizations\OrganizationUnitTypeManagement;
use Simplysmart\Admiro\Panel\Enums\Permission;
use Simplysmart\Admiro\Panel\Http\AdminAudit;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\Queries\Organizations\OrganizationUnitTypeList;

/**
 * Słownik typów jednostek organizacyjnych (`settings.view`): lista z prefiksem, ikoną i znacznikami, a z
 * `settings.manage` dodanie, zmiana i włączenie/wyłączenie typu (OrganizationUnitTypeManagement, dziennik panelu).
 */
final readonly class UnitTypeController
{
    public function __construct(
        private AdminContext $context,
        private Redirector $redirect,
    ) {}

    public function index(OrganizationUnitTypeList $types): View
    {
        return view('admiro::panel.dictionaries.unit-types', [
            'types' => $types->all(),
            'icons' => Organization::ICONS,
            'canManage' => $this->context->can(Permission::SettingsManage),
        ]);
    }

    public function store(Request $request, OrganizationUnitTypeManagement $management, AdminAudit $audit): RedirectResponse
    {
        $type = $management->create($this->validated($request));
        $audit->record('unit type saved', context: ['unit_type_id' => $type->id]);

        return $this->back()->with('status', text('admiro::admin.unit_types.saved'));
    }

    public function update(Request $request, OrganizationUnitType $unitType, OrganizationUnitTypeManagement $management, AdminAudit $audit): RedirectResponse
    {
        $management->update($unitType, $this->validated($request));
        $audit->record('unit type saved', context: ['unit_type_id' => $unitType->id]);

        return $this->back()->with('status', text('admiro::admin.unit_types.saved'));
    }

    public function toggle(OrganizationUnitType $unitType, OrganizationUnitTypeManagement $management, AdminAudit $audit): RedirectResponse
    {
        $management->setActive($unitType, ! $unitType->getAttribute(ColumnNames::IS_ACTIVE));
        $audit->record('unit type saved', context: ['unit_type_id' => $unitType->id]);

        return $this->back()->with('status', text('admiro::admin.unit_types.saved'));
    }

    /**
     * @return array{name: string, number_prefix: string, icon: string, is_internal: bool, is_public: bool}
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'number_prefix' => ['nullable', 'string', 'max:' . OrganizationUnitType::PREFIX_MAX_LENGTH, 'regex:/^[A-Za-z0-9\/.-]*$/'],
            'icon' => ['required', Rule::in(Organization::ICONS)],
        ]);

        return [
            'name' => is_string($validated['name']) ? $validated['name'] : '',
            'number_prefix' => is_string($validated['number_prefix'] ?? null) ? strtoupper($validated['number_prefix']) : '',
            'icon' => is_string($validated['icon']) ? $validated['icon'] : Organization::ICONS[0],
            'is_internal' => $request->boolean('is_internal'),
            'is_public' => $request->boolean('is_public'),
        ];
    }

    private function back(): RedirectResponse
    {
        return $this->redirect->route($this->context->zone()->routeName('unit-types.index'));
    }
}

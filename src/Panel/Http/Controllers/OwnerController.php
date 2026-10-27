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
use LogicException;
use Simplysmart\Admiro\Panel\Http\AdminAudit;
use Simplysmart\Admiro\Panel\Http\AdminContext;
use Simplysmart\Admiro\Contracts\ZoneRegistry;
use Simplysmart\Admiro\Permissions\OwnerData;
use Simplysmart\Admiro\Permissions\ZoneOwnerProvisioners;
use Simplysmart\Admiro\Queries\Organizations\ActiveOrganizations;

/**
 * Zakładanie właściciela strefy (`owners.assign`) przez kontrakt strefy docelowej: panel nie zna jej klas
 * ani nie współdzieli z nią kont. Każde założenie trafia do dziennika logowań z aktorem.
 */
final readonly class OwnerController
{
    public function __construct(
        private ZoneOwnerProvisioners $provisioners,
        private ZoneRegistry $zones,
        private ActiveOrganizations $organizations,
        private AdminContext $context,
        private Redirector $redirect,
    ) {}

    public function create(Request $request): View
    {
        return view('admiro::panel.owners.create', [
            'zones' => array_map($this->zones->get(...), $this->provisioners->zones()),
            'organizations' => $this->organizations->all(),
            'selectedOrganization' => $request->string('organization')->toString(),
        ]);
    }

    public function store(Request $request, AdminAudit $audit): RedirectResponse
    {
        $validated = $request->validate([
            'zone' => ['required', 'string', Rule::in($this->provisioners->zones())],
            'organization' => ['nullable', 'string', 'max:64'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email:rfc', 'max:255'],
        ]);
        $zone = is_string($validated['zone']) ? $validated['zone'] : '';
        $token = $validated['organization'] ?? null;
        $organization = is_string($token) && $token !== '' ? $this->organizations->find($token) : null;

        try {
            $owner = new OwnerData(is_string($validated['name']) ? $validated['name'] : '', is_string($validated['email']) ? $validated['email'] : '');
            $account = $this->provisioners->for($zone)->provisionOwner($owner, $organization);
        } catch (LogicException) {
            return $this->redirect->back()->withInput()->withErrors(['organization' => text('admiro::admin.owner_organization_required')]);
        }

        $audit->record('owner provisioned', $zone, organizationId: $organization?->id, context: ['account_token' => $account]);

        return $this->redirect->route($this->context->zone()->routeName('owners.create'))->with('status', text('admiro::admin.owner_created'));
    }
}

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

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Redirector;
use Simplysmart\Admiro\Http\AdminAudit;
use Simplysmart\Admiro\Http\AdminContext;
use Simplysmart\Mainboard\Database\ColumnNames;
use Simplysmart\Mainboard\Enums\BrandAsset;
use Simplysmart\Mainboard\Models\Organization;
use Simplysmart\Mainboard\Organizations\OrganizationBranding;
use Simplysmart\Mainboard\Organizations\OrganizationChangeRejected;
use Simplysmart\Mainboard\Queries\Organizations\OrganizationList;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Marka organizacji (`organizations.manage`): podgląd, zapis i usunięcie logo, sygnetu i ikony strony. Logo trafia
 * do layoutu e-maili organizacji; każda zmiana idzie do dziennika.
 */
final readonly class OrganizationBrandController
{
    private const string FILE = 'file';

    public function __construct(
        private OrganizationList $organizations,
        private OrganizationBranding $branding,
        private AdminContext $context,
        private AdminAudit $audit,
        private Redirector $redirect,
        private ResponseFactory $responses,
    ) {}

    public function show(string $organization, string $asset): Response
    {
        $file = $this->branding->file($this->organization($organization), BrandAsset::from($asset)) ?? throw new NotFoundHttpException();

        return $this->responses->make($file->contents, 200, [
            'Content-Type' => $file->mimeType,
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function store(Request $request, string $organization, string $asset): RedirectResponse
    {
        $current = $this->organization($organization);
        $brandAsset = BrandAsset::from($asset);
        $file = $request->validate([
            self::FILE => ['required', 'file', 'mimes:' . implode(',', $brandAsset->extensions()), 'max:' . $brandAsset->maxKilobytes()],
        ])[self::FILE];

        if (! $file instanceof UploadedFile) {
            return $this->rejected();
        }

        try {
            $this->branding->store($current, $brandAsset, $file);
        } catch (OrganizationChangeRejected) {
            return $this->rejected();
        }

        $this->audit->record('organization brand changed', organizationId: $current->id, context: ['asset' => $brandAsset->value]);

        return $this->toEdit($current, 'organization_brand_saved');
    }

    public function destroy(string $organization, string $asset): RedirectResponse
    {
        $current = $this->organization($organization);
        $brandAsset = BrandAsset::from($asset);

        $this->branding->remove($current, $brandAsset);
        $this->audit->record('organization brand removed', organizationId: $current->id, context: ['asset' => $brandAsset->value]);

        return $this->toEdit($current, 'organization_brand_removed');
    }

    private function organization(string $token): Organization
    {
        return $this->organizations->find($token) ?? throw new NotFoundHttpException();
    }

    private function rejected(): RedirectResponse
    {
        return $this->redirect->back()->withErrors([self::FILE => text('admiro::admin.organization_brand_rejected')]);
    }

    private function toEdit(Organization $organization, string $message): RedirectResponse
    {
        return $this->redirect->route($this->context->zone()->routeName('organizations.edit'), ['organization' => $organization->getAttribute(ColumnNames::TOKEN)])
            ->with('status', text('admiro::admin.' . $message));
    }
}

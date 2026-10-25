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

namespace Simplysmart\Admiro\Mail;

use Simplysmart\Admiro\Enums\BrandAsset;
use Simplysmart\Admiro\Models\Organization;
use Simplysmart\Admiro\Organizations\OrganizationBranding;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Logo w layoucie e-maila: organizacji (panel), strefy (`brand.logo`), a na końcu Core (simplysmart).
 */
final readonly class MailBrandResolver
{
    public const string CORE_LOGO = __DIR__ . '/../../public/assets/img/brand/mail-logo.png';

    public function __construct(
        private OrganizationBranding $branding,
    ) {}

    public function logo(ZoneDefinition $zone, ?Organization $organization): BrandFile
    {
        $organizationLogo = $organization === null ? null : $this->branding->file($organization, BrandAsset::Logo);

        if ($organizationLogo !== null) {
            return $organizationLogo;
        }

        $zoneLogo = $zone->brand?->path(BrandAsset::Logo);

        return BrandFile::fromPath($zoneLogo ?? self::CORE_LOGO);
    }
}

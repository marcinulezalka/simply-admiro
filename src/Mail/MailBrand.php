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

/**
 * Marka z grupy logotypów w stopce e-maila strefy (MailBrandGroups): nazwa (tekst alternatywny) i plik logo.
 */
final readonly class MailBrand
{
    public function __construct(
        public string $name,
        public BrandFile $logo,
    ) {}
}

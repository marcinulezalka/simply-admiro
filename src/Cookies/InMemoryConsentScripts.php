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

namespace Simplysmart\Admiro\Cookies;

use Simplysmart\Admiro\Contracts\ConsentScripts;

/**
 * Skrypty opcjonalne w pamięci procesu, rejestrowane przez providery przy starcie (bez powtórzeń adresu).
 */
final class InMemoryConsentScripts implements ConsentScripts
{
    /**
     * @var array<string, ConsentScript>
     */
    private array $scripts = [];

    public function add(ConsentScript $script): void
    {
        $this->scripts[$script->src] = $script;
    }

    public function all(): array
    {
        return array_values($this->scripts);
    }
}

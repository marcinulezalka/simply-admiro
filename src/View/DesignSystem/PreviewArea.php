<?php

declare(strict_types=1);

/**
 * This file is part of simplysmart/mainboard.
 *
 * Copyright (c) 2026 Marcin Ulezalka <marcin.ulezalka@simplysmart.pl>
 *
 * Licensed under the Simplysmart Restricted Source License 1.0.
 * See the LICENSE file distributed with this source code.
 */

namespace Simplysmart\Mainboard\View\DesignSystem;

/**
 * Obszar katalogu design systemu (grupa w nawigacji) z widokami w kolejności rejestracji.
 */
final readonly class PreviewArea
{
    /**
     * @param list<PreviewView> $views
     */
    public function __construct(
        public string $key,
        public string $labelKey,
        public array $views,
    ) {}

    public function label(): string
    {
        return text($this->labelKey);
    }

    public function contains(?string $viewKey): bool
    {
        foreach ($this->views as $view) {
            if ($view->key === $viewKey) {
                return true;
            }
        }

        return false;
    }
}

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

namespace Simplysmart\Mainboard\Enums;

/**
 * Obszar, którego dotyczą gotowe widoki design systemu; grupuje je w nawigacji (kolejność przypadków = kolejność
 * grup). Widok wskazuje obszar w DesignSystemView::area(), więc nowy widok sam trafia do swojej grupy.
 */
enum DesignSystemArea: string
{
    case Authorization = 'authorization';
    case Workspace = 'workspace';
    case Pages = 'pages';
    case Errors = 'errors';

    public function labelKey(): string
    {
        return 'design_system.areas.' . $this->value;
    }

    public function label(): string
    {
        return text($this->labelKey());
    }

    /**
     * @return list<DesignSystemView>
     */
    public function views(): array
    {
        return array_values(array_filter(DesignSystemView::cases(), fn(DesignSystemView $view): bool => $view->area() === $this));
    }
}

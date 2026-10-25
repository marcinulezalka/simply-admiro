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

namespace Simplysmart\Admiro\Enums;

use Simplysmart\Admiro\Contracts\HasLabel;

/**
 * Decyzja odwiedzającego w banerze cookies: zgoda na wszystkie kategorie, odrzucenie (tylko niezbędne) albo wybór
 * kategorii w ustawieniach.
 */
enum ConsentDecision: string implements HasLabel
{
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Customized = 'customized';

    public function label(): string
    {
        return text('cookies.decisions.' . $this->value);
    }
}

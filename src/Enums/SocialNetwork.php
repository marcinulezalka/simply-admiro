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
 * Serwis społecznościowy w odnośnikach strefy (`social_links` w configu strefy: wartość → adres profilu), pokazywany
 * jako okrągła ikona na karcie konta layoutu Workspace. Ikony Lucide w `public/assets/img/icons/`, etykiety:
 * `enums.social_network.<value>`.
 */
enum SocialNetwork: string implements HasLabel
{
    case LinkedIn = 'linkedin';
    case Facebook = 'facebook';
    case Instagram = 'instagram';
    case GitHub = 'github';
    case YouTube = 'youtube';

    public function icon(): string
    {
        return $this->value;
    }

    public function label(): string
    {
        return text('enums.social_network.' . $this->value);
    }
}

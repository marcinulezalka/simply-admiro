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

namespace Simplysmart\Mainboard\Models\Dictionaries;

use Simplysmart\Mainboard\Models\Concerns\HasI18n;
use Simplysmart\Mainboard\Models\DataModel;
use Simplysmart\Mainboard\Models\Relations\SpokenInCountries;

/**
 * Język (ISO 639, małe litery). `is_active` — do wyboru w treściach i dokumentach, `is_interface` —
 * język interfejsu (tylko w granicach APP_LOCALES), `is_default` — język aplikacji (jeden).
 *
 * @property string $code
 * @property string|null $native_name
 * @property bool $is_default
 * @property bool $is_interface
 */
final class Language extends DataModel
{
    use HasI18n;
    use SpokenInCountries;

    protected $table = 'dict_languages';

    /**
     * Wartości domyślne kolumn jak w migracji.
     *
     * @var array<string, mixed>
     */
    protected $attributes = ['is_default' => false, 'is_interface' => false];

    public function translationFallback(): string
    {
        return $this->code;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['code' => 'string', 'native_name' => 'string', 'is_default' => 'boolean', 'is_interface' => 'boolean'];
    }
}

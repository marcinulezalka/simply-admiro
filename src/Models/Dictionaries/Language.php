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

namespace Simplysmart\Admiro\Models\Dictionaries;

use Simplysmart\Admiro\Contracts\DictionaryEntry;
use Simplysmart\Admiro\Models\Concerns\HasI18n;
use Simplysmart\Admiro\Models\DataModel;
use Simplysmart\Admiro\Models\Relations\SpokenInCountries;

/**
 * Język (ISO 639, małe litery). `is_active` — do wyboru w treściach i dokumentach, `is_interface` —
 * język interfejsu (tylko w granicach APP_LOCALES), `is_default` — język aplikacji (jeden), `flag_icon` —
 * kod ikony flagi kraju kojarzonego z językiem (`en` → `gb`).
 *
 * @property string $code
 * @property string|null $native_name
 * @property bool $is_interface
 * @property string|null $flag_icon
 */
final class Language extends DataModel implements DictionaryEntry
{
    use HasI18n;
    use SpokenInCountries;

    public const string CODE = 'code';

    protected $table = 'locale_languages';

    /**
     * Wartości domyślne kolumn jak w migracji.
     *
     * @var array<string, mixed>
     */
    protected $attributes = ['is_interface' => false];

    /**
     * Kody języków mieszają ISO 639-1, -2 i -3 (`pl`, `gsw`, `fil`), więc kolumna to po prostu `code`.
     */
    public static function codeColumn(): string
    {
        return self::CODE;
    }

    public function translationFallback(): string
    {
        return $this->code;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [self::CODE => 'string', 'native_name' => 'string', 'is_interface' => 'boolean', 'flag_icon' => 'string'];
    }
}

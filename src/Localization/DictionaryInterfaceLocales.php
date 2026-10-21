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

namespace Simplysmart\Mainboard\Localization;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Database\DatabaseManager;
use Simplysmart\Mainboard\Contracts\InterfaceLocales;
use Simplysmart\Mainboard\Models\Dictionaries\Language;

/**
 * Języki interfejsu ze słownika (`dict_languages.is_interface`, domyślny `is_default`), zawsze w granicach
 * języków tras z configu — trasy (i route:cache) nie zależą od bazy. Bez słownika albo bez żadnego
 * języka interfejsu w granicach configu obowiązuje sam config. Wynik jest zapamiętany w cache.
 */
final readonly class DictionaryInterfaceLocales implements InterfaceLocales
{
    public const string CACHE_KEY = 'mainboard:localization:interface';

    private const int CACHE_SECONDS = 300;

    public function __construct(
        private LocaleRouting $routing,
        private Cache $cache,
        private DatabaseManager $connections,
    ) {}

    public function enabled(): array
    {
        return $this->resolved()['enabled'];
    }

    public function default(): string
    {
        return $this->resolved()['default'];
    }

    public function isEnabled(string $locale): bool
    {
        return in_array($locale, $this->enabled(), true);
    }

    public function forget(): void
    {
        $this->cache->forget(self::CACHE_KEY);
    }

    /**
     * @return array{enabled: list<string>, default: string}
     */
    private function resolved(): array
    {
        $cached = $this->cache->get(self::CACHE_KEY);

        if (is_array($cached) && isset($cached['enabled'], $cached['default']) && is_array($cached['enabled']) && is_string($cached['default'])) {
            return ['enabled' => array_values(array_filter($cached['enabled'], is_string(...))), 'default' => $cached['default']];
        }

        $resolved = $this->fromDictionary();
        $this->cache->put(self::CACHE_KEY, $resolved, self::CACHE_SECONDS);

        return $resolved;
    }

    /**
     * @return array{enabled: list<string>, default: string}
     */
    private function fromDictionary(): array
    {
        $ceiling = $this->routing->enabled();
        $fallback = ['enabled' => $ceiling, 'default' => $this->routing->default()];

        if (! $this->connections->connection()->getSchemaBuilder()->hasTable((new Language())->getTable())) {
            return $fallback;
        }

        $languages = Language::query()->where('is_interface', true)->get();
        $enabled = array_values(array_filter($ceiling, static fn(string $locale): bool => $languages->contains('code', $locale)));

        if ($enabled === []) {
            return $fallback;
        }

        $default = $languages->first(static fn(Language $language): bool => $language->is_default)?->code;

        return [
            'enabled' => $enabled,
            'default' => match (true) {
                is_string($default) && in_array($default, $enabled, true) => $default,
                in_array($fallback['default'], $enabled, true) => $fallback['default'],
                default => $enabled[0],
            },
        ];
    }
}

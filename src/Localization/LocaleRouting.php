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

use Illuminate\Config\Repository;

/**
 * Tryb języka tras: jeden włączony język → bez prefiksu; kilka → prefiks {locale} zawsze.
 */
final readonly class LocaleRouting
{
    public const string PARAMETER = 'locale';

    public function __construct(
        private Repository $config,
    ) {}

    /**
     * Włączone języki (mainboard.locale.enabled); pusta lista oznacza sam język aplikacji.
     *
     * @return list<string>
     */
    public function enabled(): array
    {
        $enabled = array_values(array_filter(
            $this->config->array('mainboard.locale.enabled', []),
            static fn(mixed $locale): bool => is_string($locale) && $locale !== '',
        ));

        return $enabled === [] ? [$this->config->string('app.locale')] : $enabled;
    }

    public function isPrefixed(): bool
    {
        return count($this->enabled()) > 1;
    }

    /**
     * Język domyślny: jedyny włączony albo język aplikacji, jeśli jest włączony.
     */
    public function default(): string
    {
        $enabled = $this->enabled();
        $application = $this->config->string('app.locale');

        return in_array($application, $enabled, true) ? $application : $enabled[0];
    }

    public function isEnabled(string $locale): bool
    {
        return in_array($locale, $this->enabled(), true);
    }

    /**
     * Wzorzec `where` parametru {locale}.
     */
    public function pattern(): string
    {
        return implode('|', array_map(static fn(string $locale): string => preg_quote($locale, '/'), $this->enabled()));
    }
}

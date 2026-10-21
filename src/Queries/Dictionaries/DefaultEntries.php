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

namespace Simplysmart\Mainboard\Queries\Dictionaries;

use Illuminate\Config\Repository;
use Simplysmart\Mainboard\Models\DataModel;
use Simplysmart\Mainboard\Models\Dictionaries\Country;
use Simplysmart\Mainboard\Models\Dictionaries\Currency;
use Simplysmart\Mainboard\Models\Dictionaries\Language;

/**
 * Kraj, język i waluta aplikacji: wpis słownika oznaczony `is_default`, a gdy go nie ma — wpis o kodzie
 * z configu (mainboard.localization.country, app.locale, mainboard.locale.currency); null bez słownika.
 */
final readonly class DefaultEntries
{
    private const string IS_DEFAULT = 'is_default';

    public function __construct(
        private Repository $config,
    ) {}

    public function country(): ?Country
    {
        return $this->find(Country::class, strtoupper($this->configured('mainboard.localization.country')));
    }

    public function language(): ?Language
    {
        return $this->find(Language::class, strtolower($this->configured('app.locale')));
    }

    public function currency(): ?Currency
    {
        return $this->find(Currency::class, strtoupper($this->configured('mainboard.locale.currency')));
    }

    /**
     * @template TModel of DataModel
     *
     * @param class-string<TModel> $model
     *
     * @return TModel|null
     */
    private function find(string $model, string $code): ?DataModel
    {
        return $model::query()->where(self::IS_DEFAULT, true)->first()
            ?? $model::query()->where('code', $code)->first();
    }

    private function configured(string $key): string
    {
        return $this->config->string($key);
    }
}

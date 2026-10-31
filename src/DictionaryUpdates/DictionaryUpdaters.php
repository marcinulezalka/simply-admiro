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

namespace Simplysmart\Admiro\DictionaryUpdates;

use Illuminate\Contracts\Container\Container;
use Simplysmart\Admiro\Contracts\DictionaryUpdater;

/**
 * Rejestr słowników aktualizowanych po zatwierdzeniu administratora (singleton): paczka dodaje w providerze klasę
 * Contracts\DictionaryUpdater, a panel administratora i zadanie aktualizacji znajdują ją po kluczu bez znajomości paczki.
 */
final class DictionaryUpdaters
{
    /**
     * @var list<class-string<DictionaryUpdater>>
     */
    private array $classes = [];

    public function __construct(
        private readonly Container $container,
    ) {}

    /**
     * @param class-string<DictionaryUpdater> $class
     */
    public function add(string $class): void
    {
        $this->classes[] = $class;
    }

    /**
     * @return list<DictionaryUpdater>
     */
    public function all(): array
    {
        return array_map(fn(string $class): DictionaryUpdater => $this->container->make($class), $this->classes);
    }

    public function find(string $key): ?DictionaryUpdater
    {
        foreach ($this->all() as $updater) {
            if ($updater->key() === $key) {
                return $updater;
            }
        }

        return null;
    }
}

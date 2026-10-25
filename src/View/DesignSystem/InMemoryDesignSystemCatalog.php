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

namespace Simplysmart\Admiro\View\DesignSystem;

use Closure;
use LogicException;
use Simplysmart\Admiro\Contracts\DesignSystemCatalog;

/**
 * Katalog design systemu w pamięci (singleton): obszary i widoki rejestrowane przez providery przy starcie.
 */
final class InMemoryDesignSystemCatalog implements DesignSystemCatalog
{
    /** @var array<string, string> Klucz obszaru → klucz tekstu etykiety. */
    private array $areas = [];

    /** @var array<string, PreviewView> */
    private array $views = [];

    public function addArea(string $key, string $labelKey): void
    {
        if (isset($this->areas[$key])) {
            throw new LogicException("Design system area [{$key}] is already registered.");
        }

        $this->areas[$key] = $labelKey;
    }

    public function addView(string $area, string $key, string $labelKey, string $template, ?Closure $data = null): void
    {
        if (! isset($this->areas[$area])) {
            throw new LogicException("Design system area [{$area}] is not registered.");
        }

        if (isset($this->views[$key])) {
            throw new LogicException("Design system view [{$key}] is already registered.");
        }

        $this->views[$key] = new PreviewView($area, $key, $labelKey, $template, $data);
    }

    public function areas(): array
    {
        $areas = [];

        foreach ($this->areas as $key => $labelKey) {
            $areas[] = new PreviewArea($key, $labelKey, array_values(array_filter($this->views, static fn(PreviewView $view): bool => $view->area === $key)));
        }

        return $areas;
    }

    public function find(string $key): ?PreviewView
    {
        return $this->views[$key] ?? null;
    }
}

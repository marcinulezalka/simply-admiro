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

namespace Simplysmart\Admiro\Mail;

/**
 * Rejestr grup logotypów w e-mailach stref (singleton): paczka dodaje w providerze markę do grupy strefy (nazwa, plik
 * logo PNG, pozycja — mniejsza wcześniej), a ZoneMailable przekazuje grupę layoutowi jako `$mailBrandGroup`. Strefa
 * bez wpisów ma pustą grupę, a layout jej nie pokazuje. Paczka może dopisać markę do grupy innej strefy (np. produkt,
 * który ta strefa obsługuje) bez zmiany jej konfiguracji.
 */
final class MailBrandGroups
{
    /**
     * @var array<string, array<string, array{logo: string, position: int}>>
     */
    private array $groups = [];

    public function add(string $zone, string $name, string $logoPath, int $position): void
    {
        $this->groups[$zone][$name] = ['logo' => $logoPath, 'position' => $position];
    }

    /**
     * Marki grupy strefy w kolejności pozycji; pliki są czytane dopiero tu (w chwili budowania maila).
     *
     * @return list<MailBrand>
     */
    public function for(string $zone): array
    {
        $group = $this->groups[$zone] ?? [];
        uasort($group, static fn(array $first, array $second): int => $first['position'] <=> $second['position']);
        $brands = [];

        foreach ($group as $name => $brand) {
            $brands[] = new MailBrand($name, BrandFile::fromPath($brand['logo']));
        }

        return $brands;
    }
}

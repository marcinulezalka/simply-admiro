<?php

declare(strict_types=1);

namespace Simplysmart\Mainboard\Contracts;

use InvalidArgumentException;
use Simplysmart\Mainboard\Zones\ZoneDefinition;

/**
 * Rejestr stref aplikacji: każda paczka strefy rejestruje tu swoją definicję.
 */
interface ZoneRegistry
{
    /**
     * Dodaje strefę.
     *
     * @throws InvalidArgumentException Gdy nazwa, guard, prefiks nazw tras lub slug koliduje z inną strefą.
     */
    public function register(ZoneDefinition $zone): void;

    /**
     * Strefa o podanej nazwie.
     *
     * @throws InvalidArgumentException Gdy takiej strefy nie zarejestrowano.
     */
    public function get(string $name): ZoneDefinition;

    /**
     * Czy strefa o podanej nazwie jest zarejestrowana.
     */
    public function has(string $name): bool;

    /**
     * Wszystkie strefy według nazwy.
     *
     * @return array<string, ZoneDefinition>
     */
    public function all(): array;
}

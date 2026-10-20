<?php

declare(strict_types=1);

namespace Simplysmart\Mainboard\Zones;

/**
 * Poziomy dostępu do tras strefy; każdy wyższy zawiera niższe (public ⊂ authenticated ⊂ uprawnienie).
 *
 * Uprawnienie nie jest osobnym poziomem: trasa poziomu Authenticated dokłada własne
 * `permission:<x>,<guard>`.
 */
enum AccessTier
{
    /** Każdy: niezalogowany odwiedzający i konto dowolnej strefy. */
    case Public;

    /** Tylko niezalogowani w tej strefie (logowanie); zalogowany trafia na dashboard. */
    case GuestOnly;

    /** Każde konto strefy, także z rolą bez uprawnień. */
    case Authenticated;

    /**
     * Middleware poziomu, z jawnie nazwanym guardem strefy.
     *
     * @return list<string>
     */
    public function middleware(ZoneDefinition $zone): array
    {
        return match ($this) {
            self::Public => [],
            self::GuestOnly => ['guest:' . $zone->guard],
            self::Authenticated => ['auth:' . $zone->guard],
        };
    }

    /**
     * Czy poziom wymaga strefy z logowaniem.
     */
    public function requiresLogin(): bool
    {
        return $this !== self::Public;
    }
}

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

namespace Simplysmart\Mainboard\Security\Honeypot;

use Illuminate\Contracts\Cache\Repository as Cache;
use Illuminate\Config\Repository as Config;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Zones\ZoneCacheKey;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * Bany IP z pułapek na boty: w cache tylko hash adresu (`zone:shared:honeypot:ban:<sha256>`), bez wspólnego
 * indeksu (brak wyścigu przy zapisie). Adresy z `mainboard.honeypot.whitelist` nigdy nie są banowane.
 */
final readonly class IpBans
{
    public const string CACHE_SCOPE = 'shared';

    public function __construct(
        private Cache $cache,
        private Config $config,
    ) {}

    /**
     * Banuje adres; zwraca koniec bana albo null dla adresu z białej listy.
     */
    public function ban(string $ip): ?Carbon
    {
        if ($this->isWhitelisted($ip)) {
            return null;
        }

        $until = Carbon::now()->addMinutes($this->config->integer('mainboard.honeypot.ban_minutes'));
        $this->cache->put(self::key('ban', $ip), $until->getTimestamp(), $until);

        return $until;
    }

    public function unban(string $ip): bool
    {
        return $this->cache->forget(self::key('ban', $ip));
    }

    public function isBanned(string $ip): bool
    {
        return ! $this->isWhitelisted($ip) && $this->cache->has(self::key('ban', $ip));
    }

    public function isWhitelisted(string $ip): bool
    {
        $whitelist = array_values(array_filter($this->config->array('mainboard.honeypot.whitelist'), is_string(...)));

        return $whitelist !== [] && IpUtils::checkIp($ip, $whitelist);
    }

    /**
     * Klucz cache dla adresu: zawsze z hashem, nigdy z jawnym IP.
     */
    public static function key(string $kind, string $ip): string
    {
        return ZoneCacheKey::make(self::CACHE_SCOPE, 'honeypot', $kind, hash('sha256', $ip));
    }
}

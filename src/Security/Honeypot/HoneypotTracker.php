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
use Illuminate\Http\Request;
use Illuminate\Log\LogManager;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Zapisuje trafienie w pułapkę (dziennik `mainboard.honeypot.log_channel`: powód, IP, ścieżka, user agent,
 * bez haseł, tylko ich długość) i banuje adres: od razu przy POST lub wypełnionym ukrytym polu, przy GET
 * dopiero po `get_hits_threshold` wejściach w oknie (przypadkowy człowiek nie traci dostępu).
 */
final readonly class HoneypotTracker
{
    private const int TEXT_LENGTH = 255;

    private const int USERNAME_LENGTH = 64;

    public function __construct(
        private IpBans $bans,
        private Cache $cache,
        private Config $config,
        private LogManager $logs,
    ) {}

    public function record(Request $request, HoneypotTrap $trap): void
    {
        $ip = (string) $request->ip();
        $log = $this->logs->channel($this->config->string('mainboard.honeypot.log_channel'));

        $log->info('honeypot hit', [
            'trap' => $trap->value,
            'ip' => $ip,
            'method' => $request->method(),
            'path' => '/' . ltrim($request->path(), '/'),
            'user_agent' => Str::limit((string) $request->userAgent(), self::TEXT_LENGTH, ''),
            'username' => $this->username($request),
            'password_length' => $this->passwordLength($request),
        ]);

        if (! $trap->bansImmediately() && $this->hits($ip) < $this->config->integer('mainboard.honeypot.get_hits_threshold')) {
            return;
        }

        $until = $this->bans->ban($ip);

        if ($until !== null) {
            $log->warning('honeypot ban', ['ip' => $ip, 'until' => $until->toIso8601String()]);
        }
    }

    private function hits(string $ip): int
    {
        $key = IpBans::key('hits', $ip);
        $this->cache->add($key, 0, Carbon::now()->addMinutes($this->config->integer('mainboard.honeypot.get_hits_window_minutes')));
        $hits = $this->cache->increment($key);

        return is_int($hits) ? $hits : 0;
    }

    private function username(Request $request): ?string
    {
        foreach (['log', 'username', 'email', 'user'] as $field) {
            $value = $request->input($field);

            if (is_string($value)) {
                return Str::limit($value, self::USERNAME_LENGTH, '');
            }
        }

        return null;
    }

    private function passwordLength(Request $request): ?int
    {
        foreach (['pwd', 'password', 'pin'] as $field) {
            $value = $request->input($field);

            if (is_string($value)) {
                return mb_strlen($value);
            }
        }

        return null;
    }
}

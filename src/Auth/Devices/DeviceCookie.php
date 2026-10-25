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

namespace Simplysmart\Admiro\Auth\Devices;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Cookie\QueueingFactory as CookieJar;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Simplysmart\Admiro\Zones\ZoneDefinition;

/**
 * Ciasteczko zaufanego urządzenia strefy `<strefa>_device`: szyfrowane (EncryptCookies w grupie strefy),
 * HttpOnly, SameSite=Strict, `secure`, ścieżka i domena jak sesja strefy, ważne do końca zaufania urządzenia.
 */
final readonly class DeviceCookie
{
    private const string SUFFIX = '_device';

    private const string SAME_SITE = 'strict';

    public function __construct(
        private CookieJar $cookies,
        private Repository $config,
    ) {}

    public static function name(ZoneDefinition $zone): string
    {
        return $zone->name . self::SUFFIX;
    }

    public function read(ZoneDefinition $zone, Request $request): ?DeviceToken
    {
        return DeviceToken::fromCookie($request->cookie(self::name($zone)));
    }

    public function queue(ZoneDefinition $zone, DeviceToken $token, CarbonInterface $expiresAt): void
    {
        $minutes = max(1, (int) ceil(Carbon::now()->diffInMinutes($expiresAt)));

        $this->cookies->queue($this->cookies->make(
            self::name($zone),
            $token->toCookie(),
            $minutes,
            $this->path(),
            $this->domain(),
            $this->config->get('session.secure') === true,
            true,
            false,
            self::SAME_SITE,
        ));
    }

    public function forget(ZoneDefinition $zone): void
    {
        $this->cookies->queue($this->cookies->forget(self::name($zone), $this->path(), $this->domain()));
    }

    private function path(): string
    {
        $path = $this->config->get('session.path');

        return is_string($path) && $path !== '' ? $path : '/';
    }

    private function domain(): ?string
    {
        $domain = $this->config->get('session.domain');

        return is_string($domain) && $domain !== '' ? $domain : null;
    }
}

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

namespace Simplysmart\Mainboard\Zones;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Simplysmart\Mainboard\Auth\ZoneAuthSettingsFactory;
use Simplysmart\Mainboard\Enums\Palette;

/**
 * Buduje ZoneDefinition z configu paczki strefy, uzupełniając go domyślnymi wartościami Core.
 *
 * Config strefy: name (wymagane), slug, guard, provider, model, session (klucze ZoneSessionSettings),
 * auth (nadpisania `mainboard.auth`, tylko strefa z modelem), teams (organizacje klientów, tylko strefa z modelem),
 * security (`mask_errors`, `block_ai_agents`; agenci AI domyślnie blokowani w strefach z logowaniem), stylesheets
 * (arkusze CSS strefy), palettes (kolorystyki do wyboru: wartości Enums\Palette), favicon (ikony strony: `ico`, `svg`,
 * `apple` — ścieżki plików), texts (namespace tekstów paczki strefy, np. etykiety ról), fonts (pliki .woff2 strefy:
 * klucz → `family`, `weight`, `style`, `file`, `unicode_range`), legal_links (odnośniki prawne w karcie logowania: lista
 * `route` — krótka nazwa trasy strefy — i `label` — klucz tekstu; tylko strefa z logowaniem).
 */
final readonly class ZoneDefinitionFactory
{
    private const string NAME_PATTERN = '/^[a-z][a-z0-9_]*$/';

    public function __construct(
        private Repository $config,
        private ZoneAuthSettingsFactory $authSettings,
    ) {}

    /**
     * @param array<array-key, mixed> $config
     *
     * @throws InvalidArgumentException Gdy config strefy jest niepoprawny.
     */
    public function make(array $config, string $routeNamePrefix, string $routesPath): ZoneDefinition
    {
        $name = Arr::string($config, 'name');

        foreach (['name' => $name, 'route name prefix' => $routeNamePrefix] as $label => $value) {
            if (preg_match(self::NAME_PATTERN, $value) !== 1) {
                throw new InvalidArgumentException("Zone {$label} [{$value}] must match " . self::NAME_PATTERN . '.');
            }
        }

        $model = $this->model($config['model'] ?? null, $name);

        return new ZoneDefinition(
            name: $name,
            routeNamePrefix: $routeNamePrefix,
            slug: trim(Arr::string($config, 'slug', $name), '/'),
            guard: Arr::string($config, 'guard', $name),
            provider: Arr::string($config, 'provider', ZoneTable::Accounts->for($name)),
            model: $model,
            routesPath: rtrim($routesPath, '/'),
            session: $this->session($name, $model !== null, Arr::array($config, 'session', [])),
            authSettings: $model !== null ? $this->authSettings->make(Arr::array($config, 'auth', [])) : null,
            teams: $model !== null && Arr::boolean($config, 'teams', false),
            security: new ZoneSecuritySettings(
                maskErrors: $this->nullableBool(Arr::array($config, 'security', []), 'mask_errors', null),
                blockAiAgents: Arr::boolean(Arr::array($config, 'security', []), 'block_ai_agents', $model !== null),
            ),
            stylesheets: $this->stylesheets($name, Arr::array($config, 'stylesheets', [])),
            palettes: $this->palettes($name, Arr::array($config, 'palettes', [])),
            favicon: $this->favicon($name, Arr::array($config, 'favicon', [])),
            textNamespace: $this->nullableString($config, 'texts', null),
            fonts: $this->fonts($name, Arr::array($config, 'fonts', [])),
            legalLinks: $model !== null ? $this->legalLinks($name, Arr::array($config, 'legal_links', [])) : [],
        );
    }

    /**
     * @return class-string<Authenticatable>|null
     */
    private function model(mixed $model, string $zone): ?string
    {
        if ($model === null) {
            return null;
        }

        if (! is_string($model) || ! is_a($model, Authenticatable::class, true)) {
            throw new InvalidArgumentException("Zone [{$zone}] model must be a class implementing " . Authenticatable::class . '.');
        }

        return $model;
    }

    /**
     * Strefa z logowaniem dostaje własną tabelę sesji, krótki czas życia, SameSite=Strict
     * i szyfrowanie; strefa publiczna tylko własną nazwę ciasteczka i SameSite=Lax.
     *
     * @param array<array-key, mixed> $session
     */
    private function session(string $zone, bool $requiresLogin, array $session): ZoneSessionSettings
    {
        return new ZoneSessionSettings(
            cookie: Arr::string($session, 'cookie', $zone . '_session'),
            table: $this->nullableString($session, 'table', $requiresLogin ? ZoneTable::Sessions->for($zone) : null),
            lifetime: $this->nullableInt($session, 'lifetime', $requiresLogin ? $this->config->integer('mainboard.zones.session_lifetime') : null),
            expireOnClose: $this->nullableBool($session, 'expire_on_close', $requiresLogin ? true : null),
            sameSite: $this->nullableString($session, 'same_site', $requiresLogin ? 'strict' : 'lax'),
            encrypt: $this->nullableBool($session, 'encrypt', $requiresLogin ? true : null),
        );
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private function nullableString(array $values, string $key, ?string $default): ?string
    {
        return array_key_exists($key, $values) && $values[$key] !== null ? Arr::string($values, $key) : $default;
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private function nullableInt(array $values, string $key, ?int $default): ?int
    {
        return array_key_exists($key, $values) && $values[$key] !== null ? Arr::integer($values, $key) : $default;
    }

    /**
     * @param array<array-key, mixed> $values
     */
    private function nullableBool(array $values, string $key, ?bool $default): ?bool
    {
        return array_key_exists($key, $values) && $values[$key] !== null ? Arr::boolean($values, $key) : $default;
    }

    /**
     * Arkusze CSS strefy z `stylesheets` (ścieżki istniejących plików `.css`).
     *
     * @param array<array-key, mixed> $paths
     *
     * @return list<string>
     *
     * @throws InvalidArgumentException Gdy ścieżka nie wskazuje istniejącego pliku `.css`.
     */
    private function stylesheets(string $zone, array $paths): array
    {
        $stylesheets = [];

        foreach ($paths as $path) {
            if (! is_string($path) || ! is_file($path) || strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'css') {
                throw new InvalidArgumentException("Zone [{$zone}] stylesheet must be an existing .css file.");
            }

            $stylesheets[] = $path;
        }

        return $stylesheets;
    }

    /**
     * Kolorystyki strefy z `palettes` (wartości {@see Palette}, bez powtórzeń, w kolejności z configu).
     *
     * @param array<array-key, mixed> $values
     *
     * @return list<Palette>
     *
     * @throws InvalidArgumentException Gdy wartość nie jest znaną kolorystyką albo się powtarza.
     */
    private function palettes(string $zone, array $values): array
    {
        $palettes = [];

        foreach ($values as $value) {
            $palette = is_string($value) ? Palette::tryFrom($value) : null;

            if ($palette === null || in_array($palette, $palettes, true)) {
                throw new InvalidArgumentException("Zone [{$zone}] palettes must be unique values of " . Palette::class . '.');
            }

            $palettes[] = $palette;
        }

        return $palettes;
    }

    /**
     * Ikony strony strefy z `favicon` (rodzaj Enums\FaviconType → ścieżka pliku); brak klucza = ikona aplikacji.
     *
     * @param array<array-key, mixed> $files
     *
     * @throws InvalidArgumentException Gdy rodzaj albo plik jest niepoprawny.
     */
    private function favicon(string $zone, array $files): ?ZoneFavicon
    {
        if ($files === []) {
            return null;
        }

        $paths = [];

        foreach ($files as $type => $path) {
            if (! is_string($type) || ! is_string($path)) {
                throw new InvalidArgumentException("Zone [{$zone}] favicon must map icon types to file paths.");
            }

            $paths[$type] = $path;
        }

        try {
            return new ZoneFavicon($paths);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException("Zone [{$zone}] {$exception->getMessage()}", previous: $exception);
        }
    }

    /**
     * Fonty strefy z `fonts` (klucz pliku → `family`, `weight`, `style`, `file`, opcjonalnie `unicode_range`); brak
     * klucza = font Core.
     *
     * @param array<array-key, mixed> $fonts
     *
     * @throws InvalidArgumentException Gdy wpis albo plik jest niepoprawny.
     */
    private function fonts(string $zone, array $fonts): ?ZoneFonts
    {
        if ($fonts === []) {
            return null;
        }

        $faces = [];

        foreach ($fonts as $key => $face) {
            if (! is_string($key) || ! is_array($face)) {
                throw new InvalidArgumentException("Zone [{$zone}] fonts must map file keys to font descriptions.");
            }

            $faces[$key] = [
                'family' => Arr::string($face, 'family'),
                'weight' => Arr::integer($face, 'weight', 400),
                'style' => Arr::string($face, 'style', 'normal'),
                'file' => Arr::string($face, 'file'),
                'unicode_range' => $this->nullableString($face, 'unicode_range', null),
            ];
        }

        try {
            return new ZoneFonts($faces);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException("Zone [{$zone}] {$exception->getMessage()}", previous: $exception);
        }
    }

    /**
     * Odnośniki prawne karty logowania z `legal_links` (lista `route` i `label`, niepuste teksty).
     *
     * @param array<array-key, mixed> $links
     *
     * @return list<array{route: string, label: string}>
     *
     * @throws InvalidArgumentException Gdy wpis nie ma niepustych `route` i `label`.
     */
    private function legalLinks(string $zone, array $links): array
    {
        $legalLinks = [];

        foreach ($links as $link) {
            $route = is_array($link) && is_string($link['route'] ?? null) ? $link['route'] : '';
            $label = is_array($link) && is_string($link['label'] ?? null) ? $link['label'] : '';

            if ($route === '' || $label === '') {
                throw new InvalidArgumentException("Zone [{$zone}] legal_links need a route name and a label key.");
            }

            $legalLinks[] = ['route' => $route, 'label' => $label];
        }

        return $legalLinks;
    }
}

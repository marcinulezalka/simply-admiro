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

namespace Simplysmart\Admiro\Zones;

use BackedEnum;
use Illuminate\Config\Repository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Simplysmart\Admiro\Auth\ZoneAuthSettingsFactory;
use Simplysmart\Admiro\Enums\AppBackdrop;
use Simplysmart\Admiro\Enums\AppLayout;
use Simplysmart\Admiro\Enums\LegalDocumentType;
use Simplysmart\Admiro\Enums\Palette;
use Simplysmart\Admiro\Enums\SocialNetwork;
use Simplysmart\Admiro\Mail\MailPalette;

/**
 * Buduje ZoneDefinition z configu paczki strefy, uzupełniając go domyślnymi wartościami Core.
 *
 * Config strefy: name (wymagane), slug, guard, provider, model, session (klucze ZoneSessionSettings),
 * auth (nadpisania `admiro.auth`, tylko strefa z modelem), teams (organizacje klientów, tylko strefa z modelem),
 * security (`mask_errors`, `block_ai_agents`; agenci AI domyślnie blokowani w strefach z logowaniem), stylesheets
 * (arkusze CSS strefy), palettes (kolorystyki do wyboru: wartości Enums\Palette), favicon (ikony strony: `ico`, `svg`,
 * `apple` — ścieżki plików), texts (namespace tekstów paczki strefy, np. etykiety ról), fonts (pliki .woff2 strefy:
 * klucz → `family`, `weight`, `style`, `file`, `unicode_range`), legal_links (odnośniki prawne w karcie logowania: lista
 * `route` — krótka nazwa trasy strefy — i `label` — klucz tekstu; tylko strefa z logowaniem), legal_documents
 * (dokumenty prawne Core linkowane w strefie, gdy są opublikowane: wartości Enums\LegalDocumentType; tylko strefa
 * z logowaniem), brand (`logo`, `signet` —
 * pliki PNG/JPG marki strefy, m.in. do e-maili), mail.views (prefiks widoków e-maili strefy: `<prefiks>.layout`,
 * `<prefiks>.<szablon>`) i mail.palette (kolory hex e-maili: `bg`, `surface`, `border`, `text`, `muted`, `accent`,
 * `on_accent`), logo (SVG strefy osadzany inline), website (strona WWW strefy: adres http(s)), app_layout (wariant
 * layoutu aplikacji: Enums\AppLayout), backdrop (tło płótna: Enums\AppBackdrop; `zone-logo` wymaga logo strefy),
 * social_links (profile w serwisach społecznościowych: Enums\SocialNetwork → adres http(s)), font_styles (kroje do
 * wyboru na stronie wyglądu: klucz kroju → klucz tekstu z jego nazwą, Zones\ZoneFontStyle) oraz role_contexts (role konta w organizacji jako konteksty z zawężonymi uprawnieniami, tylko strefa
 * z `teams`).
 */
final readonly class ZoneDefinitionFactory
{
    private const string NAME_PATTERN = '/^[a-z][a-z0-9_]*$/';

    private const string MAIL_VIEWS_PATTERN = '/^[a-z][a-z0-9_-]*::[a-z0-9_.-]+$/';

    /** Klucz kroju strefy (`font_styles`): wartość `data-font` i część nazwy klucza zapamiętania w przeglądarce. */
    private const string FONT_STYLE_KEY = '/^[a-z][a-z0-9-]*$/';

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
        $authSettings = $model !== null ? $this->authSettings->make(Arr::array($config, 'auth', [])) : null;
        $logo = $this->logo($name, $config['logo'] ?? null) ?? $authSettings?->logo;

        return new ZoneDefinition(
            name: $name,
            routeNamePrefix: $routeNamePrefix,
            slug: trim(Arr::string($config, 'slug', $name), '/'),
            guard: Arr::string($config, 'guard', $name),
            provider: Arr::string($config, 'provider', ZoneTable::Accounts->for($name)),
            model: $model,
            routesPath: rtrim($routesPath, '/'),
            session: $this->session($name, $model !== null, Arr::array($config, 'session', [])),
            authSettings: $authSettings,
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
            legalDocuments: $model !== null ? $this->legalDocuments($name, Arr::array($config, 'legal_documents', [])) : [],
            brand: $this->brand($name, Arr::array($config, 'brand', [])),
            mailViews: $this->mailViews($name, Arr::array($config, 'mail', [])),
            mailPalette: $this->mailPalette($name, Arr::array(Arr::array($config, 'mail', []), 'palette', [])),
            roleContexts: $model !== null && Arr::boolean($config, 'teams', false) && Arr::boolean($config, 'role_contexts', false),
            logo: $logo,
            website: $this->website($name, $config['website'] ?? null),
            appLayout: $this->option($name, 'app_layout', $config['app_layout'] ?? null, AppLayout::class, AppLayout::Sidebar),
            socialLinks: $this->socialLinks($name, Arr::array($config, 'social_links', [])),
            backdrop: $this->backdrop($name, $config['backdrop'] ?? null, $logo),
            fontStyles: $this->fontStyles($name, Arr::array($config, 'font_styles', [])),
        );
    }

    /**
     * Profile w serwisach społecznościowych z `social_links` (wartość Enums\SocialNetwork → adres http(s)); puste adresy
     * są pomijane (np. niewypełnione zmienne `.env`), kolejność jak w enumie.
     *
     * @param array<array-key, mixed> $links
     *
     * @return list<array{network: SocialNetwork, url: string}>
     *
     * @throws InvalidArgumentException Gdy serwis jest nieznany albo adres nie jest http(s).
     */
    private function socialLinks(string $zone, array $links): array
    {
        $result = [];

        foreach (SocialNetwork::cases() as $network) {
            $url = $links[$network->value] ?? null;

            if ($url === null || $url === '') {
                continue;
            }

            if (! self::isHttpUrl($url)) {
                throw new InvalidArgumentException("Zone [{$zone}] social_links.{$network->value} must be an http(s) URL.");
            }

            $result[] = ['network' => $network, 'url' => $url];
        }

        $unknown = array_diff(array_map(strval(...), array_keys($links)), array_column(SocialNetwork::cases(), 'value'));

        if ($unknown !== []) {
            throw new InvalidArgumentException("Zone [{$zone}] social_links has unknown networks: " . implode(', ', $unknown) . '.');
        }

        return $result;
    }

    /**
     * Tło płótna z klucza `backdrop` (wartość Enums\AppBackdrop); brak klucza — logotyp simplysmart.
     *
     * @throws InvalidArgumentException Gdy wartość nie jest tłem albo logo strefy w tle nie ma logo strefy.
     */
    private function backdrop(string $zone, mixed $value, ?string $logo): AppBackdrop
    {
        $backdrop = $this->option($zone, 'backdrop', $value, AppBackdrop::class, AppBackdrop::Brand);

        return $backdrop === AppBackdrop::ZoneLogo && $logo === null ? throw new InvalidArgumentException("Zone [{$zone}] backdrop zone-logo needs the zone logo.") : $backdrop;
    }

    /**
     * Kroje strefy z `font_styles` (klucz kroju → klucz tekstu z jego nazwą), w kolejności z configu.
     *
     * @param array<array-key, mixed> $styles
     *
     * @return list<ZoneFontStyle>
     *
     * @throws InvalidArgumentException Gdy klucz nie jest kluczem kroju albo nazwa nie jest kluczem tekstu.
     */
    private function fontStyles(string $zone, array $styles): array
    {
        $result = [];

        foreach ($styles as $key => $label) {
            if (! is_string($key) || preg_match(self::FONT_STYLE_KEY, $key) !== 1 || ! is_string($label) || $label === '') {
                throw new InvalidArgumentException("Zone [{$zone}] font_styles must map font keys (lowercase letters, digits, dashes) to text keys.");
            }

            $result[] = new ZoneFontStyle($key, $label);
        }

        return $result;
    }

    /**
     * Przypadek enuma z wartości klucza configu strefy (np. `app_layout`, `backdrop`); brak klucza — $default.
     *
     * @template TEnum of BackedEnum
     *
     * @param class-string<TEnum> $enum
     * @param TEnum $default
     *
     * @return TEnum
     *
     * @throws InvalidArgumentException Gdy wartość nie jest wartością przypadku enuma.
     */
    private function option(string $zone, string $key, mixed $value, string $enum, BackedEnum $default): BackedEnum
    {
        if ($value === null) {
            return $default;
        }

        $case = is_string($value) ? $enum::tryFrom($value) : null;

        return $case ?? throw new InvalidArgumentException("Zone [{$zone}] {$key} must be one of: " . implode(', ', array_column($enum::cases(), 'value')) . '.');
    }

    /**
     * Strona WWW strefy z klucza `website` (adres http(s)) albo null, gdy strefa jej nie podaje.
     *
     * @throws InvalidArgumentException Gdy wartość nie jest adresem http(s).
     */
    private function website(string $zone, mixed $website): ?string
    {
        if ($website === null || $website === '') {
            return null;
        }

        if (! self::isHttpUrl($website)) {
            throw new InvalidArgumentException("Zone [{$zone}] website must be an http(s) URL.");
        }

        return $website;
    }

    /**
     * Logo strefy z klucza `logo` (ścieżka pliku SVG osadzanego inline) albo null, gdy strefa go nie podaje.
     *
     * @throws InvalidArgumentException Gdy wartość nie jest ścieżką istniejącego pliku SVG.
     */
    private function logo(string $zone, mixed $logo): ?string
    {
        if ($logo === null) {
            return null;
        }

        if (! is_string($logo) || ! is_file($logo) || strtolower(pathinfo($logo, PATHINFO_EXTENSION)) !== 'svg') {
            throw new InvalidArgumentException("Zone [{$zone}] logo must be the path of an existing SVG file.");
        }

        return $logo;
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
            lifetime: $this->nullableInt($session, 'lifetime', $requiresLogin ? $this->config->integer('admiro.zones.session_lifetime') : null),
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
     * Dokumenty prawne Core strefy z `legal_documents` (wartości {@see LegalDocumentType}, bez powtórzeń, w kolejności
     * z configu; bez polityki cookies, którą linkują odnośniki cookies karty logowania).
     *
     * @param array<array-key, mixed> $values
     *
     * @return list<LegalDocumentType>
     *
     * @throws InvalidArgumentException Gdy wartość nie jest znanym dokumentem, jest polityką cookies albo się powtarza.
     */
    private function legalDocuments(string $zone, array $values): array
    {
        $documents = [];

        foreach ($values as $value) {
            $document = is_string($value) ? LegalDocumentType::tryFrom($value) : null;

            if ($document === null || $document === LegalDocumentType::Cookies || in_array($document, $documents, true)) {
                throw new InvalidArgumentException("Zone [{$zone}] legal_documents must be unique values of " . LegalDocumentType::class . ' other than the cookie policy.');
            }

            $documents[] = $document;
        }

        return $documents;
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

        $paths = $this->paths($zone, $files, 'favicon must map icon types to file paths.');

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
     * Pliki marki strefy z `brand` (rodzaj pliku → ścieżka); brak klucza = marka Core.
     *
     * @param array<array-key, mixed> $files
     *
     * @throws InvalidArgumentException Gdy rodzaj albo plik jest niepoprawny.
     */
    private function brand(string $zone, array $files): ?ZoneBrand
    {
        if ($files === []) {
            return null;
        }

        $paths = $this->paths($zone, $files, 'brand must map brand files to paths.');

        try {
            return new ZoneBrand($paths);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException("Zone [{$zone}] {$exception->getMessage()}", previous: $exception);
        }
    }

    /**
     * Mapa rodzaj pliku → ścieżka z konfiguracji strefy (`favicon`, `brand`).
     *
     * @param array<array-key, mixed> $files
     *
     * @return array<string, string>
     *
     * @throws InvalidArgumentException Gdy klucz albo ścieżka nie jest tekstem (komunikat `$invalid`).
     */
    private function paths(string $zone, array $files, string $invalid): array
    {
        $paths = [];

        foreach ($files as $type => $path) {
            if (! is_string($type) || ! is_string($path)) {
                throw new InvalidArgumentException("Zone [{$zone}] {$invalid}");
            }

            $paths[$type] = $path;
        }

        return $paths;
    }

    /**
     * @param array<array-key, mixed> $colors
     *
     * @throws InvalidArgumentException
     */
    private function mailPalette(string $zone, array $colors): MailPalette
    {
        try {
            return MailPalette::fromArray($colors);
        } catch (InvalidArgumentException $exception) {
            throw new InvalidArgumentException("Zone [{$zone}] {$exception->getMessage()}", previous: $exception);
        }
    }

    /**
     * Prefiks widoków e-maili (`mail.views`): nazwa widoku z namespace paczki, np. `shop::mail.shop`.
     *
     * @param array<array-key, mixed> $mail
     *
     * @throws InvalidArgumentException
     */
    private function mailViews(string $zone, array $mail): ?string
    {
        $views = $mail['views'] ?? null;

        if ($views === null) {
            return null;
        }

        if (! is_string($views) || preg_match(self::MAIL_VIEWS_PATTERN, $views) !== 1) {
            throw new InvalidArgumentException("Zone [{$zone}] mail.views must be a namespaced view prefix like `package::mail.{$zone}`.");
        }

        return $views;
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

    /**
     * @phpstan-assert-if-true string $value
     */
    private static function isHttpUrl(mixed $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false && preg_match('~^https?://~i', $value) === 1;
    }
}

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

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use Simplysmart\Mainboard\Auth\ZoneAuthSettings;
use Simplysmart\Mainboard\Contracts\CodeAndPinAccount;
use Simplysmart\Mainboard\Enums\LegalDocumentType;
use Simplysmart\Mainboard\Enums\Palette;
use Simplysmart\Mainboard\Mail\MailPalette;

/**
 * Niezmienna definicja jednej strefy (budowana przez ZoneDefinitionFactory z configu paczki strefy).
 */
final readonly class ZoneDefinition
{
    /**
     * @param string $name Nazwa strefy (klucze cache, nazwa ciasteczka, grupa middleware).
     * @param string $routeNamePrefix Stały prefiks nazw tras (bez kropki), niezależny od configu.
     * @param string $slug Segment URL strefy; '' = bez segmentu.
     * @param string $guard Guard strefy.
     * @param string $provider Provider użytkowników guardu.
     * @param class-string<Authenticatable>|null $model Model kont strefy; null = strefa bez logowania.
     * @param string $routesPath Katalog plików tras strefy (public.php, account.php, zone.php).
     * @param ZoneAuthSettings|null $authSettings Ustawienia logowania; null = strefa bez logowania.
     * @param ZoneSecuritySettings $security Maskowanie błędów i blokada agentów AI.
     * @param bool $teams Konta działają w organizacjach (firmach klientów) wybieranych w żądaniu;
     *                    false = zawsze w organizacji operatora aplikacji.
     * @param list<string> $stylesheets Arkusze CSS strefy (ścieżki plików), dołączane po stylach Core na stronach
     *                                  logowania i w layoucie aplikacji (x-mainboard::zone-styles).
     * @param list<Palette> $palettes Kolorystyki do wyboru w strefie (x-mainboard::palette-switch); pusta lista =
     *                               bez przełącznika, tylko kolory bazowe strefy.
     * @param ZoneFavicon|null $favicon Ikony strony strefy (trasa `<strefa>.favicon`); null = ikona aplikacji.
     * @param string|null $textNamespace Namespace tekstów paczki strefy (`texts` w configu, np. etykiety ról
     *                                   `<namespace>::roles.<strefa>.<rola>`); null = teksty Core.
     * @param ZoneFonts|null $fonts Fonty strefy (trasa `<strefa>.font`, `@font-face` w szablonie bazowym); null = font Core.
     * @param list<array{route: string, label: string}> $legalLinks Odnośniki prawne w karcie logowania (krótka nazwa trasy
     *                                                         strefy i klucz tekstu), przed odnośnikami cookies; pusta
     *                                                         lista = same odnośniki cookies pod kartami.
     * @param list<LegalDocumentType> $legalDocuments Opublikowane dokumenty prawne Core linkowane w strefie (karta logowania,
     *                                              stopka, e-maile; Legal\LegalLinks) przed `legalLinks`.
     * @param ZoneBrand|null $brand Logo i sygnet strefy (`brand`); null = marka Core (w e-mailach logo simplysmart).
     * @param string|null $mailViews Prefiks widoków e-maili strefy (`mail.views`, np. `shop::mail.shop`): `<prefiks>.layout`
     *                               i `<prefiks>.<szablon>` zastępują widoki Core; null = layout i szablony Core.
     * @param MailPalette $mailPalette Kolory e-maili strefy (`mail.palette`); domyślnie paleta Core.
     * @param bool $roleContexts Role konta w organizacji są kontekstami (`role_contexts`, tylko strefa z `teams`): konto
     *                           z kilkoma rolami działa w jednej wybranej (Permissions\RoleSelector), a jego uprawnienia
     *                           są zawężone do niej; false = uprawnienia z sumy ról.
     */
    public function __construct(
        public string $name,
        public string $routeNamePrefix,
        public string $slug,
        public string $guard,
        public string $provider,
        public ?string $model,
        public string $routesPath,
        public ZoneSessionSettings $session,
        public ?ZoneAuthSettings $authSettings = null,
        public bool $teams = false,
        public ZoneSecuritySettings $security = new ZoneSecuritySettings(),
        public array $stylesheets = [],
        public array $palettes = [],
        public ?ZoneFavicon $favicon = null,
        public ?string $textNamespace = null,
        public ?ZoneFonts $fonts = null,
        public array $legalLinks = [],
        public ?ZoneBrand $brand = null,
        public ?string $mailViews = null,
        public MailPalette $mailPalette = new MailPalette(),
        public bool $roleContexts = false,
        public array $legalDocuments = [],
    ) {}

    /**
     * Czy strefa ma konta i logowanie.
     */
    /**
     * Klucz tekstu w namespace paczki strefy (dla `text()`: bez tekstu w paczce kaskada sięga do Core).
     */
    public function textKey(string $key): string
    {
        return $this->textNamespace === null ? $key : $this->textNamespace . '::' . $key;
    }

    /**
     * Tekst ekranu logowania strefy: najpierw osobny dla strefy (`<namespace>::sign_in.<strefa>.<klucz>`, gdy kilka
     * stref dzieli paczkę tekstów), potem paczki (`<namespace>::sign_in.<klucz>`), na końcu Core (`sign_in.<klucz>`).
     *
     * @param array<string, scalar> $replace
     */
    public function signInText(string $key, array $replace = []): string
    {
        if ($this->textNamespace !== null) {
            foreach ([$this->textKey('sign_in.' . $this->name . '.' . $key), $this->textKey('sign_in.' . $key)] as $candidate) {
                $line = text($candidate, $replace);

                if ($line !== $candidate) {
                    return $line;
                }
            }
        }

        return text('sign_in.' . $key, $replace);
    }

    /**
     * Etykieta roli strefy (`roles.<strefa>.<rola>` w tekstach paczki strefy albo Core).
     */
    public function roleLabel(string $role): string
    {
        return text($this->textKey('roles.' . $this->name . '.' . $role));
    }

    public function requiresLogin(): bool
    {
        return $this->model !== null;
    }

    /**
     * Ustawienia logowania strefy.
     *
     * @throws LogicException Gdy strefa nie ma logowania.
     */
    public function auth(): ZoneAuthSettings
    {
        return $this->authSettings ?? throw new LogicException("Zone [{$this->name}] has no sign-in.");
    }

    /**
     * Model kont strefy logującej się kodem i PIN-em.
     *
     * @return class-string<Model&CodeAndPinAccount>
     *
     * @throws LogicException Gdy strefa nie ma logowania albo model nie jest takim modelem Eloquent.
     */
    public function accountModel(): string
    {
        $model = $this->model;

        if ($model === null || ! is_a($model, Model::class, true) || ! is_a($model, CodeAndPinAccount::class, true)) {
            throw new LogicException("Zone [{$this->name}] model must be an Eloquent model implementing " . CodeAndPinAccount::class . '.');
        }

        return $model;
    }

    /**
     * Pełna nazwa trasy strefy, np. "alpha.dashboard".
     */
    public function routeName(string $name): string
    {
        return $this->routeNamePrefix . '.' . $name;
    }
}

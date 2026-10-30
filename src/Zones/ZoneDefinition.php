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

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use LogicException;
use Simplysmart\Admiro\Auth\ZoneAuthSettings;
use Simplysmart\Admiro\Contracts\CodeAndPinAccount;
use Simplysmart\Admiro\Enums\AppBackdrop;
use Simplysmart\Admiro\Enums\AppLayout;
use Simplysmart\Admiro\Enums\LegalDocumentType;
use Simplysmart\Admiro\Enums\Palette;
use Simplysmart\Admiro\Enums\SocialNetwork;
use Simplysmart\Admiro\Mail\MailPalette;

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
     *                                  logowania i w layoucie aplikacji (x-admiro::zone-styles).
     * @param list<Palette> $palettes Kolorystyki do wyboru w strefie (x-admiro::palette-switch); pusta lista =
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
     * @param string|null $logo Logo strefy do osadzenia inline na stronach (`logo`: ścieżka SVG z fill="currentColor",
     *                          np. strona startowa View\ZoneLogos); domyślnie `auth.logo`; null = strefa bez logo.
     * @param string|null $website Strona WWW strefy (`website`: adres http(s)), np. odnośnik logo strefy na stronie
     *                             startowej (View\ZoneLogos); null = logo bez odnośnika.
     * @param AppLayout $appLayout Wariant layoutu aplikacji po zalogowaniu (`app_layout`); Workspace — ustawienia i konto
     *                             w głównym menu, akcje z podpowiedziami, powiadomienia w wysuwanym panelu, stopka z mapą strony.
     * @param list<array{network: SocialNetwork, url: string}> $socialLinks Profile strefy w serwisach społecznościowych
     *                                                                   (`social_links`), np. ikony na karcie konta.
     * @param AppBackdrop $backdrop Tło płótna layoutu aplikacji (`backdrop`): logotyp simplysmart albo logo strefy na dole.
     * @param list<ZoneFontStyle> $fontStyles Kroje do wyboru na stronie wyglądu obok kroju bazowego strefy (`font_styles`);
     *                                       pusta lista = bez wyboru kroju.
     * @param ZoneAppearance $appearance Wygląd strefy (`appearance`): zapis wyborów w koncie i domyślny wygląd aplikacji.
     * @param bool $roleContexts Role konta w organizacji są kontekstami (`role_contexts`; w strefie bez `teams` — role
     *                           w organizacji operatora): konto z kilkoma rolami działa w jednej wybranej
     *                           (Permissions\RoleSelector), a jego uprawnienia są zawężone do niej; false = uprawnienia
     *                           z sumy ról.
     * @param bool $contextInUrl Kontekst konta (organizacja i rola) w adresie stron strefy jako nieprzezroczysty klucz
     *                           (`context_in_url`, tylko z `role_contexts`; Zones\ContextKeys): `/<slug>/<klucz>/...`.
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
        public ?string $logo = null,
        public ?string $website = null,
        public AppLayout $appLayout = AppLayout::Sidebar,
        public array $socialLinks = [],
        public AppBackdrop $backdrop = AppBackdrop::Brand,
        public array $fontStyles = [],
        public ZoneAppearance $appearance = new ZoneAppearance(),
        public bool $contextInUrl = false,
    ) {}

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

    /**
     * Czy strefa ma konta i logowanie.
     */
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

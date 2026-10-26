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

namespace Simplysmart\Admiro\Enums;

/**
 * Gotowe widoki aplikacji Core na stronie design systemu (`simplysmart.design-system.view`; rejestrowane w katalogu
 * Contracts\DesignSystemCatalog przez Providers\DesignSystemServiceProvider), złożone z tokenów
 * i komponentów Core, pogrupowane w nawigacji według obszaru (area()). Kolejność przypadków to kolejność w grupie;
 * nowy widok = nowy przypadek, jego obszar i plik Blade.
 */
enum DesignSystemView: string
{
    case Dashboard = 'dashboard';
    case List = 'list';
    case Record = 'record';
    case Form = 'form';
    case Sidebar = 'sidebar';
    case Notifications = 'notifications';
    case ContentPage = 'content-page';
    case SignIn = 'sign-in';
    case SignInSplit = 'sign-in-split';
    case SignInCode = 'sign-in-code';
    case SignInCodeInvalid = 'sign-in-code-invalid';
    case SignInPin = 'sign-in-pin';
    case SignInPinKeypad = 'sign-in-pin-keypad';
    case SignInPinLimit = 'sign-in-pin-limit';
    case SignInPinSetup = 'sign-in-pin-setup';
    case SignInExpired = 'sign-in-expired';
    case ForgotPin = 'forgot-pin';
    case ForgotPinRecovery = 'forgot-pin-recovery';
    case ForgotPinCode = 'forgot-pin-code';
    case Error401 = 'error-401';
    case Error403 = 'error-403';
    case Error404 = 'error-404';
    case Error419 = 'error-419';
    case Error429 = 'error-429';
    case Error500 = 'error-500';
    case Error503 = 'error-503';

    /**
     * Szablon widoku: prawdziwe ekrany logowania (`admiro::auth.*`) i strony błędów (`admiro::app.errors.*`)
     * są pokazywane z produkcyjnych plików, pozostałe to widoki design systemu.
     */
    public function template(): string
    {
        return match ($this) {
            self::SignInSplit => self::SignIn->template(),
            self::SignInCode, self::SignInCodeInvalid => 'admiro::auth.code',
            self::SignInPin, self::SignInPinKeypad, self::SignInPinLimit => 'admiro::auth.pin',
            self::SignInPinSetup => 'admiro::auth.pin-setup',
            self::SignInExpired => 'admiro::auth.login',
            self::ForgotPin, self::ForgotPinRecovery, self::ForgotPinCode => 'admiro::auth.forgot-pin',
            default => $this->errorCode() !== null ? 'admiro::app.errors.' . $this->errorCode() : 'admiro::design-system.views.' . $this->value,
        };
    }

    public function area(): DesignSystemArea
    {
        return match ($this) {
            self::SignIn, self::SignInSplit, self::SignInCode, self::SignInCodeInvalid, self::SignInPin, self::SignInPinKeypad,
            self::SignInPinLimit, self::SignInPinSetup, self::SignInExpired, self::ForgotPin, self::ForgotPinRecovery,
            self::ForgotPinCode => DesignSystemArea::Authorization,
            self::Dashboard, self::List, self::Record, self::Form, self::Sidebar, self::Notifications => DesignSystemArea::Workspace,
            self::ContentPage => DesignSystemArea::Pages,
            self::Error401, self::Error403, self::Error404, self::Error419, self::Error429, self::Error500, self::Error503 => DesignSystemArea::Errors,
        };
    }

    /**
     * Kod HTTP strony błędu albo null dla pozostałych widoków.
     */
    public function errorCode(): ?int
    {
        return str_starts_with($this->value, 'error-') ? (int) substr($this->value, strlen('error-')) : null;
    }

    /**
     * Czy podgląd renderuje prawdziwy ekran logowania strefy (wymaga strefy z logowaniem, dane z SignInPreviews).
     */
    public function isZoneSignIn(): bool
    {
        return str_starts_with($this->template(), 'admiro::auth.');
    }

    /**
     * Układ logowania pokazywany przez widok logowania (oba warianty dzielą jeden szablon).
     */
    public function authLayout(): AuthLayout
    {
        return $this === self::SignInSplit ? AuthLayout::Split : AuthLayout::Centered;
    }

    public function labelKey(): string
    {
        return 'design_system.views.' . $this->value;
    }

    public function label(): string
    {
        return text($this->labelKey());
    }
}

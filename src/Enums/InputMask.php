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

namespace Simplysmart\Mainboard\Enums;

/**
 * Maska popularnych pól (x-mainboard::input, js/input-mask.js): wzór (`9` cyfra, `A` litera, pozostałe znaki
 * wstawiane same), klawiatura, ikona i przykład. Formaty polskie; wzór jest też podany w podpowiedzi pola.
 */
enum InputMask: string
{
    case Phone = 'phone';
    case PostalCode = 'postal-code';
    case TaxId = 'tax-id';
    case Pesel = 'pesel';
    case BankAccount = 'bank-account';

    public function pattern(): string
    {
        return match ($this) {
            self::Phone => '+99 999 999 999',
            self::PostalCode => '99-999',
            self::TaxId => '999-999-99-99',
            self::Pesel => '99999999999',
            self::BankAccount => '99 9999 9999 9999 9999 9999 9999',
        };
    }

    public function inputMode(): string
    {
        return $this === self::Phone ? 'tel' : 'numeric';
    }

    public function icon(): string
    {
        return match ($this) {
            self::Phone => 'phone',
            self::PostalCode => 'map-pin',
            self::TaxId => 'building',
            self::Pesel => 'id-card',
            self::BankAccount => 'credit-card',
        };
    }

    /**
     * Przykładowa wartość w formacie maski (placeholder).
     */
    public function example(): string
    {
        return match ($this) {
            self::Phone => '+48 600 100 200',
            self::PostalCode => '00-950',
            self::TaxId => '525-000-11-22',
            self::Pesel => '88041512345',
            self::BankAccount => '61 1090 1014 0000 0712 1981 2874',
        };
    }
}

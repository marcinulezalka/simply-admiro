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

namespace Simplysmart\Mainboard\Contracts;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Simplysmart\Mainboard\Auth\SignIn\PendingSignIn;

/**
 * Krok logowania strefy (kod z e-maila, PIN, ustawienie PIN-u, w przyszłości np. TOTP).
 *
 * Kroki strefy i ich kolejność pochodzą z configu (`auth.steps`); nowy krok to nowa klasa,
 * bez zmian w Core. Trasy kroku: `<strefa>.auth.<key>` (GET) i `<strefa>.auth.<key>.store` (POST).
 */
interface SignInStep
{
    /**
     * Klucz kroku: segment URL i nazwa trasy (kebab-case).
     */
    public function key(): string;

    /**
     * Czy krok jest potrzebny w tym logowaniu (wykonane kroki pomija SignInFlow).
     */
    public function isRequired(PendingSignIn $signIn): bool;

    /**
     * Ile minut po wejściu w krok można go wykonać.
     */
    public function ttlMinutes(): int;

    public function view(): string;

    /**
     * @return array<string, mixed>
     */
    public function viewData(PendingSignIn $signIn): array;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array;

    /**
     * Wykonuje krok na zwalidowanych danych.
     *
     * @param array<array-key, mixed> $input
     *
     * @throws ValidationException Gdy krok się nie powiódł (komunikat zawsze ogólny).
     */
    public function handle(PendingSignIn $signIn, array $input, Request $request): void;
}

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

use Illuminate\Config\Repository;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\StringEncrypter;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Pułapka w formularzu (`mainboard.honeypot.forms`): ukryte pole, którego człowiek nie wypełnia, i zaszyfrowany
 * czas wyświetlenia formularza; wysłanie szybsze niż `form_min_seconds`, bez czasu albo z podrobionym czasem
 * to bot. Widok dodaje pola komponentem `x-mainboard::honeypot`.
 */
final readonly class FormHoneypot
{
    public const string TRAP_FIELD = 'website';

    public const string TIMESTAMP_FIELD = 'form_rendered_at';

    public function __construct(
        private StringEncrypter $encrypter,
        private Repository $config,
    ) {}

    public function isEnabled(): bool
    {
        return $this->config->boolean('mainboard.honeypot.forms');
    }

    /**
     * Zaszyfrowany czas wyświetlenia formularza do ukrytego pola.
     */
    public function token(): string
    {
        return $this->encrypter->encryptString((string) Carbon::now()->getTimestamp());
    }

    public function isBot(Request $request): bool
    {
        if (! $this->isEnabled()) {
            return false;
        }

        $trap = $request->input(self::TRAP_FIELD);
        $renderedAt = $this->renderedAt($request->input(self::TIMESTAMP_FIELD));

        return (is_string($trap) && $trap !== '')
            || $renderedAt === null
            || Carbon::now()->getTimestamp() - $renderedAt < $this->config->integer('mainboard.honeypot.form_min_seconds');
    }

    private function renderedAt(mixed $token): ?int
    {
        if (! is_string($token) || $token === '') {
            return null;
        }

        try {
            $timestamp = $this->encrypter->decryptString($token);
        } catch (DecryptException) {
            return null;
        }

        return ctype_digit($timestamp) ? (int) $timestamp : null;
    }
}

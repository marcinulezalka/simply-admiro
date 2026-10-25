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

namespace Simplysmart\Mainboard\Auth\Recovery;

use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Auth\SignIn\LoginCodeGenerator;
use Simplysmart\Mainboard\Contracts\CurrentZone;

/**
 * Jednorazowe kody bezpieczeństwa poza logowaniem, trzymane w sesji strefy osobno dla każdego celu
 * (SecurityCodePurpose): HMAC kodu, ważność i liczba prób z `auth` strefy (`code_length`, `code_ttl_minutes`,
 * `code_max_attempts`) oraz zaszyfrowany ładunek (np. adres e-mail). Nie trafiają do tabeli kodów logowania, więc
 * kodem z adresu zapasowego nie da się zalogować. Nowy kod zastępuje poprzedni, wyczerpanie prób go usuwa.
 */
final readonly class SessionCodes
{
    private const string SESSION_PREFIX = 'mainboard.security_code.';

    public function __construct(
        private LoginCodeGenerator $codes,
        private CurrentZone $zone,
        private Encrypter $encrypter,
    ) {}

    /**
     * @param array<string, string> $payload
     *
     * @return string Kod do wysłania.
     */
    public function issue(Session $session, SecurityCodePurpose $purpose, array $payload): string
    {
        $settings = $this->zone->get()->auth();
        $code = $this->codes->generate($settings->codeLength);

        $session->put(self::key($purpose), [
            'hash' => $this->codes->hash($code),
            'expires_at' => Carbon::now()->addMinutes($settings->codeTtlMinutes)->getTimestamp(),
            'attempts' => 0,
            'payload' => $this->encrypter->encrypt($payload),
        ]);

        return $code;
    }

    /**
     * Ładunek ważnego kodu (bez sprawdzania kodu); null, gdy kodu nie ma albo wygasł.
     *
     * @return array<string, string>|null
     */
    public function pending(Session $session, SecurityCodePurpose $purpose): ?array
    {
        $stored = $this->stored($session, $purpose);

        return $stored === null ? null : $this->payload($stored['payload']);
    }

    /**
     * Sprawdza kod: poprawny usuwa wpis i zwraca ładunek, błędny liczy próbę (ostatnia usuwa wpis).
     *
     * @return array<string, string>|null
     */
    public function verify(Session $session, SecurityCodePurpose $purpose, string $code): ?array
    {
        $stored = $this->stored($session, $purpose);

        if ($stored === null) {
            return null;
        }

        if ($this->codes->matches($code, $stored['hash'])) {
            $this->forget($session, $purpose);

            return $this->payload($stored['payload']);
        }

        $stored['attempts']++;

        if ($stored['attempts'] >= $this->zone->get()->auth()->codeMaxAttempts) {
            $this->forget($session, $purpose);
        } else {
            $session->put(self::key($purpose), $stored);
        }

        return null;
    }

    public function forget(Session $session, SecurityCodePurpose $purpose): void
    {
        $session->forget(self::key($purpose));
    }

    /**
     * @return array{hash: string, expires_at: int, attempts: int, payload: string}|null
     */
    private function stored(Session $session, SecurityCodePurpose $purpose): ?array
    {
        $stored = $session->get(self::key($purpose));

        if (! is_array($stored) || ! is_string($stored['hash'] ?? null) || ! is_int($stored['expires_at'] ?? null) || ! is_int($stored['attempts'] ?? null) || ! is_string($stored['payload'] ?? null)) {
            return null;
        }

        if ($stored['expires_at'] < Carbon::now()->getTimestamp()) {
            $this->forget($session, $purpose);

            return null;
        }

        return ['hash' => $stored['hash'], 'expires_at' => $stored['expires_at'], 'attempts' => $stored['attempts'], 'payload' => $stored['payload']];
    }

    /**
     * @return array<string, string>
     */
    private function payload(string $encrypted): array
    {
        $payload = $this->encrypter->decrypt($encrypted);

        $values = [];

        foreach (is_array($payload) ? $payload : [] as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $values[$key] = $value;
            }
        }

        return $values;
    }

    private static function key(SecurityCodePurpose $purpose): string
    {
        return self::SESSION_PREFIX . $purpose->value;
    }
}

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

namespace Simplysmart\Mainboard\Auth\SignIn;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Stan rozpoczętego logowania w sesji strefy: adres, wykonane kroki, czas wejścia w krok,
 * czas wysłania kodu i próby kodu bez konta (decoy).
 */
final readonly class SignInFlowState
{
    private const string KEY = 'sign_in';

    public function __construct(
        private Request $request,
    ) {}

    public function begin(string $email): void
    {
        $now = Carbon::now()->getTimestamp();

        $this->request->session()->put(self::KEY, [
            'email' => $email,
            'completed' => [],
            'step' => null,
            'step_started_at' => $now,
            'code_issued_at' => $now,
            'decoy_attempts' => 0,
        ]);
    }

    public function isStarted(): bool
    {
        return $this->email() !== null;
    }

    public function email(): ?string
    {
        $email = $this->get('email');

        return is_string($email) && $email !== '' ? $email : null;
    }

    /**
     * @return list<string>
     */
    public function completed(): array
    {
        $completed = $this->get('completed');

        return is_array($completed) ? array_values(array_filter($completed, is_string(...))) : [];
    }

    public function complete(string $step): void
    {
        $this->merge(['completed' => [...$this->completed(), $step]]);
    }

    /**
     * Zapamiętuje wejście w krok; od niego liczy się czas ważności kroku, a dla pierwszego
     * kroku od rozpoczęcia logowania.
     */
    public function enter(string $step): void
    {
        $current = $this->get('step');

        if ($current === null) {
            $this->merge(['step' => $step]);
        } elseif ($current !== $step) {
            $this->merge(['step' => $step, 'step_started_at' => Carbon::now()->getTimestamp()]);
        }
    }

    public function hasStepExpired(int $ttlMinutes): bool
    {
        return $this->int('step_started_at') + $ttlMinutes * 60 < Carbon::now()->getTimestamp();
    }

    public function codeIssued(): void
    {
        $this->merge(['code_issued_at' => Carbon::now()->getTimestamp(), 'decoy_attempts' => 0, 'step_started_at' => Carbon::now()->getTimestamp()]);
    }

    public function resendCooldownRemaining(int $cooldownSeconds): int
    {
        return max(0, $this->int('code_issued_at') + $cooldownSeconds - Carbon::now()->getTimestamp());
    }

    public function recordDecoyAttempt(): int
    {
        $attempts = $this->int('decoy_attempts') + 1;
        $this->merge(['decoy_attempts' => $attempts]);

        return $attempts;
    }

    public function clear(): void
    {
        $this->request->session()->forget(self::KEY);
    }

    private function get(string $field): mixed
    {
        return $this->request->session()->get(self::KEY . '.' . $field);
    }

    private function int(string $field): int
    {
        $value = $this->get($field);

        return is_int($value) ? $value : 0;
    }

    /**
     * @param array<string, mixed> $values
     */
    private function merge(array $values): void
    {
        $current = $this->request->session()->get(self::KEY, []);

        $this->request->session()->put(self::KEY, array_replace(is_array($current) ? $current : [], $values));
    }
}

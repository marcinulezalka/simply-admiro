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

use Illuminate\Support\Carbon;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\LoginChallengeRepository;
use Simplysmart\Mainboard\Contracts\SignInAccount;

/**
 * Wydaje i sprawdza kody logowania konta: ważny jest tylko najnowszy, z TTL i limitem prób.
 */
final readonly class LoginChallengeService
{
    public function __construct(
        private LoginChallengeRepository $challenges,
        private LoginCodeGenerator $codes,
        private CurrentZone $zone,
    ) {}

    /**
     * Nowy kod, chyba że poprzedni wysłano przed chwilą (podwójne wysłanie formularza).
     *
     * @return string|null Kod do wysłania; null, gdy obowiązuje świeży poprzedni.
     */
    public function issueUnlessRecent(SignInAccount $account, ?string $ip, ?string $userAgent): ?string
    {
        $recent = $this->challenges->latestActive($this->id($account));
        $cooldown = $this->zone->get()->auth()->codeResendCooldownSeconds;

        if ($recent !== null && $recent->createdAt->diffInSeconds(Carbon::now()) < $cooldown) {
            return null;
        }

        return $this->issue($account, $ip, $userAgent);
    }

    /**
     * Unieważnia poprzednie kody i wydaje nowy.
     */
    public function issue(SignInAccount $account, ?string $ip, ?string $userAgent): string
    {
        $settings = $this->zone->get()->auth();
        $code = $this->codes->generate($settings->codeLength);

        $this->challenges->consumeAll($this->id($account));
        $this->challenges->create($this->id($account), $this->codes->hash($code), Carbon::now()->addMinutes($settings->codeTtlMinutes), $ip, $userAgent);

        return $code;
    }

    public function verify(SignInAccount $account, string $code): CodeCheckResult
    {
        $maxAttempts = $this->zone->get()->auth()->codeMaxAttempts;
        $challenge = $this->challenges->latestActive($this->id($account));

        if ($challenge === null || ! $challenge->isUsable($maxAttempts)) {
            return CodeCheckResult::Exhausted;
        }

        if ($this->codes->matches($code, $challenge->codeHash)) {
            $this->challenges->markVerified($challenge->id);

            return CodeCheckResult::Valid;
        }

        $this->challenges->recordFailedAttempt($challenge->id);

        return $challenge->attempts + 1 >= $maxAttempts ? CodeCheckResult::Exhausted : CodeCheckResult::Invalid;
    }

    private function id(SignInAccount $account): int|string
    {
        $id = $account->getAuthIdentifier();

        return is_int($id) || is_string($id) ? $id : throw new \LogicException('Account identifier must be an integer or a string.');
    }
}

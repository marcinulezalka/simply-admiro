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

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Simplysmart\Mainboard\Contracts\CurrentZone;
use Simplysmart\Mainboard\Contracts\LoginChallengeRepository;
use Simplysmart\Mainboard\Database\ZoneTables;
use Simplysmart\Mainboard\Zones\ZoneTable;

/**
 * Kody logowania w tabeli `<strefa>_login_challenges` bieżącej strefy.
 */
final readonly class EloquentLoginChallengeRepository implements LoginChallengeRepository
{
    private const int USER_AGENT_LENGTH = 255;

    public function __construct(
        private CurrentZone $zone,
    ) {}

    public function latestActive(int|string $accountId): ?LoginChallengeRecord
    {
        $query = $this->query();
        $query->where(ZoneTables::ACCOUNT_FOREIGN_KEY, $accountId);
        $query->whereNull('consumed_at');
        $query->latest('created_at');
        $query->latest('id');
        $challenge = $query->first();

        if (! $challenge instanceof LoginChallenge) {
            return null;
        }

        $id = $challenge->getKey();
        $codeHash = $challenge->getAttribute('code_hash');
        $expiresAt = $challenge->getAttribute('expires_at');
        $createdAt = $challenge->getAttribute('created_at');
        $attempts = $challenge->getAttribute('attempts');

        if (! is_string($id) || ! is_string($codeHash)) {
            return null;
        }

        return new LoginChallengeRecord(
            id: $id,
            codeHash: $codeHash,
            attempts: is_int($attempts) ? $attempts : 0,
            expiresAt: $expiresAt instanceof CarbonImmutable ? $expiresAt : CarbonImmutable::now(),
            createdAt: $createdAt instanceof CarbonImmutable ? $createdAt : CarbonImmutable::now(),
        );
    }

    public function create(int|string $accountId, string $codeHash, CarbonInterface $expiresAt, ?string $ip, ?string $userAgent): void
    {
        $this->model()->forceFill([
            ZoneTables::ACCOUNT_FOREIGN_KEY => $accountId,
            'code_hash' => $codeHash,
            'expires_at' => $expiresAt,
            'ip' => $ip,
            'user_agent' => $userAgent === null ? null : Str::limit($userAgent, self::USER_AGENT_LENGTH, ''),
            'created_at' => Carbon::now(),
        ])->save();
    }

    public function consumeAll(int|string $accountId): void
    {
        $this->query()
            ->where(ZoneTables::ACCOUNT_FOREIGN_KEY, $accountId)
            ->whereNull('consumed_at')
            ->update(['consumed_at' => Carbon::now()]);
    }

    public function recordFailedAttempt(string $challengeId): void
    {
        $this->query()->whereKey($challengeId)->increment('attempts');
    }

    public function markVerified(string $challengeId): void
    {
        $now = Carbon::now();

        $this->query()->whereKey($challengeId)->update(['verified_at' => $now, 'consumed_at' => $now]);
    }

    public function pruneCreatedBefore(CarbonInterface $before): int
    {
        return $this->query()->toBase()->where('created_at', '<', $before)->delete();
    }

    private function model(): LoginChallenge
    {
        return (new LoginChallenge())->setTable(ZoneTable::LoginChallenges->for($this->zone->get()));
    }

    /**
     * @return Builder<LoginChallenge>
     */
    private function query(): Builder
    {
        return $this->model()->newQuery();
    }
}

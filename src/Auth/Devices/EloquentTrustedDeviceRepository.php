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

namespace Simplysmart\Admiro\Auth\Devices;

use Simplysmart\Admiro\Auth\AccountKey;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Simplysmart\Admiro\Contracts\CurrentZone;
use Simplysmart\Admiro\Contracts\TrustedDeviceRepository;
use Simplysmart\Admiro\Database\ZoneTables;
use Simplysmart\Admiro\Zones\ZoneTable;

/**
 * Zaufane urządzenia w tabeli `<strefa>_trusted_devices` bieżącej strefy.
 */
final readonly class EloquentTrustedDeviceRepository implements TrustedDeviceRepository
{
    private const int TOKEN_LENGTH = 40;

    private const int TEXT_LENGTH = 255;

    public function __construct(
        private CurrentZone $zone,
    ) {}

    public function findActiveBySelector(string $selector): ?TrustedDeviceRecord
    {
        return $this->record($this->active()->where('selector', $selector)->first());
    }

    public function findActiveByToken(int|string $accountId, string $token): ?TrustedDeviceRecord
    {
        return $this->record($this->active()->where(ZoneTables::ACCOUNT_FOREIGN_KEY, $accountId)->where('token', $token)->first());
    }

    public function activeFor(int|string $accountId): array
    {
        $devices = $this->active()->where(ZoneTables::ACCOUNT_FOREIGN_KEY, $accountId)->latest('created_at')->latest('id')->get();

        return array_values(array_filter($devices->map($this->record(...))->all()));
    }

    public function create(
        int|string $accountId,
        string $selector,
        string $validatorHash,
        string $name,
        ?string $ip,
        ?string $userAgent,
        CarbonInterface $expiresAt,
    ): TrustedDeviceRecord {
        $device = $this->model()->forceFill([
            'token' => Str::random(self::TOKEN_LENGTH),
            ZoneTables::ACCOUNT_FOREIGN_KEY => $accountId,
            'selector' => $selector,
            'validator_hash' => $validatorHash,
            'name' => Str::limit($name, self::TEXT_LENGTH, ''),
            'ip' => $ip,
            'user_agent' => $userAgent === null ? null : Str::limit($userAgent, self::TEXT_LENGTH, ''),
            'created_at' => Carbon::now(),
            'expires_at' => $expiresAt,
        ]);
        $device->save();

        return $this->record($device) ?? throw new \LogicException('A saved trusted device must be readable.');
    }

    public function rotate(string $id, string $validatorHash): void
    {
        $this->query()->whereKey($id)->update(['validator_hash' => $validatorHash, 'last_used_at' => Carbon::now()]);
    }

    public function revoke(string $id): void
    {
        $this->query()->whereKey($id)->whereNull('revoked_at')->update(['revoked_at' => Carbon::now()]);
    }

    public function revokeAll(int|string $accountId): int
    {
        return $this->active()->where(ZoneTables::ACCOUNT_FOREIGN_KEY, $accountId)->update(['revoked_at' => Carbon::now()]);
    }

    public function pruneInactiveBefore(CarbonInterface $before): int
    {
        return $this->query()->toBase()
            ->where(static function (\Illuminate\Database\Query\Builder $query) use ($before): void {
                $query->where('revoked_at', '<', $before)->orWhere('expires_at', '<', $before);
            })
            ->delete();
    }

    private function record(?TrustedDevice $device): ?TrustedDeviceRecord
    {
        if ($device === null) {
            return null;
        }

        $id = $device->getKey();
        $accountId = AccountKey::from($device->getAttribute(ZoneTables::ACCOUNT_FOREIGN_KEY));
        $userAgent = $device->getAttribute('user_agent');
        $createdAt = $device->getAttribute('created_at');
        $lastUsedAt = $device->getAttribute('last_used_at');
        $expiresAt = $device->getAttribute('expires_at');

        if (! is_string($id) || $accountId === null || ! $expiresAt instanceof CarbonImmutable) {
            return null;
        }

        return new TrustedDeviceRecord(
            id: $id,
            token: $this->string($device, 'token'),
            accountId: $accountId,
            selector: $this->string($device, 'selector'),
            validatorHash: $this->string($device, 'validator_hash'),
            name: $this->string($device, 'name'),
            userAgent: is_string($userAgent) ? $userAgent : null,
            createdAt: $createdAt instanceof CarbonImmutable ? $createdAt : $expiresAt,
            lastUsedAt: $lastUsedAt instanceof CarbonImmutable ? $lastUsedAt : null,
            expiresAt: $expiresAt,
        );
    }

    private function string(TrustedDevice $device, string $column): string
    {
        $value = $device->getAttribute($column);

        return is_string($value) ? $value : '';
    }

    /**
     * @return Builder<TrustedDevice>
     */
    private function active(): Builder
    {
        $query = $this->query();
        $query->whereNull('revoked_at');
        $query->where('expires_at', '>', Carbon::now());

        return $query;
    }

    private function model(): TrustedDevice
    {
        return (new TrustedDevice())->setTable(ZoneTable::TrustedDevices->for($this->zone->get()));
    }

    /**
     * @return Builder<TrustedDevice>
     */
    private function query(): Builder
    {
        return $this->model()->newQuery();
    }
}

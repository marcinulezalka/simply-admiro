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

namespace Simplysmart\Admiro\DictionaryUpdates;

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Carbon;
use Simplysmart\Admiro\Auth\AccountKey;
use Simplysmart\Admiro\Database\ColumnNames;
use Simplysmart\Admiro\Enums\DictionaryUpdateStatus;
use Simplysmart\Admiro\Models\DictionaryUpdateRequest;
use Simplysmart\Admiro\Models\ZoneAccount;
use Simplysmart\Admiro\Queries\DictionaryUpdates\DictionaryUpdateRequestList;
use Simplysmart\Admiro\Zones\ZoneDefinition;
use Throwable;

/**
 * Prośby o aktualizację słowników: konto strefy prosi (jedna otwarta prośba na słownik — kolejna zwraca istniejącą),
 * administrator zatwierdza (zadanie RunDictionaryUpdate w kolejce) albo odrzuca, a zadanie zapisuje wynik. Tylko tu
 * zmienia się stan prośby.
 */
final readonly class DictionaryUpdateRequests
{
    public function __construct(
        private DictionaryUpdaters $updaters,
        private DictionaryUpdateRequestList $requests,
        private Dispatcher $bus,
    ) {}

    /**
     * @throws DictionaryUpdateRejected Gdy słownik nie jest zarejestrowany.
     */
    public function request(string $dictionary, ZoneDefinition $zone, ZoneAccount $account, ?int $organizationId): DictionaryUpdateRequest
    {
        $updater = $this->updaters->find($dictionary) ?? throw DictionaryUpdateRejected::because("unknown dictionary [{$dictionary}].");
        $open = $this->requests->open($dictionary);

        if ($open !== null) {
            return $open;
        }

        $request = new DictionaryUpdateRequest();
        $request->forceFill([
            ColumnNames::NAME => $updater->label(),
            ColumnNames::IS_ACTIVE => true,
            'dictionary' => $dictionary,
            'zone' => $zone->name,
            'account_id' => AccountKey::of($account),
            'organization_id' => $organizationId,
            'status' => DictionaryUpdateStatus::Pending,
        ])->save();

        return $request;
    }

    /**
     * Zatwierdza prośbę i wstawia aktualizację do kolejki.
     *
     * @throws DictionaryUpdateRejected Gdy prośba nie czeka na decyzję.
     */
    public function approve(DictionaryUpdateRequest $request, int|string $adminId): void
    {
        $this->decide($request, $adminId, DictionaryUpdateStatus::Approved);
        $this->bus->dispatch(new RunDictionaryUpdate($request->token));
    }

    /**
     * @throws DictionaryUpdateRejected Gdy prośba nie czeka na decyzję.
     */
    public function reject(DictionaryUpdateRequest $request, int|string $adminId): void
    {
        $this->decide($request, $adminId, DictionaryUpdateStatus::Rejected);
    }

    /**
     * Wykonuje zatwierdzoną aktualizację (zadanie z kolejki) i zapisuje podsumowanie albo błąd.
     */
    public function run(DictionaryUpdateRequest $request): void
    {
        if ($request->status !== DictionaryUpdateStatus::Approved) {
            return;
        }

        $updater = $this->updaters->find($request->dictionary);

        try {
            $result = $updater?->update() ?? throw DictionaryUpdateRejected::because("unknown dictionary [{$request->dictionary}].");
            $status = DictionaryUpdateStatus::Done;
        } catch (Throwable $exception) {
            report($exception);
            $result = $exception->getMessage();
            $status = DictionaryUpdateStatus::Failed;
        }

        $request->forceFill(['status' => $status, 'result' => mb_substr($result, 0, 1000), 'completed_at' => Carbon::now()])->save();
    }

    private function decide(DictionaryUpdateRequest $request, int|string $adminId, DictionaryUpdateStatus $status): void
    {
        if ($request->status !== DictionaryUpdateStatus::Pending) {
            throw DictionaryUpdateRejected::because('the request was already decided.');
        }

        $request->forceFill(['status' => $status, 'decided_by' => $adminId, 'decided_at' => Carbon::now()])->save();
    }
}

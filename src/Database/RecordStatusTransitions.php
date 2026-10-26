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

namespace Simplysmart\Admiro\Database;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Model;
use Simplysmart\Admiro\Audit\Events\RecordStatusChanged;
use Simplysmart\Admiro\Auth\AccountActor;
use Simplysmart\Admiro\Enums\RecordStatus;

/**
 * Jedyna droga zmiany stanu wiersza (RecordStatus) — transakcyjnie: w transakcji odczytuje wiersz ponownie z blokadą
 * (SELECT … FOR UPDATE), sprawdza przejście (RecordStatus::canTransitionTo()), zapisuje stan (checksum i historia
 * przez model) i po zatwierdzeniu transakcji ogłasza RecordStatusChanged (dziennik zdarzeń). Wejście w migrację
 * zapamiętuje stan sprzed niej w `json_migrations.record_status.previous`, a finishMigration() przywraca go i czyści.
 */
final readonly class RecordStatusTransitions
{
    public const string MIGRATIONS_KEY = 'record_status';

    public function __construct(
        private ConnectionResolverInterface $database,
        private Dispatcher $events,
    ) {}

    /**
     * @template TModel of Model
     *
     * @param TModel $record
     *
     * @return TModel Wiersz po zmianie (świeżo odczytany w transakcji).
     *
     * @throws RecordStatusTransitionRejected
     */
    public function transition(Model $record, RecordStatus $to, ?string $reason = null, ?AccountActor $actor = null): Model
    {
        return $this->change($record, $reason, $actor, static function (?RecordStatus $from, array $migrations) use ($to): array {
            if (($from ?? RecordStatus::Active)->canTransitionTo($to) !== true) {
                throw RecordStatusTransitionRejected::between($from, $to);
            }

            if ($to === RecordStatus::Migrating) {
                $migrations[self::MIGRATIONS_KEY] = ['previous' => ($from ?? RecordStatus::Active)->value];
            }

            return [$to, $migrations];
        });
    }

    /**
     * Kończy migrację wiersza: przywraca stan sprzed niej (bez zapisanego — aktywny).
     *
     * @template TModel of Model
     *
     * @param TModel $record
     *
     * @return TModel
     *
     * @throws RecordStatusTransitionRejected Gdy wiersz nie jest w migracji.
     */
    public function finishMigration(Model $record, ?string $reason = null, ?AccountActor $actor = null): Model
    {
        return $this->change($record, $reason, $actor, static function (?RecordStatus $from, array $migrations): array {
            if ($from !== RecordStatus::Migrating) {
                throw RecordStatusTransitionRejected::because('the record is not being migrated.');
            }

            $saved = $migrations[self::MIGRATIONS_KEY] ?? null;
            $previous = is_array($saved) && is_string($saved['previous'] ?? null) ? RecordStatus::tryFrom($saved['previous']) : null;
            unset($migrations[self::MIGRATIONS_KEY]);

            return [$previous ?? RecordStatus::Active, $migrations];
        });
    }

    /**
     * @template TModel of Model
     *
     * @param TModel $record
     * @param callable(?RecordStatus, array<array-key, mixed>): array{0: RecordStatus, 1: array<array-key, mixed>} $decide
     *
     * @return TModel
     */
    private function change(Model $record, ?string $reason, ?AccountActor $actor, callable $decide): Model
    {
        if (! method_exists($record, 'usesRecordStatus') || $record->usesRecordStatus() !== true) {
            throw RecordStatusTransitionRejected::because($record::class . ' has no standard record status.');
        }

        [$fresh, $from, $to] = $this->database->connection($record->getConnectionName())->transaction(static function () use ($record, $decide): array {
            /** @var TModel $fresh */
            $fresh = $record->newQueryWithoutScopes()->whereKey($record->getKey())->lockForUpdate()->firstOrFail();
            $from = $fresh->getAttribute(ColumnNames::STATUS);
            $from = $from instanceof RecordStatus ? $from : null;
            $migrations = $fresh->getAttribute(ColumnNames::JSON_MIGRATIONS);
            [$to, $migrations] = $decide($from, is_array($migrations) ? $migrations : []);
            $fresh->forceFill([ColumnNames::STATUS => $to, ColumnNames::JSON_MIGRATIONS => $migrations === [] ? null : $migrations])->save();

            return [$fresh, $from, $to];
        });

        $token = $fresh->getAttribute(ColumnNames::TOKEN);
        $key = $fresh->getKey();
        $this->events->dispatch(new RecordStatusChanged($fresh->getTable(), is_string($token) ? $token : (is_scalar($key) ? (string) $key : ''), $from, $to, $reason, $actor));

        return $fresh;
    }
}

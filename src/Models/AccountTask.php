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

namespace Simplysmart\Admiro\Models;

use Illuminate\Support\Carbon;
use Simplysmart\Admiro\Enums\TaskStatus;

/**
 * Zadanie TODO konta strefy (`account_tasks`), jak zgłoszenie w systemie ticketów: konto strefy (`zone`,
 * `account_id`), kolejny numer zgłoszenia w obrębie konta (`issue`), stan (`status`: Enums\TaskStatus — stan
 * dziedzinowy, nie RecordStatus), nazwa (`name`, wpisana po `todo:`), tytuł, opis, termin (`due_on`), opóźnienie
 * w dniach (`delay_days`), projekt, klient i chwila wykonania (`completed_at`). Zapis tylko przez Tasks\AccountTasks,
 * odczyt przez Queries\Tasks\AccountTaskList.
 *
 * @property string $zone
 * @property int $account_id
 * @property int $issue
 * @property TaskStatus $status
 * @property string|null $title
 * @property string|null $description
 * @property Carbon|null $due_on
 * @property int $delay_days
 * @property string|null $project
 * @property string|null $customer
 * @property Carbon|null $completed_at
 */
final class AccountTask extends DataModel
{
    public const string TABLE = 'account_tasks';

    protected $table = self::TABLE;

    /**
     * @var array<string, mixed>
     */
    protected $attributes = ['status' => 'open', 'delay_days' => 0];

    public function usesRecordStatus(): bool
    {
        return false;
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'zone' => 'string',
            'account_id' => 'integer',
            'issue' => 'integer',
            'status' => TaskStatus::class,
            'title' => 'string',
            'description' => 'string',
            'due_on' => 'date',
            'delay_days' => 'integer',
            'project' => 'string',
            'customer' => 'string',
            'completed_at' => 'datetime',
        ];
    }
}

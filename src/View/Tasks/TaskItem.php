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

namespace Simplysmart\Admiro\View\Tasks;

use Simplysmart\Admiro\Models\AccountTask;

/**
 * Zadanie na liście TODO panelu powiadomień: zadanie konta, adres przełączenia wykonania (PATCH) i linki do commitów
 * z krótkim podpisem `<repozytorium>@<7 znaków skrótu>` (adres w stylu `…/<repozytorium>/commit/<skrót>`; inny adres
 * podpisany jest w całości).
 */
final readonly class TaskItem
{
    private const int SHORT_HASH = 7;

    public function __construct(
        public AccountTask $task,
        public string $toggleUrl,
    ) {}

    /**
     * @return list<array{url: string, label: string}>
     */
    public function commits(): array
    {
        return array_map(static fn(string $url): array => ['url' => $url, 'label' => self::commitLabel($url)], $this->task->commits());
    }

    private static function commitLabel(string $url): string
    {
        $path = parse_url($url, PHP_URL_PATH);
        $segments = is_string($path) ? array_values(array_filter(explode('/', $path), static fn(string $segment): bool => $segment !== '')) : [];
        $commit = array_search('commit', $segments, true);

        if (! is_int($commit) || $commit < 1 || ! isset($segments[$commit + 1])) {
            return $url;
        }

        return $segments[$commit - 1] . '@' . substr($segments[$commit + 1], 0, self::SHORT_HASH);
    }
}

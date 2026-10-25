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

namespace Simplysmart\Admiro\Tools\Deploy;

/**
 * Stan repozytoriów przed wdrożeniem (nazwy względem katalogu projektu, `.` to sam projekt).
 */
final readonly class RepositoryReport
{
    /**
     * @param  list<string>  $unpushed  Repozytoria z niewypchniętymi commitami — wdrożenie jest wstrzymywane.
     * @param  list<string>  $uncommitted  Repozytoria z niezacommitowanymi zmianami — ostrzeżenie.
     * @param  list<string>  $unfetched  Repozytoria bez aktualnego stanu z `origin` — ostrzeżenie.
     */
    public function __construct(
        public array $unpushed = [],
        public array $uncommitted = [],
        public array $unfetched = [],
    ) {}
}

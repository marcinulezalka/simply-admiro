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

use PDOException;
use RuntimeException;

/**
 * Serwer bazy odrzucił triggery historii, bo ma włączony binlog, a użytkownik bazy nie ma uprawnienia SUPER
 * (MySQL 1419). Aplikacja tego nie obejdzie: administrator serwera ustawia `log_bin_trust_function_creators = 1`
 * (docs/wdrozenie.md), a w środowisku deweloperskim może też wyłączyć binlog.
 */
final class HistoryTriggersRejected extends RuntimeException
{
    /** Kod błędu MySQL/MariaDB ER_BINLOG_CREATE_ROUTINE_NEED_SUPER. */
    public const int BINLOG_NEEDS_SUPER = 1419;

    public static function matches(PDOException $exception): bool
    {
        return ($exception->errorInfo[1] ?? null) === self::BINLOG_NEEDS_SUPER;
    }

    public static function binlogNeedsTrust(string $table, PDOException $previous): self
    {
        return new self(
            "History triggers for [{$table}] were rejected: binary logging is enabled and the database user lacks the SUPER privilege. "
            . 'Set log_bin_trust_function_creators = 1 on the database server (e.g. SET PERSIST log_bin_trust_function_creators = ON as an administrator, '
            . 'or log_bin_trust_function_creators = 1 under [mysqld] in my.cnf), or disable binary logging in development, then run the migrations again.',
            previous: $previous,
        );
    }
}

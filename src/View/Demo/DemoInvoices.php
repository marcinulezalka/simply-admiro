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

namespace Simplysmart\Admiro\View\Demo;

/**
 * Jedno źródło przykładowych danych dla wszystkich widoków design systemu (pulpit, lista, rekord).
 */
final class DemoInvoices
{
    public const string CURRENCY = 'PLN';

    /** NIP i adres e-mail nabywcy pokazywane w widokach rekordu i formularza. */
    public const string CLIENT_TAX_ID = '527-000-12-34';

    public const string CLIENT_EMAIL = 'biuro@kowalski.example';

    /** Kraje (ISO 3166-1 alfa-2) w polu wyboru formularza; nazwy w języku interfejsu daje ICU. */
    public const array COUNTRIES = ['PL', 'DE', 'SE'];

    /** Dzień, względem którego przykład liczy terminy, żeby widoki nie zmieniały się z upływem czasu. */
    public const string TODAY = '2026-10-23';

    /**
     * @return list<DemoInvoice>
     */
    public static function all(): array
    {
        return [
            new DemoInvoice('FV/2026/10/014', 'Kowalski Sp. z o.o.', '2026-10-20', '2026-11-03', 12500.00, 23, DemoInvoiceStatus::Accepted),
            new DemoInvoice('FV/2026/10/013', 'Nordic Tools AB', '2026-10-17', '2026-10-31', 8420.50, 0, DemoInvoiceStatus::Sent),
            new DemoInvoice('FV/2026/10/012', 'Baltic Logistics', '2026-10-02', '2026-10-16', 3150.00, 23, DemoInvoiceStatus::Sent),
            new DemoInvoice('FV/2026/10/011', 'Zielony Ogród S.C.', '2026-10-01', '2026-10-15', 980.00, 8, DemoInvoiceStatus::Paid),
            new DemoInvoice('FV/2026/09/010', 'Kowalski Sp. z o.o.', '2026-09-28', '2026-10-12', 4600.00, 23, DemoInvoiceStatus::Closed),
            new DemoInvoice('FV/2026/10/015', 'Nordic Tools AB', '2026-10-23', '2026-11-06', 2200.00, 0, DemoInvoiceStatus::Draft),
        ];
    }

    /**
     * Faktura pokazywana w widoku szczegółów rekordu.
     */
    public static function featured(): DemoInvoice
    {
        return self::all()[0];
    }

    /**
     * Faktury po terminie względem {@see self::TODAY}: wysłane lub zaakceptowane, nieopłacone.
     *
     * @return list<DemoInvoice>
     */
    public static function overdue(): array
    {
        return array_values(array_filter(
            self::all(),
            static fn(DemoInvoice $invoice): bool => in_array($invoice->status, [DemoInvoiceStatus::Sent, DemoInvoiceStatus::Accepted], true) && $invoice->due < self::TODAY,
        ));
    }
}

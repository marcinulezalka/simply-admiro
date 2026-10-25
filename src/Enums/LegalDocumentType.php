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

namespace Simplysmart\Mainboard\Enums;

use Simplysmart\Mainboard\Contracts\HasLabel;

/**
 * Dokument prawny aplikacji w wersjach (Models\LegalDocument, poza polityką cookies, która ma własne wersje i zgody):
 * polityka prywatności i klauzula informacyjna RODO. Każdy ma publiczną stronę Core (trasa `mainboard.legal.<wartość>`),
 * etykietę odnośnika, wstęp i ikonę tytułu; strefa linkuje opublikowane w karcie logowania (`legal_documents`).
 */
enum LegalDocumentType: string implements HasLabel
{
    case Privacy = 'privacy';
    case Gdpr = 'gdpr';

    /**
     * Etykieta odnośnika (karta logowania, stopka, panel administratora).
     */
    public function label(): string
    {
        return text('legal.documents.' . $this->value . '.link');
    }

    /**
     * Krótki opis pod tytułem strony i w karcie „Zobacz także”.
     */
    public function lead(): string
    {
        return text('legal.documents.' . $this->value . '.lead');
    }

    /**
     * Ikona tytułu strony (x-mainboard::icon).
     */
    public function icon(): string
    {
        return match ($this) {
            self::Privacy => 'shield-check',
            self::Gdpr => 'lock',
        };
    }

    /**
     * Nazwa publicznej trasy Core strony dokumentu.
     */
    public function routeName(): string
    {
        return 'mainboard.legal.' . $this->value;
    }
}

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

return [

    /*
     * Dokumenty prawne Core (Enums\LegalDocumentType, Models\LegalDocument): odnośniki, wstępy stron, nazwy wersji.
     */
    'documents' => [
        'privacy' => [
            'link' => 'Polityka prywatności',
            'lead' => 'Kto przetwarza dane użytkowników aplikacji, w jakim celu, jak długo i jakie masz prawa.',
        ],
        'gdpr' => [
            'link' => 'RODO',
            'lead' => 'Informacje o przetwarzaniu danych osobowych zgodnie z art. 13 i 14 RODO.',
        ],
    ],
    'cookies_lead' => 'Jakie pliki cookies wykorzystuje aplikacja, w jakim celu i jak zarządzać zgodami.',
    'eyebrow' => 'Wersja :version z :date',
    'draft_name' => ':document — szkic',
    'version_name' => ':document, wersja :version',

];

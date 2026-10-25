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
     * Core legal documents (Enums\LegalDocumentType, Models\LegalDocument): links, page leads, version names.
     */
    'documents' => [
        'privacy' => [
            'link' => 'Privacy policy',
            'lead' => 'Who processes the data of the application users, why, for how long and what your rights are.',
        ],
        'gdpr' => [
            'link' => 'GDPR',
            'lead' => 'Information on the processing of personal data under Articles 13 and 14 GDPR.',
        ],
    ],
    'cookies_lead' => 'Which cookies the application uses, why, and how to manage your consent.',
    'eyebrow' => 'Version :version of :date',
    'draft_name' => ':document — draft',
    'version_name' => ':document, version :version',

];
